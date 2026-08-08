<?php
$page_title = 'Atur Hak Akses';
$pdo = db();
$roleId = $_GET['role_id'] ?? null;
if (!$roleId) { header("Location: ?page=roles"); exit; }

try {
    $pdo->query("SELECT 1 FROM permissions LIMIT 1");
} catch (Exception $e) {
    $_SESSION['error'] = "Tabel hak akses belum tersedia. Klik 'Inisialisasi Hak Akses' terlebih dahulu.";
    header("Location: ?page=roles");
    exit;
}

$role = $pdo->prepare("SELECT * FROM roles WHERE id = ?");
$role->execute([$roleId]);
$role = $role->fetch();
if (!$role) { header("Location: ?page=roles"); exit; }

$permissions = $pdo->query("SELECT * FROM permissions ORDER BY id")->fetchAll();

$assigned = $pdo->prepare("SELECT permission_id FROM role_permissions WHERE role_id = ?");
$assigned->execute([$roleId]);
$assignedIds = $assigned->fetchAll(PDO::FETCH_COLUMN);
$assignedIds = array_flip($assignedIds);
ob_start();
?>
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #FF6B35 0%, #e85d2a 100%); color: white; border-radius: 10px 10px 0 0 !important;">
                <h5 class="mb-0"><i class="fas fa-shield-alt me-2"></i> Hak Akses: <?php echo htmlspecialchars($role['display_name']); ?></h5>
                <a href="?page=roles" class="btn btn-light btn-sm rounded-pill px-3"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3"><i class="fas fa-info-circle me-1"></i> Centang hak akses yang ingin diberikan kepada role <strong><?php echo htmlspecialchars($role['display_name']); ?></strong></p>
                <form method="POST" action="?page=permissions-save">
                    <input type="hidden" name="role_id" value="<?php echo $role['id']; ?>">
                    <div class="list-group list-group-flush">
                        <?php foreach ($permissions as $p): ?>
                        <label class="list-group-item d-flex align-items-center gap-3 py-3 border-start border-4 <?php echo isset($assignedIds[$p['id']]) ? 'border-success' : 'border-light'; ?>">
                            <input type="checkbox" name="permissions[]" value="<?php echo $p['id']; ?>"
                                class="form-check-input form-check-input-lg ms-0 mt-0 flex-shrink-0"
                                style="width: 1.3em; height: 1.3em; cursor: pointer;"
                                <?php echo isset($assignedIds[$p['id']]) ? 'checked' : ''; ?>>
                            <div class="flex-grow-1">
                                <div class="fw-bold small"><?php echo htmlspecialchars($p['name']); ?></div>
                                <div class="text-muted small"><?php echo htmlspecialchars($p['description'] ?? ''); ?></div>
                            </div>
                            <code class="text-muted small flex-shrink-0"><?php echo htmlspecialchars($p['key']); ?></code>
                        </label>
                        <?php endforeach; ?>
                    </div>
                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary rounded-pill px-4"><i class="fas fa-save me-2"></i> Simpan Hak Akses</button>
                        <a href="?page=roles" class="btn btn-outline-secondary rounded-pill px-4">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/master.php';
