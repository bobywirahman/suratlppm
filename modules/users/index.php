<?php
$page_title = 'Manajemen User';
$pdo = db();
$action = $_GET['action'] ?? 'list';
$editUser = null;

$adminIds = $pdo->query("SELECT user_id FROM user_roles WHERE role_id = (SELECT id FROM roles WHERE name = 'admin')")->fetchAll(PDO::FETCH_COLUMN);
$adminGuard = empty($adminIds) ? '1=1' : 'id NOT IN (' . implode(',', $adminIds) . ')';

if ($action === 'activate' && isset($_GET['id'])) {
    $pdo->prepare("UPDATE users SET is_active = 1 WHERE id = ? AND $adminGuard")->execute([$_GET['id']]);
    $_SESSION['success'] = "Akun berhasil diaktivasi";
    header("Location: ?page=users");
    exit;
}

if ($action === 'reject' && isset($_GET['id'])) {
    $pdo->prepare("DELETE FROM users WHERE id = ? AND $adminGuard AND is_active = 0")->execute([$_GET['id']]);
    $_SESSION['success'] = "Akun ditolak dan dihapus";
    header("Location: ?page=users");
    exit;
}

if ($action === 'delete' && isset($_GET['id'])) {
    $pdo->prepare("DELETE FROM users WHERE id = ? AND $adminGuard")->execute([$_GET['id']]);
    $_SESSION['success'] = "User berhasil dihapus";
    header("Location: ?page=users");
    exit;
}

if ($action === 'edit' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT u.*, r.name as role FROM users u LEFT JOIN user_roles ur ON u.id = ur.user_id LEFT JOIN roles r ON ur.role_id = r.id WHERE u.id = ?");
    $stmt->execute([$_GET['id']]);
    $editUser = $stmt->fetch();
}

$users = $pdo->query("SELECT u.*, d.name as department_name, r.name as role FROM users u LEFT JOIN departments d ON u.department_id = d.id LEFT JOIN user_roles ur ON u.id = ur.user_id LEFT JOIN roles r ON ur.role_id = r.id ORDER BY u.is_active ASC, u.id")->fetchAll();
$departments = $pdo->query("SELECT * FROM departments ORDER BY name")->fetchAll();
$faculties = $pdo->query("SELECT id, name FROM faculties ORDER BY name")->fetchAll();
$roles = $pdo->query("SELECT * FROM roles ORDER BY id")->fetchAll();
$roleMap = [];
foreach ($roles as $r) $roleMap[$r['name']] = $r['display_name'];
ob_start();
?>

