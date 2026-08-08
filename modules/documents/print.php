<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/constant.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: " . SITE_URL);
    exit;
}

$pdo = db();
$id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("SELECT d.*, u.full_name as applicant_name, u.nim, u.no_hp, u.email, u.alamat,
                        de.name as department_name, f.name as faculty_name,
                        dt.template, dt.name as type_name,
                        ay.name as academic_year_name
                        FROM documents d
                        JOIN users u ON d.applicant_id = u.id
                        JOIN departments de ON d.department_id = de.id
                        LEFT JOIN faculties f ON de.faculty_id = f.id
                        LEFT JOIN document_types dt ON d.type = dt.code
                        LEFT JOIN academic_years ay ON d.academic_year_id = ay.id
                        WHERE d.id = ?");
$stmt->execute([$id]);
$doc = $stmt->fetch();

if (!$doc) {
    $_SESSION['error'] = "Dokumen tidak ditemukan!";
    header("Location: ?page=list");
    exit;
}

// Only allow print if completed or approved
if (!in_array($doc['status'], [STATUS_COMPLETED, STATUS_APPROVED])) {
    $_SESSION['error'] = "Surat belum dapat dicetak";
    header("Location: ?page=list");
    exit;
}

// Process template with placeholders
$template = $doc['template'] ?? '';
$apprStmt = $pdo->prepare("SELECT approved_at FROM approvals WHERE document_id = ? AND action = 'approve' ORDER BY approved_at DESC LIMIT 1");
$apprStmt->execute([$id]);
$approvalDate = $apprStmt->fetchColumn();
$replacements = [
    '{nama}' => htmlspecialchars($doc['applicant_name']),
    '{nim}' => htmlspecialchars($doc['nim'] ?? '-'),
    '{prodi}' => htmlspecialchars($doc['department_name'] ?? '-'),
    '{fakultas}' => htmlspecialchars($doc['faculty_name'] ?? '-'),
    '{alamat}' => htmlspecialchars($doc['alamat'] ?? '-'),
    '{no_hp}' => htmlspecialchars($doc['no_hp'] ?? '-'),
    '{email}' => htmlspecialchars($doc['email'] ?? '-'),
    '{tahun_ajaran}' => htmlspecialchars($doc['academic_year_name'] ?? '-'),
    '{judul}' => htmlspecialchars($doc['title'] ?? '-'),
    '{tipe_surat}' => htmlspecialchars($doc['type_name'] ?? '-'),
    '{deskripsi}' => nl2br(htmlspecialchars($doc['description'] ?? '-')),
    '{tanggal}' => date('d F Y'),
    '{tanggal_approval}' => $approvalDate ? date('d F Y', strtotime($approvalDate)) : '-',
    '{no_surat}' => htmlspecialchars($doc['document_number'] ?? '-'),
];
// Add dynamic replacements from text-type requirements
$textStmt = $pdo->prepare("SELECT tr.description, tr.input_type, tr.config, da.text_value FROM document_attachments da
    JOIN type_requirements tr ON da.requirement_id = tr.id
    WHERE da.document_id = ? AND tr.input_type IN ('text','list') AND da.text_value IS NOT NULL");
$textStmt->execute([$id]);
foreach ($textStmt->fetchAll() as $tr) {
    $key = '{' . strtolower(str_replace(' ', '_', preg_replace('/[^a-zA-Z0-9 ]/', '', $tr['description']))) . '}';
    if (($tr['input_type'] ?? '') === 'list') {
        $decoded = json_decode($tr['text_value'], true);
        $config = json_decode($tr['config'] ?? '', true);
        $cols = [];
        if (is_array($config) && !empty($config)) {
            foreach ($config as $c) {
                if (isset($c['key'], $c['label'])) $cols[] = ['key' => $c['key'], 'label' => $c['label']];
            }
        }
        $tbl = '';
        if (is_array($decoded)) {
            if (empty($cols) && !empty($decoded)) {
                $first = is_array($decoded[0]) ? $decoded[0] : [];
                foreach ($first as $k => $v) $cols[] = ['key' => $k, 'label' => ucwords(str_replace('_', ' ', $k))];
            }
            if (empty($cols)) $cols[] = ['key' => 'nama', 'label' => 'Nama'];
            $tbl = '<table style="border-collapse:collapse; width:100%; margin:6px 0;">';
            $tbl .= '<thead><tr><th style="border:1px solid #000; padding:4px 8px; text-align:center; width:30px;">No</th>';
            foreach ($cols as $c) $tbl .= '<th style="border:1px solid #000; padding:4px 8px;">' . htmlspecialchars($c['label']) . '</th>';
            $tbl .= '</tr></thead><tbody>';
            $no = 0;
            foreach ($decoded as $li) {
                $li = is_array($li) ? $li : [];
                $no++;
                $tbl .= '<tr><td style="border:1px solid #000; padding:4px 8px; text-align:center;">' . $no . '</td>';
                foreach ($cols as $c) {
                    $val = isset($li[$c['key']]) ? trim((string)$li[$c['key']]) : '';
                    $tbl .= '<td style="border:1px solid #000; padding:4px 8px;">' . htmlspecialchars($val) . '</td>';
                }
                $tbl .= '</tr>';
            }
            $tbl .= '</tbody></table>';
        }
        $replacements[$key] = $tbl;
    } else {
        $replacements[$key] = nl2br(htmlspecialchars($tr['text_value']));
    }
}

$body = str_replace(array_keys($replacements), array_values($replacements), $template);
// Hapus placeholder yang tidak dikenal (tidak ada datanya)
$body = preg_replace('/\{[a-z_0-9]+\}/i', '', $body);
if (empty(trim(strip_tags($body)))) {
    $body = '<p>' . nl2br(htmlspecialchars($doc['description'] ?? '')) . '</p>';
}
?><!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Surat - <?php echo htmlspecialchars($doc['title']); ?></title>
    <?php $printFavicon = asset(getSetting('favicon', '')); ?>
    <?php if ($printFavicon): ?>
    <link rel="icon" href="<?php echo htmlspecialchars($printFavicon); ?>">
    <?php endif; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Times New Roman', Times, serif; }
        body { padding: 5px 60px; background: #fff; font-size: 12pt; line-height: 1.6; }
        img { max-width: 100%; height: auto; }
        img.img-full-bleed { margin-left: -60px; margin-right: -60px; width: calc(100% + 120px); max-width: none; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 5px 60px; }
        }
    </style>
</head>
<body>
    <div class="text-end mb-3 no-print">
        <button onclick="window.print()" class="btn btn-primary rounded-pill px-4"><i class="fas fa-print me-2"></i> Cetak / PDF</button>
        <a href="?page=list" class="btn btn-outline-secondary rounded-pill px-4 ms-2">Kembali</a>
    </div>

    <?php echo $body; ?>
</body>
</html>