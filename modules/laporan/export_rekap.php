<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/constant.php';
require_once __DIR__ . '/../../includes/helpers.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: " . SITE_URL);
    exit;
}
if (!hasPermission('view_all_documents')) {
    $_SESSION['error'] = "Akses ditolak";
    header("Location: " . SITE_URL);
    exit;
}

$pdo = db();

$fStatus = $_GET['status'] ?? '';
$fType = $_GET['type'] ?? '';
$fYear = $_GET['academic_year_id'] ?? '';

$statusList = [
    STATUS_DRAFT       => 'Draft',
    STATUS_SUBMITTED   => 'Diajukan',
    STATUS_IN_PROGRESS => 'Diproses',
    STATUS_APPROVED    => 'Disetujui',
    STATUS_REJECTED    => 'Ditolak',
    STATUS_REVISI      => 'Revisi',
    STATUS_COMPLETED   => 'Selesai',
];

$types = $pdo->query("SELECT code, name FROM document_types")->fetchAll(PDO::FETCH_KEY_PAIR);
$years = $pdo->query("SELECT id, name FROM academic_years")->fetchAll(PDO::FETCH_KEY_PAIR);

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

$listStmt = $pdo->prepare("SELECT d.*, u.full_name AS applicant_name, de.name AS department_name,
        dt.name AS type_name, ay.name AS academic_year_name
        FROM documents d
        JOIN users u ON d.applicant_id = u.id
        LEFT JOIN departments de ON d.department_id = de.id
        LEFT JOIN document_types dt ON d.type = dt.code
        LEFT JOIN academic_years ay ON d.academic_year_id = ay.id"
        . $whereSql . " ORDER BY d.created_at DESC");
$listStmt->execute($params);
$documents = $listStmt->fetchAll();

$appName = getSetting('app_name', APP_NAME);

header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header('Content-Disposition: attachment; filename="daftar_surat_' . date('Ymd_His') . '.xls"');
header('Pragma: no-cache');
header('Expires: 0');

echo "\xEF\xBB\xBF";
echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
echo '<head><meta charset="utf-8"><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>DaftarSurat</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head>';
echo '<body>';
echo '<table border="1" cellpadding="5">';

echo '<tr>';
echo '<td colspan="9" style="font-size:14pt; font-weight:bold; text-align:center; background:#f2f2f2;">' . htmlspecialchars($appName) . '</td>';
echo '</tr>';
echo '<tr>';
echo '<td colspan="9" style="text-align:center; font-weight:bold;">LAPORAN DAFTAR SURAT</td>';
echo '</tr>';
echo '<tr>';
echo '<td colspan="9">Tanggal Export: ' . date('d/m/Y H:i') . '</td>';
echo '</tr>';
$filterDesc = [];
if ($fStatus !== '') $filterDesc[] = 'Status: ' . ($statusList[$fStatus] ?? $fStatus);
if ($fType !== '') $filterDesc[] = 'Jenis Surat: ' . ($types[$fType] ?? $fType);
if ($fYear !== '') $filterDesc[] = 'Tahun Ajaran: ' . ($years[$fYear] ?? $fYear);
echo '<tr>';
echo '<td colspan="9">Filter: ' . htmlspecialchars($filterDesc ? implode(', ', $filterDesc) : 'Semua') . '</td>';
echo '</tr>';
echo '<tr><td colspan="9">&nbsp;</td></tr>';

echo '<tr style="background:#d9d9d9;">';
echo '<th>No</th><th>Nomor Surat</th><th>Judul Surat</th><th>Jenis Surat</th><th>Pengaju</th><th>Prodi</th><th>Status</th><th>Tahun Ajaran</th><th>Tanggal Dibuat</th>';
echo '</tr>';

foreach ($documents as $i => $doc) {
    echo '<tr>';
    echo '<td style="text-align:center;">' . ($i + 1) . '</td>';
    echo '<td>' . htmlspecialchars($doc['document_number'] ?? '-') . '</td>';
    echo '<td>' . htmlspecialchars($doc['title']) . '</td>';
    echo '<td>' . htmlspecialchars($doc['type_name'] ?? $doc['type']) . '</td>';
    echo '<td>' . htmlspecialchars($doc['applicant_name']) . '</td>';
    echo '<td>' . htmlspecialchars($doc['department_name'] ?? '-') . '</td>';
    echo '<td>' . htmlspecialchars($statusList[$doc['status']] ?? ucfirst($doc['status'])) . '</td>';
    echo '<td>' . htmlspecialchars($doc['academic_year_name'] ?? '-') . '</td>';
    echo '<td>' . date('d/m/Y', strtotime($doc['created_at'])) . '</td>';
    echo '</tr>';
}

echo '</table>';
echo '</body></html>';
exit;
