<?php
$page_title = 'Manajemen User';
$pdo = db();
$action = $_GET['action'] ?? 'list';
$editUser = null;

// List state: search + pagination
$q = trim($_GET['q'] ?? '');
$perPageRaw = $_GET['per_page'] ?? '15';
$perPage = in_array($perPageRaw, ['15', '20', '50', '100', 'all'], true) ? $perPageRaw : '15';
$p = max(1, (int)($_GET['p'] ?? 1));
$listParams = 'q=' . urlencode($q) . '&per_page=' . urlencode($perPage) . '&p=' . $p;

$adminIds = $pdo->query("SELECT user_id FROM user_roles WHERE role_id = (SELECT id FROM roles WHERE name = 'admin')")->fetchAll(PDO::FETCH_COLUMN);
$adminGuard = empty($adminIds) ? '1=1' : 'id NOT IN (' . implode(',', $adminIds) . ')';

if ($action === 'activate' && isset($_GET['id'])) {
    $pdo->prepare("UPDATE users SET is_active = 1 WHERE id = ? AND $adminGuard")->execute([$_GET['id']]);
    $_SESSION['success'] = "Akun berhasil diaktivasi";
    header("Location: ?page=users&" . $listParams);
    exit;
}

if ($action === 'reject' && isset($_GET['id'])) {
    $pdo->prepare("DELETE FROM users WHERE id = ? AND $adminGuard AND is_active = 0")->execute([$_GET['id']]);
    $_SESSION['success'] = "Akun ditolak dan dihapus";
    header("Location: ?page=users&" . $listParams);
    exit;
}

if ($action === 'delete' && isset($_GET['id'])) {
    $pdo->prepare("DELETE FROM users WHERE id = ? AND $adminGuard")->execute([$_GET['id']]);
    $_SESSION['success'] = "User berhasil dihapus";
    header("Location: ?page=users&" . $listParams);
    exit;
}

if ($action === 'edit' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT u.*, r.name as role FROM users u LEFT JOIN user_roles ur ON u.id = ur.user_id LEFT JOIN roles r ON ur.role_id = r.id WHERE u.id = ?");
    $stmt->execute([$_GET['id']]);
    $editUser = $stmt->fetch();
}

// Search across all user metadata
$searchWhere = '';
$searchParams = [];
if ($q !== '') {
    $like = '%' . $q . '%';
    $searchWhere = " AND (u.id LIKE :sq_id OR u.full_name LIKE :sq_name OR u.username LIKE :sq_user OR u.nip LIKE :sq_nip OR u.nim LIKE :sq_nim OR u.email LIKE :sq_email OR u.no_hp LIKE :sq_hp OR u.alamat LIKE :sq_alamat OR d.name LIKE :sq_prodi OR r.name LIKE :sq_role)";
    $searchParams = [
        ':sq_id' => $like, ':sq_name' => $like, ':sq_user' => $like, ':sq_nip' => $like,
        ':sq_nim' => $like, ':sq_email' => $like, ':sq_hp' => $like, ':sq_alamat' => $like,
        ':sq_prodi' => $like, ':sq_role' => $like,
    ];
}

$fromSql = "FROM users u
            LEFT JOIN departments d ON u.department_id = d.id
            LEFT JOIN user_roles ur ON u.id = ur.user_id
            LEFT JOIN roles r ON ur.role_id = r.id
            WHERE 1=1" . $searchWhere;

$countStmt = $pdo->prepare("SELECT COUNT(DISTINCT u.id) " . $fromSql);
$countStmt->execute($searchParams);
$totalUsers = (int)$countStmt->fetchColumn();

