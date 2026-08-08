<?php
$page_title = 'Master Fakultas';
$pdo = db();
$action = $_GET['action'] ?? 'list';
$editFac = null;

if ($action === 'delete' && isset($_GET['id'])) {
    $check = $pdo->prepare("SELECT COUNT(*) FROM departments WHERE faculty_id = ?");
    $check->execute([$_GET['id']]);
    if ($check->fetchColumn() > 0) {
        $_SESSION['error'] = "Fakultas tidak bisa dihapus karena masih memiliki Program Studi";
        header("Location: ?page=faculties");
        exit;
    }
    $pdo->prepare("DELETE FROM faculties WHERE id = ?")->execute([$_GET['id']]);
    $_SESSION['success'] = "Fakultas berhasil dihapus";
    header("Location: ?page=faculties");
    exit;
}

if ($action === 'edit' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM faculties WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $editFac = $stmt->fetch();
}

$facList = $pdo->query("SELECT * FROM faculties ORDER BY name")->fetchAll();
ob_start();
?>

<?php if ($action === 'edit' || $action === 'create'): ?>
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header" style="background: linear-gradient(135deg, #FF6B35 0%, #e85d2a 100%); color: white; border-radius: 10px 10px 0 0 !important;">
                <h5 class="mb-0"><i class="fas fa-graduation-cap me-2"></i> <?php echo $action === 'create' ? 'Tambah' : 'Edit'; ?> Fakultas</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="?page=faculties-save">
                    <?php if ($editFac): ?><input type="hidden" name="id" value="<?php echo $editFac['id']; ?>"><?php endif; ?>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Kode *</label>
                        <input type="text" name="code" class="form-control form-control" required value="<?php echo htmlspecialchars($editFac['code'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Nama Fakultas *</label>
                        <input type="text" name="name" class="form-control form-control" required value="<?php echo htmlspecialchars($editFac['name'] ?? ''); ?>">
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary rounded-pill px-4"><i class="fas fa-save me-2"></i> Simpan</button>
                        <a href="?page=faculties" class="btn btn-outline-secondary rounded-pill px-4">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #FF6B35 0%, #e85d2a 100%); color: white; border-radius: 10px 10px 0 0 !important;">
        <h5 class="mb-0"><i class="fas fa-graduation-cap me-2"></i> Daftar Fakultas</h5>
        <a href="?page=faculties&action=create" class="btn btn-light btn-sm rounded-pill px-3"><i class="fas fa-plus me-1"></i> Tambah Fakultas</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-primary text-white">
                    <tr>
                        <th class="ps-3">#</th>
                        <th>Kode</th>
                        <th>Nama Fakultas</th>
                        <th class="pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($facList as $i => $f): ?>
                    <tr>
                        <td class="ps-3"><?php echo $i + 1; ?></td>
                        <td><code class="bg-light px-2 py-1 rounded"><?php echo htmlspecialchars($f['code']); ?></code></td>
                        <td class="fw-bold"><?php echo htmlspecialchars($f['name']); ?></td>
                        <td class="pe-3">
                            <a href="?page=faculties&action=edit&id=<?php echo $f['id']; ?>" class="btn btn-sm btn-outline-primary rounded-circle" title="Edit"><i class="fas fa-edit"></i></a>
                            <a href="?page=faculties&action=delete&id=<?php echo $f['id']; ?>" class="btn btn-sm btn-outline-danger rounded-circle" onclick="return confirm('Hapus fakultas ini?')" title="Hapus"><i class="fas fa-trash"></i></a>
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
