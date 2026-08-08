<?php
$pdo = db();
$id = $_POST['id'] ?? 0;
$nomor_surat = trim($_POST['nomor_surat'] ?? '');

if (empty($nomor_surat)) {
    $_SESSION['error'] = "Nomor surat wajib diisi!";
    header("Location: ?page=pending");
    exit;
}

$stmt = $pdo->prepare("SELECT id, title, status FROM documents WHERE id = ?");
$stmt->execute([$id]);
$document = $stmt->fetch();

if (!$document) {
    $_SESSION['error'] = "Dokumen tidak ditemukan!";
    header("Location: ?page=detail&id=$id");
    exit;
}

if ($document['status'] !== STATUS_SUBMITTED) {
    $_SESSION['error'] = "Status surat tidak dapat disetujui!";
    header("Location: ?page=detail&id=$id");
    exit;
}

$now = date('Y-m-d H:i:s');

$pdo->prepare("UPDATE documents SET status = ?, approval_stage = 2, in_progress_at = ?, completed_at = ?, document_number = ? WHERE id = ?")
    ->execute([STATUS_APPROVED, $now, $now, $nomor_surat, $id]);

$pdo->prepare("INSERT INTO approvals (document_id, approver_id, action, comment, approved_at) VALUES (?, ?, 'approve', ?, ?)")
    ->execute([$id, $_SESSION['user_id'], 'Nomor Surat: ' . $nomor_surat, $now]);

logActivity('Setujui Surat', 'Menyetujui surat "' . $document['title'] . '" dengan nomor ' . $nomor_surat . ' (id ' . $id . ')');

$_SESSION['success'] = "Surat berhasil disetujui!";
header("Location: ?page=detail&id=$id");
exit;
