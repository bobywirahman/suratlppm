<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/constant.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: " . SITE_URL);
    exit;
}

$page_title = 'User Activity Log';

ob_start();

$pdo = db();

$fUser = $_GET['user'] ?? '';
$fAction = trim($_GET['action'] ?? '');
$fFrom = trim($_GET['from'] ?? '');
$fTo = trim($_GET['to'] ?? '');

$users = $pdo->query("SELECT u.id, u.full_name, r.name AS role_name
    FROM users u
    LEFT JOIN user_roles ur ON ur.user_id = u.id
    LEFT JOIN roles r ON r.id = ur.role_id
    ORDER BY u.full_name")->fetchAll();

$actionList = $pdo->query("SELECT action, COUNT(*) AS total FROM user_activity GROUP BY action ORDER BY action")->fetchAll();

$where = [];
$params = [];
if ($fUser !== '') {
    $where[] = 'ua.user_id = ?';
    $params[] = $fUser;
}
if ($fAction !== '') {
    $where[] = 'ua.action = ?';
    $params[] = $fAction;
}
if ($fFrom !== '') {
    $where[] = 'ua.created_at >= ?';
    $params[] = $fFrom . ' 00:00:00';
}
if ($fTo !== '') {
    $where[] = 'ua.created_at <= ?';
    $params[] = $fTo . ' 23:59:59';
}
$whereSql = $where ? (' WHERE ' . implode(' AND ', $where)) : '';

$limit = $_GET['limit'] ?? 10;
if ($limit !== 'all') {
    $limit = (int)$limit;
    if (!in_array($limit, [10, 25, 50, 100])) $limit = 10;
}
$page = max(1, (int)($_GET['p'] ?? 1));

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM user_activity ua" . $whereSql);
$countStmt->execute($params);
$totalMatching = (int)$countStmt->fetchColumn();

$listSql = "SELECT ua.*, u.full_name, u.email, r.name AS role_name
        FROM user_activity ua
        LEFT JOIN users u ON ua.user_id = u.id
        LEFT JOIN user_roles ur ON ur.user_id = ua.user_id
        LEFT JOIN roles r ON r.id = ur.role_id"
        . $whereSql . " ORDER BY ua.created_at DESC";
$listParams = $params;
if ($limit !== 'all') {
    $listSql .= " LIMIT " . (int)$limit . " OFFSET " . (int)(($page - 1) * $limit);
}
$listStmt = $pdo->prepare($listSql);
$listStmt->execute($listParams);
$logs = $listStmt->fetchAll();

$totalPages = ($limit === 'all') ? 1 : max(1, (int)ceil($totalMatching / $limit));

if ($limit === 'all') {
    $startNo = 0;
    $fromRow = $totalMatching > 0 ? 1 : 0;
    $toRow = $totalMatching;
} else {
    $startNo = ($page - 1) * $limit;
    $fromRow = $totalMatching > 0 ? $startNo + 1 : 0;
    $toRow = min($startNo + count($logs), $totalMatching);
}

$pageUrl = '?page=laporan-activity-log';
if ($fUser !== '') $pageUrl .= '&user=' . urlencode($fUser);
if ($fAction !== '') $pageUrl .= '&action=' . urlencode($fAction);
if ($fFrom !== '') $pageUrl .= '&from=' . urlencode($fFrom);
if ($fTo !== '') $pageUrl .= '&to=' . urlencode($fTo);
if ($limit !== 10) $pageUrl .= '&limit=' . urlencode($limit);
?>

<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0 fw-bold"><i class="fas fa-filter me-2"></i>Filter Log Aktivitas</h5>
                <a href="<?php echo SITE_URL; ?>?page=laporan-activity-log" class="btn btn-light btn-sm rounded-pill px-3">
                    <i class="fas fa-times me-1"></i>Reset
                </a>
            </div>
            <div class="card-body">
                <form method="GET" class="row g-3 align-items-end">
                    <input type="hidden" name="page" value="laporan-activity-log">
                    <div class="col-md-6 col-lg-3">
                        <label class="form-label small fw-semibold text-muted">User</label>
                        <select name="user" class="form-select">
                            <option value="">Semua User</option>
                            <?php foreach ($users as $u): ?>
                                <option value="<?php echo $u['id']; ?>" <?php echo $fUser == $u['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($u['full_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <label class="form-label small fw-semibold text-muted">Jenis Aktivitas</label>
                        <select name="action" class="form-select">
                            <option value="">Semua Aktivitas</option>
                            <?php foreach ($actionList as $a): ?>
                                <option value="<?php echo htmlspecialchars($a['action']); ?>" <?php echo $fAction === $a['action'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($a['action']); ?> (<?php echo $a['total']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 col-lg-2">
                        <label class="form-label small fw-semibold text-muted">Dari Tanggal</label>
                        <input type="date" name="from" value="<?php echo htmlspecialchars($fFrom); ?>" class="form-control">
                    </div>
                    <div class="col-md-6 col-lg-2">
                        <label class="form-label small fw-semibold text-muted">Sampai Tanggal</label>
                        <input type="date" name="to" value="<?php echo htmlspecialchars($fTo); ?>" class="form-control">
                    </div>
                    <div class="col-md-6 col-lg-2">
                        <button type="submit" class="btn btn-primary rounded-pill px-4 w-100"><i class="fas fa-search me-1"></i>Tampilkan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header d-flex justify-content-between align-items-center py-3">
                <h6 class="mb-0 fw-bold"><i class="fas fa-history me-2"></i>Log Aktivitas (<?php echo $totalMatching; ?>)</h6>
                <select name="limit" class="form-select form-select-sm w-auto" onchange="location.href='<?php echo htmlspecialchars($pageUrl); ?>' + (this.value != 10 ? '&limit=' + this.value : '')">
                    <option value="10" <?php echo $limit == 10 ? 'selected' : ''; ?>>10</option>
                    <option value="25" <?php echo $limit == 25 ? 'selected' : ''; ?>>25</option>
                    <option value="50" <?php echo $limit == 50 ? 'selected' : ''; ?>>50</option>
                    <option value="100" <?php echo $limit == 100 ? 'selected' : ''; ?>>100</option>
                    <option value="all" <?php echo $limit === 'all' ? 'selected' : ''; ?>>All</option>
                </select>
            </div>
            <div class="card-body">
                <?php if (empty($logs)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                        <p class="text-muted mb-0">Belum ada log aktivitas yang cocok dengan filter.</p>
                    </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th width="5%" class="text-center">No</th>
                                <th width="17%">Waktu</th>
                                <th width="18%">User</th>
                                <th width="15%">Role</th>
                                <th width="20%">Aktivitas</th>
                                <th>Detail</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $i => $log): ?>
                            <tr>
                                <td class="text-center"><?php echo $startNo + $i + 1; ?></td>
                                <td><?php echo date('d/m/Y H:i:s', strtotime($log['created_at'])); ?></td>
                                <td class="fw-semibold">
                                    <?php echo $log['full_name'] ? htmlspecialchars($log['full_name']) : '<span class="text-muted">Sistem/Tamu</span>'; ?>
                                    <div class="small text-muted"><?php echo htmlspecialchars($log['email'] ?? ''); ?></div>
                                </td>
                                <td>
                                    <?php if ($log['role_name']): ?>
                                        <span class="badge bg-primary"><?php echo htmlspecialchars($log['role_name']); ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">-</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($log['action']); ?></td>
                                <td class="text-muted"><?php echo htmlspecialchars($log['details'] ?? ''); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3">
                    <div class="text-muted small">
                        Menampilkan <?php echo $fromRow; ?> - <?php echo $toRow; ?> dari <?php echo $totalMatching; ?> data
                    </div>
                    <?php if ($totalPages > 1): ?>
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                <a class="page-link" href="<?php echo htmlspecialchars($pageUrl . '&p=' . ($page - 1)); ?>">&laquo;</a>
                            </li>
                            <?php
                            $window = 2;
                            $pageLinks = [];
                            if ($totalPages <= 7) {
                                $pageLinks = range(1, $totalPages);
                            } else {
                                for ($p = 1; $p <= $totalPages; $p++) {
                                    if ($p == 1 || $p == $totalPages || abs($p - $page) <= $window) {
                                        $pageLinks[] = $p;
                                    } elseif (end($pageLinks) !== '...') {
                                        $pageLinks[] = '...';
                                    }
                                }
                            }
                            foreach ($pageLinks as $p):
                                if ($p === '...'):
                            ?>
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            <?php else: ?>
                                <li class="page-item <?php echo $p == $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="<?php echo htmlspecialchars($pageUrl . '&p=' . $p); ?>"><?php echo $p; ?></a>
                                </li>
                            <?php endif; endforeach; ?>
                            <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                                <a class="page-link" href="<?php echo htmlspecialchars($pageUrl . '&p=' . ($page + 1)); ?>">&raquo;</a>
                            </li>
                        </ul>
                    </nav>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); require __DIR__ . '/../layouts/master.php'; ?>
