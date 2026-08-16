<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/constant.php';

ob_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: " . SITE_URL);
    exit;
}

$pdo = db();
$id = $_GET['id'] ?? 0;
$page_title = 'Detail Surat';

// Get document with user & department info
$stmt = $pdo->prepare("SELECT d.*, u.full_name as applicant_name, u.nim, u.no_hp,
                        de.name as department_name, f.name as faculty_name
                        FROM documents d
                        JOIN users u ON d.applicant_id = u.id
                        JOIN departments de ON d.department_id = de.id
                        LEFT JOIN faculties f ON de.faculty_id = f.id
                        WHERE d.id = ?");
$stmt->execute([$id]);
$document = $stmt->fetch();

if (!$document) {
    $_SESSION['error'] = "Dokumen tidak ditemukan!";
    header("Location: ?page=list");
    exit;
}

// Get attachments with requirement descriptions
$attStmt = $pdo->prepare("SELECT a.*, r.description as req_description, r.input_type
                          FROM document_attachments a
                          LEFT JOIN type_requirements r ON a.requirement_id = r.id
                          WHERE a.document_id = ?");
$attStmt->execute([$id]);
$attachments = $attStmt->fetchAll();

// Nomor surat terakhir yang tercatat digunakan
$lastNomor = (string)($pdo->query("SELECT document_number FROM documents WHERE document_number IS NOT NULL AND document_number <> '' ORDER BY id DESC LIMIT 1")->fetchColumn() ?: '');

// Daftar syarat sesuai tipe surat
$reqStmt = $pdo->prepare("SELECT r.* FROM type_requirements r JOIN document_types t ON r.type_id = t.id WHERE t.code = ? ORDER BY r.sort_order ASC, r.id ASC");
$reqStmt->execute([$document['type']]);
$requirements = $reqStmt->fetchAll();

// Peta lampiran per requirement untuk cek status kelengkapan
$attByReq = [];
foreach ($attachments as $a) {
    $attByReq[$a['requirement_id']] = $a;
}

$statusMap = [
    STATUS_DRAFT       => ['label' => 'Draft', 'class' => 'bg-secondary'],
    STATUS_SUBMITTED   => ['label' => 'Diajukan', 'class' => 'bg-warning text-dark'],
    STATUS_IN_PROGRESS => ['label' => 'Diproses', 'class' => 'bg-info'],
    STATUS_APPROVED    => ['label' => 'Disetujui', 'class' => 'bg-success'],
    STATUS_REVISI   => ['label' => 'Revisi', 'class' => 'bg-warning text-dark'],
    STATUS_COMPLETED   => ['label' => 'Selesai', 'class' => 'bg-success'],
];
$st = $statusMap[$document['status']] ?? ['label' => ucfirst($document['status']), 'class' => 'bg-secondary'];

function fmtDate($val) {
    return $val ? date('d/m/Y H:i', strtotime($val)) : '-';
}
?>

<div class="row">
    <div class="col-12 mb-4">

        <!-- Header -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2 py-3">
                <h5 class="mb-0 fw-bold"><i class="fas fa-file-alt me-2"></i>Detail Surat</h5>
                <div class="d-flex gap-2">
                    <?php if (in_array($document['status'], [STATUS_APPROVED, STATUS_COMPLETED])): ?>
                        <a href="?page=print&id=<?php echo $document['id']; ?>" class="btn btn-sm btn-success rounded-pill px-3" target="_blank">
                            <i class="fas fa-print me-1"></i>Cetak
                        </a>
                    <?php endif; ?>
                    <?php if ($document['status'] === STATUS_REVISI && $_SESSION['user_id'] === $document['applicant_id']): ?>
                        <a href="?page=form&edit=<?php echo $document['id']; ?>" class="btn btn-sm btn-warning rounded-pill px-3">
                            <i class="fas fa-edit me-1"></i>Perbaiki Surat
                        </a>
                    <?php endif; ?>
                    <?php if ($document['status'] !== STATUS_DRAFT && hasPermission('delete_approved_documents')): ?>
                        <a href="?page=delete-verified-document&id=<?php echo $document['id']; ?>&from=detail" class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="return confirm('Yakin ingin menghapus surat \'<?php echo htmlspecialchars(addslashes($document['title'])); ?>\'? Tindakan ini tidak dapat dibatalkan.')">
                            <i class="fas fa-trash me-1"></i>Hapus
                        </a>
                    <?php endif; ?>
                    <a href="javascript:history.back()" class="btn btn-sm btn-light rounded-pill px-3">Kembali</a>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-user me-2"></i>Data Pemohon</h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <small class="text-muted d-block">Nama Lengkap</small>
                                <span class="fw-semibold"><?php echo htmlspecialchars($document['applicant_name']); ?></span>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted d-block">NIM</small>
                                <span class="fw-semibold"><?php echo htmlspecialchars($document['nim'] ?? '-'); ?></span>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted d-block">No. HP</small>
                                <span class="fw-semibold"><?php echo htmlspecialchars($document['no_hp'] ?? '-'); ?></span>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted d-block">Fakultas</small>
                                <span class="fw-semibold"><?php echo htmlspecialchars($document['faculty_name'] ?? '-'); ?></span>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted d-block">Program Studi</small>
                                <span class="fw-semibold"><?php echo htmlspecialchars($document['department_name']); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-info-circle me-2"></i>Informasi Surat</h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <small class="text-muted d-block">Judul Surat</small>
                                <span class="fw-semibold"><?php echo htmlspecialchars($document['title']); ?></span>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted d-block">Tipe Surat</small>
                                <?php
                                $typeLabel = $pdo->prepare("SELECT name FROM document_types WHERE code = ?");
                                $typeLabel->execute([$document['type']]);
                                $tn = $typeLabel->fetchColumn();
                                ?>
                                <span class="badge bg-secondary d-inline-block text-start" style="white-space:normal; word-break:break-word; max-width:100%;"><?php echo htmlspecialchars($tn ?: $document['type']); ?></span>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted d-block">Status</small>
                                <span class="badge <?php echo $st['class']; ?> d-inline-block text-start" style="white-space:normal; word-break:break-word; max-width:100%;"><?php echo $st['label']; ?></span>
                            </div>
                            <?php if (!empty($document['description'])): ?>
                                <div class="col-12">
                                    <small class="text-muted d-block">Deskripsi</small>
                                    <p class="mb-0 text-secondary"><?php echo nl2br(htmlspecialchars($document['description'])); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

<?php
// Fetch revision history
$revStmt = $pdo->prepare("SELECT * FROM document_revisions WHERE document_id = ? ORDER BY version ASC");
$revStmt->execute([$id]);
$revisions = $revStmt->fetchAll();

// Fetch approval comment for "Diterima" event
$approveComment = null;
$appStmt = $pdo->prepare("SELECT comment FROM approvals WHERE document_id = ? AND action = 'approve' ORDER BY id DESC LIMIT 1");
$appStmt->execute([$id]);
$appRow = $appStmt->fetch();
if ($appRow && !empty($appRow['comment'])) {
    $approveComment = $appRow['comment'];
}
?>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header py-3">
                <h6 class="mb-0 fw-bold"><i class="fas fa-stream me-2"></i>Riwayat Status</h6>
            </div>
            <div class="card-body p-4">
                <div class="timeline-revision">
                    <?php
                    // Riwayat status dibangun dalam urutan logis:
                    // Pengajuan Pertama -> Revisi v.1 -> Pengajuan Kedua -> Revisi v.2 -> ... -> Diterima
                    $ordinals = ['Pertama', 'Kedua', 'Ketiga', 'Keempat', 'Kelima', 'Keenam', 'Ketujuh', 'Kedelapan', 'Kesembilan', 'Kesepuluh'];
                    $allEvents = [];

                    // 1. Pengajuan pertama kali oleh user (submitted_at selalu tanggal pengajuan pertama)
                    if ($document['submitted_at'] || $document['status'] !== STATUS_DRAFT) {
                        $firstSubmittedAt = $document['submitted_at'] ?: $document['created_at'];
                        $allEvents[] = ['label' => 'Pengajuan Pertama', 'date' => $firstSubmittedAt, 'class' => 'bg-warning text-dark', 'icon' => 'fa-paper-plane', 'comment' => null];
                    }

                    // 2. Siklus revisi & pengajuan ulang
                    $resubmitCount = 0;
                    foreach ($revisions as $rev) {
                        $ver = $rev['version'];
                        if ($rev['revisi_at']) {
                            $allEvents[] = ['label' => "Revisi v.$ver", 'date' => $rev['revisi_at'], 'class' => 'bg-warning', 'icon' => 'fa-rotate-left', 'comment' => $rev['revisi_comment']];
                        }
                        if ($rev['resubmitted_at']) {
                            $resubmitCount++;
                            $nth = $resubmitCount + 1;
                            $label = isset($ordinals[$nth - 1]) ? 'Pengajuan ' . $ordinals[$nth - 1] : 'Pengajuan ke-' . $nth;
                            $allEvents[] = ['label' => $label, 'date' => $rev['resubmitted_at'], 'class' => 'bg-info', 'icon' => 'fa-paper-plane', 'comment' => null];
                        }
                    }

                    // 3. Diterima setelah disetujui admin
                    if ($document['status'] === STATUS_APPROVED || $document['status'] === STATUS_COMPLETED) {
                        $allEvents[] = ['label' => 'Diterima', 'date' => $document['completed_at'] ?: $document['in_progress_at'], 'class' => 'bg-success', 'icon' => 'fa-check-circle', 'comment' => $approveComment];
                    }
                    ?>
                    <?php foreach ($allEvents as $idx => $event): ?>
                        <div class="d-flex align-items-start gap-3 mb-3 pb-3 <?php echo $idx < count($allEvents) - 1 ? 'border-bottom' : ''; ?>">
                            <span class="badge <?php echo $event['class']; ?> fs-6 px-3 py-2 mt-1"><i class="fas <?php echo $event['icon']; ?> me-1"></i><?php echo $event['label']; ?></span>
                            <div class="flex-grow-1">
                                <small class="text-muted d-block mt-1"><i class="fas fa-calendar me-1"></i><?php echo fmtDate($event['date']); ?></small>
                                <?php if (!empty($event['comment'])): ?>
                                    <p class="mb-0 small text-secondary mt-1"><i class="fas fa-comment me-1"></i><?php echo htmlspecialchars($event['comment']); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php if (empty($allEvents)): ?>
                        <p class="text-muted text-center py-3 mb-0"><i class="fas fa-info-circle me-2"></i>Belum ada riwayat status</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header py-3">
                <h6 class="mb-0 fw-bold"><i class="fas fa-paperclip me-2"></i>Syarat & Lampiran</h6>
            </div>
            <div class="card-body p-4">
                <?php if (!empty($requirements)): ?>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Syarat Pengajuan</th>
                                <th>Detail / Nama File</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($requirements as $r):
                                $att = $attByReq[$r['id']] ?? null;
                                $isText = ($r['input_type'] ?? 'file') === 'text';
                                $isList = ($r['input_type'] ?? 'file') === 'list';
                                $listItems = [];
                                if ($isList && $att && trim($att['text_value'] ?? '') !== '') {
                                    $decoded = json_decode($att['text_value'], true);
                                    if (is_array($decoded)) $listItems = $decoded;
                                }
                                $fulfilled = $att !== null && ($isText ? trim($att['text_value'] ?? '') !== '' : ($isList ? !empty($listItems) : !empty($att['file_name'])));
                            ?>
                            <tr>
                                <td>
                                    <?php echo htmlspecialchars($r['description']); ?>
                                    <?php if ($r['is_required']): ?><span class="badge bg-danger ms-1 small">Wajib</span><?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($isList): ?>
                                        <i class="fas fa-list-ol me-1 text-info"></i>
                                        <?php if (!empty($listItems)): ?>
                                            <ol class="mb-0 ps-3">
                                                <?php foreach ($listItems as $li):
                                                    $parts = is_array($li) ? array_values(array_filter(array_map(function($v){ return trim((string)$v); }, $li), function($v){ return $v !== ''; })) : [trim((string)$li)];
                                                ?>
                                                <li><?php echo htmlspecialchars(implode(' — ', $parts)); ?></li>
                                                <?php endforeach; ?>
                                            </ol>
                                        <?php else: ?>
                                            <span class="text-muted"><i class="fas fa-minus me-1"></i>-</span>
                                        <?php endif; ?>
                                    <?php elseif ($isText): ?>
                                        <i class="fas fa-font me-1 text-info"></i>
                                        <?php echo nl2br(htmlspecialchars($att['text_value'] ?? '-')); ?>
                                    <?php elseif ($att && !empty($att['file_name'])): ?>
                                        <?php
                                            $filePath = $att['file_path'] ?? '';
                                            $fileName = htmlspecialchars($att['file_name']);
                                            $fileUrl = upload_url($filePath);
                                            $ext = strtolower(pathinfo($att['file_name'], PATHINFO_EXTENSION));
                                            $iconMap = ['pdf' => 'fa-file-pdf', 'doc' => 'fa-file-word', 'docx' => 'fa-file-word', 'xls' => 'fa-file-excel', 'xlsx' => 'fa-file-excel', 'jpg' => 'fa-file-image', 'jpeg' => 'fa-file-image', 'png' => 'fa-file-image', 'zip' => 'fa-file-zip', 'rar' => 'fa-file-zip'];
                                            $iconClass = $iconMap[$ext] ?? 'fa-file';
                                        ?>
                                        <?php if ($filePath && $fileUrl !== '#'): ?>
                                            <a href="<?php echo htmlspecialchars($fileUrl); ?>" target="_blank" class="text-decoration-none">
                                                <i class="fas <?php echo $iconClass; ?> me-1"></i><?php echo $fileName; ?>
                                            </a>
                                        <?php else: ?>
                                            <i class="fas <?php echo $iconClass; ?> me-1"></i><?php echo $fileName; ?>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted"><i class="fas fa-minus me-1"></i>-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($fulfilled): ?>
                                        <span class="badge bg-success"><i class="fas fa-check me-1"></i>Terlampir</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger"><i class="fas fa-times me-1"></i>Belum dilampirkan</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php elseif (!empty($attachments)): ?>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Syarat Pengajuan</th>
                                <th>Detail / Nama File</th>
                                <th>Ukuran</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($attachments as $att): ?>
                            <?php if (($att['input_type'] ?? '') === 'text'): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($att['req_description'] ?? '-'); ?></td>
                                <td>
                                    <i class="fas fa-font me-1 text-info"></i>
                                    <?php echo nl2br(htmlspecialchars($att['text_value'] ?? '-')); ?>
                                </td>
                                <td>-</td>
                            </tr>
                            <?php else: ?>
                            <?php
                                $filePath = $att['file_path'] ?? '';
                                $fileName = htmlspecialchars($att['file_name']);
                                $fileUrl = upload_url($filePath);
                                $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                                $iconMap = ['pdf' => 'fa-file-pdf', 'doc' => 'fa-file-word', 'docx' => 'fa-file-word', 'xls' => 'fa-file-excel', 'xlsx' => 'fa-file-excel', 'jpg' => 'fa-file-image', 'jpeg' => 'fa-file-image', 'png' => 'fa-file-image', 'zip' => 'fa-file-zip', 'rar' => 'fa-file-zip'];
                                $iconClass = $iconMap[$ext] ?? 'fa-file';
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($att['req_description'] ?? '-'); ?></td>
                                <td>
                                    <?php if ($filePath && $fileUrl !== '#'): ?>
                                        <a href="<?php echo htmlspecialchars($fileUrl); ?>" target="_blank" class="text-decoration-none">
                                            <i class="fas <?php echo $iconClass; ?> me-1"></i><?php echo $fileName; ?>
                                        </a>
                                    <?php else: ?>
                                        <i class="fas <?php echo $iconClass; ?> me-1"></i><?php echo $fileName; ?>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $att['file_size'] ? round($att['file_size'] / 1024, 1) . ' KB' : '-'; ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                    <p class="text-muted text-center py-4 mb-0">
                        <i class="fas fa-info-circle me-2"></i>Tidak ada syarat/lampiran yang ditetapkan untuk tipe surat ini.
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

        <!-- Approval Actions -->
        <?php if (hasPermission('approve_documents') && $document['status'] === STATUS_SUBMITTED): ?>
        <div class="card shadow-sm border-0 mb-4 border-success">
            <div class="card-header py-3">
                <h6 class="mb-0 fw-bold"><i class="fas fa-check-double me-2"></i>Tindakan Persetujuan</h6>
            </div>
            <div class="card-body p-4 text-center">
                <p class="text-muted mb-3">Anda akan menyetujui atau menolak surat ini</p>
                <div class="d-flex gap-3 justify-content-center">
                    <button type="button" class="btn btn-success rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#approveModal">
                        <i class="fas fa-check me-2"></i>Setujui
                    </button>
                    <button type="button" class="btn btn-warning rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#revisiModal">
                        <i class="fas fa-rotate-left me-2"></i>Revisi
                    </button>
                </div>
            </div>
        </div>

        <!-- Approve Confirmation Modal -->
        <div class="modal fade" id="approveModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-check-circle text-success me-2"></i>Setujui Surat</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST" action="<?php echo SITE_URL; ?>">
                        <input type="hidden" name="approve-document" value="1">
                        <input type="hidden" name="id" value="<?php echo $document['id']; ?>">
                        <div class="modal-body">
                            <p>Yakin ingin menyetujui surat <strong><?php echo htmlspecialchars($document['title']); ?></strong>?</p>
                            <div class="mb-3">
                                <label class="form-label">Nomor Surat <span class="text-danger">*</span></label>
                                <input type="text" name="nomor_surat" class="form-control" placeholder="Contoh: LPPM/001/VII/2026" required>
                                <?php if ($lastNomor !== ''): ?>
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>Nomor surat terakhir yang digunakan: <strong><?php echo htmlspecialchars($lastNomor); ?></strong>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-success"><i class="fas fa-check me-1"></i>Setujui</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Revisi Modal -->
        <div class="modal fade" id="revisiModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white;">
                        <h5 class="modal-title"><i class="fas fa-rotate-left me-2"></i>Revisi Surat</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST" action="<?php echo SITE_URL; ?>">
                        <input type="hidden" name="revisi-document" value="1">
                        <input type="hidden" name="id" value="<?php echo $document['id']; ?>">
                        <div class="modal-body">
                            <p>Kirim pesan revisi untuk surat <strong><?php echo htmlspecialchars($document['title']); ?></strong>:</p>
                            <div class="mb-3">
                                <label class="form-label">Pesan Revisi *</label>
                                <textarea name="comment" class="form-control" rows="4" placeholder="Jelaskan apa yang perlu diperbaiki sesuai SOP dan syarat yang berlaku..." required></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-warning"><i class="fas fa-rotate-left me-1"></i>Kirim Revisi</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </div>
</div>

<?php $content = ob_get_clean(); require __DIR__ . '/../layouts/master.php'; ?>
