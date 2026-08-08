<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/constant.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: " . SITE_URL);
    exit;
}

$page_title = 'Rekap Status Surat';

ob_start();

$pdo = db();

$fStatus = $_GET['status'] ?? '';
$fType = $_GET['type'] ?? '';
$fYear = $_GET['academic_year_id'] ?? '';

$types = $pdo->query("SELECT code, name FROM document_types WHERE is_active = 1 ORDER BY name")->fetchAll();
$years = $pdo->query("SELECT id, name FROM academic_years ORDER BY name")->fetchAll();

$statusList = [
    STATUS_DRAFT       => ['label' => 'Draft', 'class' => 'secondary'],
    STATUS_SUBMITTED   => ['label' => 'Diajukan', 'class' => 'warning'],
    STATUS_IN_PROGRESS => ['label' => 'Diproses', 'class' => 'info'],
    STATUS_APPROVED    => ['label' => 'Disetujui', 'class' => 'success'],
    STATUS_REJECTED    => ['label' => 'Ditolak', 'class' => 'danger'],
    STATUS_REVISI      => ['label' => 'Revisi', 'class' => 'warning'],
    STATUS_COMPLETED   => ['label' => 'Selesai', 'class' => 'success'],
];

$where = [];
$params = [];
if ($fStatus !== '') {
    $where[] = 'd.status = ?';
    $params[] = $fStatus;
}
if ($fType !== '') {
    $where[] = 'd.type = ?';
    $params[] = $fType;
}
if ($fYear !== '') {
    $where[] = 'd.academic_year_id = ?';
    $params[] = $fYear;
}
$whereSql = $where ? (' WHERE ' . implode(' AND ', $where)) : '';

$limit = $_GET['limit'] ?? 10;
if ($limit !== 'all') {
    $limit = (int)$limit;
    if (!in_array($limit, [10, 25, 50, 100])) $limit = 10;
}
$page = max(1, (int)($_GET['page'] ?? 1));

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM documents d" . $whereSql);
$countStmt->execute($params);
$totalMatching = (int)$countStmt->fetchColumn();

$listSql = "SELECT d.*, u.full_name AS applicant_name, u.no_hp AS applicant_phone, de.name AS department_name,
        dt.name AS type_name, ay.name AS academic_year_name
        FROM documents d
        JOIN users u ON d.applicant_id = u.id
        LEFT JOIN departments de ON d.department_id = de.id
        LEFT JOIN document_types dt ON d.type = dt.code
        LEFT JOIN academic_years ay ON d.academic_year_id = ay.id"
        . $whereSql . " ORDER BY d.created_at DESC";
$listParams = $params;
if ($limit !== 'all') {
    $listSql .= " LIMIT " . (int)$limit . " OFFSET " . (int)(($page - 1) * $limit);
}
$listStmt = $pdo->prepare($listSql);
$listStmt->execute($listParams);
$documents = $listStmt->fetchAll();

$totalPages = ($limit === 'all') ? 1 : max(1, (int)ceil($totalMatching / $limit));

if ($limit === 'all') {
    $startNo = 0;
    $fromRow = $totalMatching > 0 ? 1 : 0;
    $toRow = $totalMatching;
} else {
    $startNo = ($page - 1) * $limit;
    $fromRow = $totalMatching > 0 ? $startNo + 1 : 0;
    $toRow = min($startNo + count($documents), $totalMatching);
}

$exportUrl = '?page=laporan-rekap-export';
$exportParams = [];
if ($fStatus !== '') $exportParams[] = 'status=' . urlencode($fStatus);
if ($fType !== '') $exportParams[] = 'type=' . urlencode($fType);
if ($fYear !== '') $exportParams[] = 'academic_year_id=' . urlencode($fYear);
if ($exportParams) $exportUrl .= '&' . implode('&', $exportParams);

$pageUrl = '?page=laporan-rekap';
if ($fStatus !== '') $pageUrl .= '&status=' . urlencode($fStatus);
if ($fType !== '') $pageUrl .= '&type=' . urlencode($fType);
if ($fYear !== '') $pageUrl .= '&academic_year_id=' . urlencode($fYear);
if ($limit !== 10) $pageUrl .= '&limit=' . urlencode($limit);
?>