$listSql = "SELECT u.*, d.name as department_name, r.name as role " . $fromSql . " ORDER BY u.is_active ASC, u.id";
if ($perPage !== 'all') {
    $limit = (int)$perPage;
    $totalPages = max(1, (int)ceil($totalUsers / $limit));
    $p = min($p, $totalPages);
    $offset = ($p - 1) * $limit;
    $listSql .= " LIMIT " . $offset . ", " . $limit;
} else {
    $totalPages = 1;
    $offset = 0;
}
$listStmt = $pdo->prepare($listSql);
$listStmt->execute($searchParams);
$users = $listStmt->fetchAll();

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
                        <input type="hidden" name="back" value="<?php echo htmlspecialchars($listParams); ?>">
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
                        <a href="?page=users&<?php echo $listParams; ?>" class="btn btn-outline-secondary rounded-pill px-4">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #FF6B35 0%, #e85d2a 100%); color: white; border-radius: 10px 10px 0 0 !important;">
        <h5 class="mb-0"><i class="fas fa-users me-2"></i> Daftar User <span class="badge bg-light text-dark"><?php echo $totalUsers; ?></span></h5>
        <div class="d-flex gap-2">
            <a href="?page=users" class="btn btn-light btn-sm rounded-pill px-3" title="Reset pencarian"><i class="fas fa-sync-alt"></i></a>
            <a href="?page=users&action=create" class="btn btn-light btn-sm rounded-pill px-3"><i class="fas fa-plus me-1"></i> Tambah User</a>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 p-3 pb-2">
            <form method="get" action="?page=users" class="d-flex gap-2" role="search" style="max-width: 480px;">
                <input type="hidden" name="page" value="users">
                <input type="hidden" name="p" value="1">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white text-muted"><i class="fas fa-search"></i></span>
                    <input type="text" name="q" class="form-control" placeholder="Cari nama, email, username, NIP, NIM, No. HP, alamat, prodi, role..." value="<?php echo htmlspecialchars($q); ?>">
                </div>
                <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3">Cari</button>
                <?php if ($q !== ''): ?>
                <a href="?page=users" class="btn btn-outline-secondary btn-sm rounded-pill px-3">Reset</a>
                <?php endif; ?>
            </form>
            <div class="d-flex align-items-center gap-2">
                <span class="text-muted small">Tampilkan</span>
                <select class="form-select form-select-sm" style="width:auto;" onchange="location.href='?page=users&q=<?php echo urlencode($q); ?>&per_page='+this.value+'&p=1';" aria-label="Jumlah data per halaman">
                    <?php foreach (['15', '20', '50', '100', 'all'] as $opt): ?>
                        <option value="<?php echo $opt; ?>" <?php echo $perPage === $opt ? 'selected' : ''; ?>><?php echo $opt === 'all' ? 'Semua' : $opt; ?></option>
                    <?php endforeach; ?>
                </select>
                <span class="text-muted small">per halaman</span>
            </div>
        </div>
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
                    <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted py-5">
                            <i class="fas fa-user-slash fa-2x mb-2"></i><br>
                            <?php echo $q !== '' ? 'Tidak ada user yang cocok dengan pencarian &ldquo;' . htmlspecialchars($q) . '&rdquo;.' : 'Belum ada user.'; ?>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($users as $i => $u): ?>
                    <tr class="<?php echo !$u['is_active'] ? 'table-warning' : ''; ?>">
                        <td class="ps-3"><?php echo $offset + $i + 1; ?></td>
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
                            <a href="?page=users&action=edit&id=<?php echo $u['id']; ?>&<?php echo $listParams; ?>" class="btn btn-sm btn-outline-primary rounded-circle" title="Edit"><i class="fas fa-edit"></i></a>
                            <?php if (!$u['is_active'] && $u['role'] !== 'admin'): ?>
                                <a href="?page=users&action=activate&id=<?php echo $u['id']; ?>&<?php echo $listParams; ?>" class="btn btn-sm btn-outline-success rounded-circle" onclick="return confirm('Aktivasi akun ini?')" title="Aktivasi"><i class="fas fa-check"></i></a>
                                <a href="?page=users&action=reject&id=<?php echo $u['id']; ?>&<?php echo $listParams; ?>" class="btn btn-sm btn-outline-danger rounded-circle" onclick="return confirm('Tolak dan hapus akun ini?')" title="Tolak"><i class="fas fa-times"></i></a>
                            <?php endif; ?>
                            <?php if ($u['role'] !== 'admin'): ?>
                                <a href="?page=users&action=delete&id=<?php echo $u['id']; ?>&<?php echo $listParams; ?>" class="btn btn-sm btn-outline-danger rounded-circle" onclick="return confirm('Hapus user ini?')" title="Hapus"><i class="fas fa-trash"></i></a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 border-top p-3">
            <small class="text-muted">
                <?php if ($totalUsers > 0): ?>
                    Menampilkan <?php echo $offset + 1; ?>–<?php echo min($offset + count($users), $totalUsers); ?> dari <?php echo $totalUsers; ?> user
                <?php else: ?>
                    0 user
                <?php endif; ?>
            </small>
            <?php if ($perPage !== 'all' && $totalPages > 1): ?>
            <nav aria-label="Paginasi daftar user">
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item <?php echo $p <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=users&q=<?php echo urlencode($q); ?>&per_page=<?php echo $perPage; ?>&p=<?php echo $p - 1; ?>">&laquo;</a>
                    </li>
                    <?php
                    $startPg = max(1, min($p - 3, $totalPages - 6));
                    $endPg = min($totalPages, $startPg + 6);
                    for ($i = $startPg; $i <= $endPg; $i++):
                    ?>
                    <li class="page-item <?php echo $i === $p ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=users&q=<?php echo urlencode($q); ?>&per_page=<?php echo $perPage; ?>&p=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                    <?php endfor; ?>
                    <li class="page-item <?php echo $p >= $totalPages ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=users&q=<?php echo urlencode($q); ?>&per_page=<?php echo $perPage; ?>&p=<?php echo $p + 1; ?>">&raquo;</a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; 
$content = ob_get_clean(); require __DIR__ . '/../layouts/master.php'; ?>