<?php if ($action === 'edit' || $action === 'create'): ?>
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card">
            <div class="card-header bg-gradient" style="background: linear-gradient(135deg, #FF6B35 0%, #e85d2a 100%); color: white; border-radius: 10px 10px 0 0 !important;">
                <h5 class="mb-0"><i class="fas fa-user-<?php echo $action === 'create' ? 'plus' : 'edit'; ?> me-2"></i> <?php echo $action === 'create' ? 'Tambah' : 'Edit'; ?> User</h5>
            </div>
            <div class="card-body">
                    <form method="POST" action="?page=users-save">
                        <?php if ($editUser): ?><input type="hidden" name="id" value="<?php echo $editUser['id']; ?>"><?php endif; ?>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">Nama Lengkap *</label>
                                <input type="text" name="full_name" class="form-control form-control" required value="<?php echo htmlspecialchars($editUser['full_name'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">Username</label>
                                <input type="text" name="username" class="form-control form-control" value="<?php echo htmlspecialchars($editUser['username'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">NIP</label>
                                <input type="text" name="nip" class="form-control form-control" value="<?php echo htmlspecialchars($editUser['nip'] ?? ''); ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">NIM</label>
                                <input type="text" name="nim" class="form-control form-control" value="<?php echo htmlspecialchars($editUser['nim'] ?? ''); ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">No. HP</label>
                                <input type="text" name="no_hp" class="form-control form-control" value="<?php echo htmlspecialchars($editUser['no_hp'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">Email *</label>
                                <input type="email" name="email" class="form-control form-control" required value="<?php echo htmlspecialchars($editUser['email'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">Password <?php echo $editUser ? '(kosongkan jika tidak diubah)' : '*'; ?></label>
                                <input type="password" name="password" class="form-control form-control" <?php echo $editUser ? '' : 'required'; ?>>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">Role</label>
                                <select name="role" class="form-select form-select">
                                    <?php foreach ($roles as $r): ?>
                                        <option value="<?php echo $r['name']; ?>" <?php echo ($editUser['role'] ?? '') === $r['name'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($r['display_name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">Program Studi</label>
                                <select name="department_id" class="form-select form-select">
                                    <option value="">Pilih Program Studi</option>
                                    <?php foreach ($departments as $d): ?>
                                        <option value="<?php echo $d['id']; ?>" <?php echo ($editUser['department_id'] ?? '') == $d['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($d['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Alamat</label>
                            <textarea name="alamat" class="form-control form-control" rows="2"><?php echo htmlspecialchars($editUser['alamat'] ?? ''); ?></textarea>
                        </div>
                        <div class="mb-4 form-check">
                            <input type="checkbox" name="is_active" class="form-check-input" value="1" id="is_active" <?php echo ($editUser['is_active'] ?? 1) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="is_active">Aktif</label>
                        </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary rounded-pill px-4"><i class="fas fa-save me-2"></i> Simpan</button>
                        <a href="?page=users" class="btn btn-outline-secondary rounded-pill px-4">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #FF6B35 0%, #e85d2a 100%); color: white; border-radius: 10px 10px 0 0 !important;">
        <h5 class="mb-0"><i class="fas fa-users me-2"></i> Daftar User</h5>
        <a href="?page=users&action=create" class="btn btn-light btn-sm rounded-pill px-3"><i class="fas fa-plus me-1"></i> Tambah User</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-primary text-white">
                    <tr>
                        <th class="ps-3">#</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Program Studi</th>
                        <th>Status</th>
                        <th class="pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $i => $u): ?>
                    <tr class="<?php echo !$u['is_active'] ? 'table-warning' : ''; ?>">
                        <td class="ps-3"><?php echo $i + 1; ?></td>
                        <td class="fw-bold"><?php echo htmlspecialchars($u['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                        <td><?php echo htmlspecialchars($u['username'] ?? '-'); ?></td>
                        <td><span class="badge bg-<?php echo $u['role'] === 'admin' ? 'danger' : ($u['role'] === 'staff' ? 'warning' : 'info'); ?>"><?php echo htmlspecialchars($roleMap[$u['role']] ?? ($u['role'] ? ucfirst($u['role']) : '-')); ?></span></td>
                        <td><?php echo htmlspecialchars($u['department_name'] ?? '-'); ?></td>
                        <td>
                            <?php if ($u['is_active']): ?>
                                <span class="badge bg-success">Aktif</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark">Menunggu</span>
                            <?php endif; ?>
                        </td>
                        <td class="pe-3" style="white-space: nowrap;">
                            <a href="?page=users&action=edit&id=<?php echo $u['id']; ?>" class="btn btn-sm btn-outline-primary rounded-circle" title="Edit"><i class="fas fa-edit"></i></a>
                            <?php if (!$u['is_active'] && $u['role'] !== 'admin'): ?>
                                <a href="?page=users&action=activate&id=<?php echo $u['id']; ?>" class="btn btn-sm btn-outline-success rounded-circle" onclick="return confirm('Aktivasi akun ini?')" title="Aktivasi"><i class="fas fa-check"></i></a>
                                <a href="?page=users&action=reject&id=<?php echo $u['id']; ?>" class="btn btn-sm btn-outline-danger rounded-circle" onclick="return confirm('Tolak dan hapus akun ini?')" title="Tolak"><i class="fas fa-times"></i></a>
                            <?php endif; ?>
                            <?php if ($u['role'] !== 'admin'): ?>
                                <a href="?page=users&action=delete&id=<?php echo $u['id']; ?>" class="btn btn-sm btn-outline-danger rounded-circle" onclick="return confirm('Hapus user ini?')" title="Hapus"><i class="fas fa-trash"></i></a>
                            <?php endif; ?>
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
