<?php
$page_title = 'Master Tahun Ajaran';
$pdo = db();
$action = $_GET['action'] ?? 'list';
$editAY = null;

if ($action === 'delete' && isset($_GET['id'])) {
    $pdo->prepare("DELETE FROM academic_years WHERE id = ?")->execute([$_GET['id']]);
    $_SESSION['success'] = "Tahun ajaran berhasil dihapus";
    header("Location: ?page=academic-years");
    exit;
}

if ($action === 'edit' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM academic_years WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $editAY = $stmt->fetch();
}

$ayList = $pdo->query("SELECT * FROM academic_years ORDER BY name DESC")->fetchAll();
ob_start();
?>

<?php if ($action === 'edit' || $action === 'create'): ?>
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header" style="background: linear-gradient(135deg, #FF6B35 0%, #e85d2a 100%); color: white; border-radius: 10px 10px 0 0 !important;">
                <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i> <?php echo $action === 'create' ? 'Tambah' : 'Edit'; ?> Tahun Ajaran</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="?page=academic-years-save">
                    <?php if ($editAY): ?><input type="hidden" name="id" value="<?php echo $editAY['id']; ?>"><?php endif; ?>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Nama Tahun Ajaran *</label>
                        <input type="text" name="name" class="form-control form-control" required value="<?php echo htmlspecialchars($editAY['name'] ?? ''); ?>" placeholder="Contoh: 2024/2025">
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary rounded-pill px-4"><i class="fas fa-save me-2"></i> Simpan</button>
                        <a href="?page=academic-years" class="btn btn-outline-secondary rounded-pill px-4">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #FF6B35 0%, #e85d2a 100%); color: white; border-radius: 10px 10px 0 0 !important;">
        <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i> Daftar Tahun Ajaran</h5>
        <a href="?page=academic-years&action=create" class="btn btn-light btn-sm rounded-pill px-3"><i class="fas fa-plus me-1"></i> Tambah Tahun Ajaran</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-primary text-white">
                    <tr>
                        <th class="ps-3">#</th>
                        <th>Nama Tahun Ajaran</th>
                        <th class="pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ayList as $i => $a): ?>
                    <tr>
                        <td class="ps-3"><?php echo $i + 1; ?></td>
                        <td class="fw-bold"><?php echo htmlspecialchars($a['name']); ?></td>
                        <td class="pe-3">
                            <a href="?page=academic-years&action=edit&id=<?php echo $a['id']; ?>" class="btn btn-sm btn-outline-primary rounded-circle" title="Edit"><i class="fas fa-edit"></i></a>
                            <a href="?page=academic-years&action=delete&id=<?php echo $a['id']; ?>" class="btn btn-sm btn-outline-danger rounded-circle" onclick="return confirm('Hapus tahun ajaran ini?')" title="Hapus"><i class="fas fa-trash"></i></a>
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