<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0 fw-bold"><i class="fas fa-filter me-2"></i>Filter Laporan</h5>
                <a href="<?php echo SITE_URL; ?>?page=laporan-rekap" class="btn btn-light btn-sm rounded-pill px-3">
                    <i class="fas fa-times me-1"></i>Reset
                </a>
            </div>
            <div class="card-body">
                <form method="GET" class="row g-3 align-items-end">
                    <input type="hidden" name="page" value="laporan-rekap">
                    <div class="col-md-6 col-lg-2">
                        <label class="form-label small fw-semibold text-muted">Status</label>
                        <select name="status" class="form-select">
                            <option value="">Semua Status</option>
                            <?php foreach ($statusList as $val => $info): ?>
                                <option value="<?php echo $val; ?>" <?php echo $fStatus === $val ? 'selected' : ''; ?>>
                                    <?php echo $info['label']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 col-lg-2">
                        <label class="form-label small fw-semibold text-muted">Jenis Surat</label>
                        <select name="type" class="form-select">
                            <option value="">Semua Jenis Surat</option>
                            <?php foreach ($types as $t): ?>
                                <option value="<?php echo htmlspecialchars($t['code']); ?>" <?php echo $fType === $t['code'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($t['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 col-lg-2">
                        <label class="form-label small fw-semibold text-muted">Tahun Ajaran</label>
                        <select name="academic_year_id" class="form-select">
                            <option value="">Semua Tahun Ajaran</option>
                            <?php foreach ($years as $y): ?>
                                <option value="<?php echo $y['id']; ?>" <?php echo $fYear == $y['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($y['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 col-lg-2">
                        <label class="form-label small fw-semibold text-muted">Jumlah Data</label>
                        <select name="limit" class="form-select" onchange="this.form.submit()">
                            <option value="10" <?php echo $limit == 10 ? 'selected' : ''; ?>>10</option>
                            <option value="25" <?php echo $limit == 25 ? 'selected' : ''; ?>>25</option>
                            <option value="50" <?php echo $limit == 50 ? 'selected' : ''; ?>>50</option>
                            <option value="100" <?php echo $limit == 100 ? 'selected' : ''; ?>>100</option>
                            <option value="all" <?php echo $limit === 'all' ? 'selected' : ''; ?>>All</option>
                        </select>
                    </div>
                    <div class="col-md-6 col-lg-4">
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
                <h6 class="mb-0 fw-bold"><i class="fas fa-file-alt me-2"></i>Daftar Surat (<?php echo $totalMatching; ?>)</h6>
                <a href="<?php echo htmlspecialchars($exportUrl); ?>" class="btn btn-success btn-sm rounded-pill px-3">
                    <i class="fas fa-file-excel me-1"></i>Export Excel
                </a>
            </div>
            <div class="card-body">
                <?php if (empty($documents)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <p class="text-muted mb-0">Tidak ada surat yang cocok dengan filter.</p>
                    </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th width="3%" class="text-center">No</th>
                                <th width="15%">Nomor Surat</th>
                                <th>Judul Surat</th>
                                <th width="14%">Jenis Surat</th>
                                <th width="12%">Pengaju</th>
                                <th width="12%">No. HP</th>
                                <th width="10%">Status</th>
                                <th width="12%">Tahun Ajaran</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($documents as $i => $doc): ?>
                            <tr>
                                <td class="text-center"><?php echo $startNo + $i + 1; ?></td>
                                <td><?php echo htmlspecialchars($doc['document_number'] ?? '-'); ?></td>
                                <td class="fw-semibold">
                                    <a href="<?php echo SITE_URL; ?>?page=detail&id=<?php echo $doc['id']; ?>" class="text-decoration-none">
                                        <?php echo htmlspecialchars($doc['title']); ?>
                                    </a>
                                </td>
                                <td><?php echo htmlspecialchars($doc['type_name'] ?? $doc['type']); ?></td>
                                <td><?php echo htmlspecialchars($doc['applicant_name']); ?></td>
                                <td>
                                    <?php $wa = waNumber($doc['applicant_phone'] ?? ''); ?>
                                    <?php if ($wa !== ''): ?>
                                        <a href="https://wa.me/<?php echo $wa; ?>" target="_blank" rel="noopener" class="text-decoration-none text-success" title="Chat via WhatsApp">
                                            <i class="fab fa-whatsapp me-1"></i><?php echo htmlspecialchars($doc['applicant_phone']); ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php $st = $statusList[$doc['status']] ?? ['label' => ucfirst($doc['status']), 'class' => 'secondary']; ?>
                                    <span class="badge bg-<?php echo $st['class']; ?>"><?php echo $st['label']; ?></span>
                                </td>
                                <td><?php echo htmlspecialchars($doc['academic_year_name'] ?? '-'); ?></td>
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
                                <a class="page-link" href="<?php echo htmlspecialchars($pageUrl . '&page=' . ($page - 1)); ?>">&laquo;</a>
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
                                    <a class="page-link" href="<?php echo htmlspecialchars($pageUrl . '&page=' . $p); ?>"><?php echo $p; ?></a>
                                </li>
                            <?php endif; endforeach; ?>
                            <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                                <a class="page-link" href="<?php echo htmlspecialchars($pageUrl . '&page=' . ($page + 1)); ?>">&raquo;</a>
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
