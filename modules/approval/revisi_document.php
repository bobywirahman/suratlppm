<?php
$pdo = db();
$id = $_POST['id'] ?? 0;
$comment = trim($_POST['comment'] ?? '');

if (empty($comment)) {
    $_SESSION['error'] = "Pesan revisi wajib diisi!";
    header("Location: ?page=detail&id=$id");
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
    $_SESSION['error'] = "Status surat tidak dapat direvisi!";
    header("Location: ?page=detail&id=$id");
    exit;
}

$now = date('Y-m-d H:i:s');

$pdo->prepare("UPDATE documents SET status = ?, in_progress_at = ?, completed_at = NULL, rejected_at = NULL WHERE id = ?")
    ->execute([STATUS_REVISI, $now, $id]);

$pdo->prepare("INSERT INTO approvals (document_id, approver_id, action, comment, approved_at) VALUES (?, ?, 'revisi', ?, ?)")
    ->execute([$id, $_SESSION['user_id'], $comment, $now]);

$versionStmt = $pdo->prepare("SELECT COALESCE(MAX(version), 0) + 1 as next_version FROM document_revisions WHERE document_id = ?");
$versionStmt->execute([$id]);
$nextVersion = $versionStmt->fetch()['next_version'];

$pdo->prepare("INSERT INTO document_revisions (document_id, version, revisi_comment, reviser_id, revisi_at) VALUES (?, ?, ?, ?, ?)")
    ->execute([$id, $nextVersion, $comment, $_SESSION['user_id'], $now]);

logActivity('Revisi Surat', 'Menetapkan revisi pada surat "' . $document['title'] . '" (id ' . $id . ')');

$_SESSION['success'] = "Surat dikembalikan untuk revisi!";
header("Location: ?page=detail&id=$id");
exit;