<?php
$page_title = 'Master Program Studi';
$pdo = db();
$action = $_GET['action'] ?? 'list';
$editProdi = null;

if ($action === 'delete' && isset($_GET['id'])) {
    $check = $pdo->prepare("SELECT COUNT(*) FROM documents WHERE department_id = ?");
    $check->execute([$_GET['id']]);
    if ($check->fetchColumn() > 0) {
        $_SESSION['error'] = "Program Studi tidak bisa dihapus karena masih digunakan oleh surat";
        header("Location: ?page=prodi");
        exit;
    }
    $pdo->prepare("DELETE FROM departments WHERE id = ?")->execute([$_GET['id']]);
    $_SESSION['success'] = "Program Studi berhasil dihapus";
    header("Location: ?page=prodi");
    exit;
}

if ($action === 'edit' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM departments WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $editProdi = $stmt->fetch();
}

$prodiList = $pdo->query("SELECT d.*, f.name as faculty_name FROM departments d LEFT JOIN faculties f ON d.faculty_id = f.id ORDER BY f.name, d.name")->fetchAll();
$faculties = $pdo->query("SELECT * FROM faculties ORDER BY name")->fetchAll();
ob_start();
?>

<?php if ($action === 'edit' || $action === 'create'): ?>
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header" style="background: linear-gradient(135deg, #FF6B35 0%, #e85d2a 100%); color: white; border-radius: 10px 10px 0 0 !important;">
                <h5 class="mb-0"><i class="fas fa-building me-2"></i> <?php echo $action === 'create' ? 'Tambah' : 'Edit'; ?> Program Studi</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="?page=prodi-save">
                    <?php if ($editProdi): ?><input type="hidden" name="id" value="<?php echo $editProdi['id']; ?>"><?php endif; ?>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-muted">Kode *</label>
                            <input type="text" name="code" class="form-control form-control" required value="<?php echo htmlspecialchars($editProdi['code'] ?? ''); ?>">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-bold small text-muted">Nama Program Studi *</label>
                            <input type="text" name="name" class="form-control form-control" required value="<?php echo htmlspecialchars($editProdi['name'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold small text-muted">Fakultas</label>
                        <select name="faculty_id" class="form-select form-select">
                            <option value="">Pilih Fakultas</option>
                            <?php foreach ($faculties as $f): ?>
                                <option value="<?php echo $f['id']; ?>" <?php echo ($editProdi['faculty_id'] ?? '') == $f['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($f['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary rounded-pill px-4"><i class="fas fa-save me-2"></i> Simpan</button>
                        <a href="?page=prodi" class="btn btn-outline-secondary rounded-pill px-4">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #FF6B35 0%, #e85d2a 100%); color: white; border-radius: 10px 10px 0 0 !important;">
        <h5 class="mb-0"><i class="fas fa-building me-2"></i> Daftar Program Studi</h5>
        <a href="?page=prodi&action=create" class="btn btn-light btn-sm rounded-pill px-3"><i class="fas fa-plus me-1"></i> Tambah Prodi</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-primary text-white">
                    <tr>
                        <th class="ps-3">#</th>
                        <th>Kode</th>
                        <th>Program Studi</th>
                        <th>Fakultas</th>
                        <th class="pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($prodiList as $i => $p): ?>
                    <tr>
                        <td class="ps-3"><?php echo $i + 1; ?></td>
                        <td><code class="bg-light px-2 py-1 rounded"><?php echo htmlspecialchars($p['code']); ?></code></td>
                        <td class="fw-bold"><?php echo htmlspecialchars($p['name']); ?></td>
                        <td><?php echo htmlspecialchars($p['faculty_name'] ?? '-'); ?></td>
                        <td class="pe-3">
                            <a href="?page=prodi&action=edit&id=<?php echo $p['id']; ?>" class="btn btn-sm btn-outline-primary rounded-circle" title="Edit"><i class="fas fa-edit"></i></a>
                            <a href="?page=prodi&action=delete&id=<?php echo $p['id']; ?>" class="btn btn-sm btn-outline-danger rounded-circle" onclick="return confirm('Hapus prodi ini?')" title="Hapus"><i class="fas fa-trash"></i></a>
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
