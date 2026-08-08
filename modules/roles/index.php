<?php
$page_title = 'Role User';
$pdo = db();
$action = $_GET['action'] ?? 'list';
$editRole = null;

if ($action === 'delete' && isset($_GET['id'])) {
    $check = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = (SELECT name FROM roles WHERE id = ?)");
    $check->execute([$_GET['id']]);
    if ($check->fetchColumn() > 0) {
        $_SESSION['error'] = "Role tidak bisa dihapus karena masih digunakan oleh user";
        header("Location: ?page=roles");
        exit;
    }
    $pdo->prepare("DELETE FROM roles WHERE id = ?")->execute([$_GET['id']]);
    $_SESSION['success'] = "Role berhasil dihapus";
    header("Location: ?page=roles");
    exit;
}

if ($action === 'edit' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM roles WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $editRole = $stmt->fetch();
}

$roleList = $pdo->query("SELECT * FROM roles ORDER BY id")->fetchAll();

ob_start();
?>

<?php if ($action === 'edit' || $action === 'create'): ?>
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header" style="background: linear-gradient(135deg, #FF6B35 0%, #e85d2a 100%); color: white; border-radius: 10px 10px 0 0 !important;">
                <h5 class="mb-0"><i class="fas fa-user-tag me-2"></i> <?php echo $action === 'create' ? 'Tambah' : 'Edit'; ?> Role</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="?page=roles-save">
                    <?php if ($editRole): ?><input type="hidden" name="id" value="<?php echo $editRole['id']; ?>"><?php endif; ?>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Nama Role *</label>
                        <input type="text" name="name" class="form-control form-control" required value="<?php echo htmlspecialchars($editRole['name'] ?? ''); ?>">
                        <div class="form-text">Nama unik untuk role (contoh: admin, staff, researcher)</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Nama Tampilan *</label>
                        <input type="text" name="display_name" class="form-control form-control" required value="<?php echo htmlspecialchars($editRole['display_name'] ?? ''); ?>">
                        <div class="form-text">Nama yang ditampilkan di antarmuka (contoh: Administrator, Staff LPPM)</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Deskripsi</label>
                        <textarea name="description" class="form-control form-control" rows="3"><?php echo htmlspecialchars($editRole['description'] ?? ''); ?></textarea>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary rounded-pill px-4"><i class="fas fa-save me-2"></i> Simpan</button>
                        <a href="?page=roles" class="btn btn-outline-secondary rounded-pill px-4">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #FF6B35 0%, #e85d2a 100%); color: white; border-radius: 10px 10px 0 0 !important;">
        <h5 class="mb-0"><i class="fas fa-user-tag me-2"></i> Daftar Role User</h5>
        <a href="?page=roles&action=create" class="btn btn-light btn-sm rounded-pill px-3"><i class="fas fa-plus me-1"></i> Tambah Role</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-primary text-white">
                    <tr>
                        <th class="ps-3">#</th>
                        <th>Nama Role</th>
                        <th>Nama Tampilan</th>
                        <th>Deskripsi</th>
                        <th class="pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($roleList as $i => $r): ?>
                    <tr>
                        <td class="ps-3"><?php echo $i + 1; ?></td>
                        <td><code class="bg-light px-2 py-1 rounded"><?php echo htmlspecialchars($r['name']); ?></code></td>
                        <td class="fw-bold"><?php echo htmlspecialchars($r['display_name']); ?></td>
                        <td><?php echo htmlspecialchars($r['description'] ?? '-'); ?></td>
                        <td class="pe-3">
                            <a href="?page=permissions&role_id=<?php echo $r['id']; ?>" class="btn btn-sm btn-outline-success rounded-pill px-3" title="Atur Hak Akses"><i class="fas fa-shield-alt me-1"></i> Atur Hak Akses</a>
                            <a href="?page=roles&action=edit&id=<?php echo $r['id']; ?>" class="btn btn-sm btn-outline-primary rounded-circle" title="Edit"><i class="fas fa-edit"></i></a>
                            <a href="?page=roles&action=delete&id=<?php echo $r['id']; ?>" class="btn btn-sm btn-outline-danger rounded-circle" onclick="return confirm('Hapus role ini?')" title="Hapus"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; 
$content = ob_get_clean(); require __DIR__ . '/../layouts/master.php'; ?>
