<?php
$page_title = 'Master Jenis Surat';
$pdo = db();
$action = $_GET['action'] ?? 'list';
$editType = null;

if ($action === 'delete' && isset($_GET['id'])) {
    $pdo->prepare("DELETE FROM document_types WHERE id = ?")->execute([$_GET['id']]);
    $_SESSION['success'] = "Jenis surat berhasil dihapus";
    header("Location: ?page=types");
    exit;
}

if ($action === 'edit' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM document_types WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $editType = $stmt->fetch();
}

$editRoleIds = [];
if ($editType) {
    $roleStmt = $pdo->prepare("SELECT role_id FROM document_type_roles WHERE type_id = ?");
    $roleStmt->execute([$editType['id']]);
    $editRoleIds = $roleStmt->fetchAll(PDO::FETCH_COLUMN);
}

$roleList = $pdo->query("SELECT * FROM roles ORDER BY id")->fetchAll();

$types = $pdo->query("SELECT t.*, GROUP_CONCAT(r.display_name ORDER BY r.name SEPARATOR ', ') AS role_names
    FROM document_types t
    LEFT JOIN document_type_roles dtr ON dtr.type_id = t.id
    LEFT JOIN roles r ON r.id = dtr.role_id
    GROUP BY t.id ORDER BY t.id")->fetchAll();
ob_start();
?>

<?php if ($action === 'edit' || $action === 'create'): ?>
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header" style="background: linear-gradient(135deg, #FF6B35 0%, #e85d2a 100%); color: white; border-radius: 10px 10px 0 0 !important;">
                <h5 class="mb-0"><i class="fas fa-tag me-2"></i> <?php echo $action === 'create' ? 'Tambah' : 'Edit'; ?> Jenis Surat</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="?page=types-save">
                    <?php if ($editType): ?><input type="hidden" name="id" value="<?php echo $editType['id']; ?>"><?php endif; ?>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Kode *</label>
                        <input type="text" name="code" class="form-control form-control" required value="<?php echo htmlspecialchars($editType['code'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Nama *</label>
                        <input type="text" name="name" class="form-control form-control" required value="<?php echo htmlspecialchars($editType['name'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Deskripsi</label>
                        <textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($editType['description'] ?? ''); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Ditujukan untuk Role</label>
                        <div class="border rounded p-2" style="max-height:180px; overflow-y:auto;">
                            <?php foreach ($roleList as $r): ?>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="role_ids[]" value="<?php echo $r['id']; ?>" id="role_<?php echo $r['id']; ?>" <?php echo in_array($r['id'], $editRoleIds, true) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="role_<?php echo $r['id']; ?>"><?php echo htmlspecialchars($r['display_name']); ?></label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="form-text">Kosongkan untuk berlaku bagi semua role.</div>
                    </div>
                    <div class="mb-4 form-check">
                        <input type="checkbox" name="is_active" class="form-check-input" value="1" id="is_active" <?php echo ($editType['is_active'] ?? 1) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="is_active">Aktif</label>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary rounded-pill px-4"><i class="fas fa-save me-2"></i> Simpan</button>
                        <a href="?page=types" class="btn btn-outline-secondary rounded-pill px-4">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #FF6B35 0%, #e85d2a 100%); color: white; border-radius: 10px 10px 0 0 !important;">
        <h5 class="mb-0"><i class="fas fa-tags me-2"></i> Daftar Jenis Surat</h5>
        <a href="?page=types&action=create" class="btn btn-light btn-sm rounded-pill px-3"><i class="fas fa-plus me-1"></i> Tambah Jenis</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-primary text-white">
                    <tr>
                        <th class="ps-3">#</th>
                        <th>Kode</th>
                        <th>Nama</th>
                        <th>Deskripsi</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th class="pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($types as $i => $t): ?>
                    <tr>
                        <td class="ps-3"><?php echo $i + 1; ?></td>
                        <td><code class="bg-light px-2 py-1 rounded"><?php echo htmlspecialchars($t['code']); ?></code></td>
                        <td class="fw-bold"><?php echo htmlspecialchars($t['name']); ?></td>
                        <td class="text-muted"><?php echo htmlspecialchars($t['description'] ?? '-'); ?></td>
                        <td>
                            <?php if (!empty($t['role_names'])): ?>
                                <span class="badge bg-primary"><?php echo htmlspecialchars($t['role_names']); ?></span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Semua Role</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $t['is_active'] ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-secondary">Nonaktif</span>'; ?></td>
                        <td class="pe-3">
                            <a href="?page=types-requirements&id=<?php echo $t['id']; ?>" class="btn btn-sm btn-outline-warning rounded-circle" title="Set Syarat"><i class="fas fa-list-check"></i></a>
                            <a href="?page=types-template&id=<?php echo $t['id']; ?>" class="btn btn-sm btn-outline-info rounded-circle" title="Set Template"><i class="fas fa-file-code"></i></a>
                            <a href="?page=types&action=edit&id=<?php echo $t['id']; ?>" class="btn btn-sm btn-outline-primary rounded-circle" title="Edit"><i class="fas fa-edit"></i></a>
                            <a href="?page=types&action=delete&id=<?php echo $t['id']; ?>" class="btn btn-sm btn-outline-danger rounded-circle" onclick="return confirm('Hapus jenis surat ini?')" title="Hapus"><i class="fas fa-trash"></i></a>
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
