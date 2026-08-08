<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/constant.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: " . SITE_URL);
    exit;
}

if (!isset($_POST['submit']) && !isset($_POST['save_draft'])) {
    header("Location: ?page=form");
    exit;
}

$pdo = db();
$title = $_POST['title'] ?? '';
$department_id = $_POST['department_id'] ?? 0;
$type = $_POST['type'] ?? '';
$description = $_POST['description'] ?? '';
$academic_year_id = $_POST['academic_year_id'] ?: null;
$editId = $_POST['edit_id'] ?? null;

$isDraft = isset($_POST['save_draft']);
$status = $isDraft ? STATUS_DRAFT : STATUS_SUBMITTED;

if (empty($title) || empty($type) || empty($department_id)) {
    $_SESSION['error'] = "Lengkapi semua field wajib!";
    header("Location: ?page=form");
    exit;
}

$now = date('Y-m-d H:i:s');
$submittedAt = $isDraft ? null : $now;

if ($editId) {
    // Update existing draft, rejected, or revisi document
    $checkStmt = $pdo->prepare("SELECT id, status, submitted_at FROM documents WHERE id = ? AND applicant_id = ? AND (status = ? OR status = ? OR status = ?)");
    $checkStmt->execute([$editId, $_SESSION['user_id'], STATUS_DRAFT, STATUS_REJECTED, STATUS_REVISI]);
    $existing = $checkStmt->fetch();
    if (!$existing) {
        $_SESSION['error'] = "Dokumen tidak ditemukan!";
        header("Location: ?page=form");
        exit;
    }
    // Pertahankan tanggal pengajuan pertama; isi hanya jika belum pernah diajukan
    $finalSubmittedAt = $existing['submitted_at'] ?: $submittedAt;
    $updateStmt = $pdo->prepare("UPDATE documents SET title=?, type=?, description=?, academic_year_id=?, status=?, submitted_at=?, approval_stage=0, rejected_at=NULL WHERE id=?");
    $updateStmt->execute([$title, $type, $description, $academic_year_id, $status, $finalSubmittedAt, $editId]);
    $newDocumentId = $editId;

    // Catat pengajuan ulang bila surat (revisi/draft) diajukan dan masih ada revisi yang belum diajukan ulang
    if ($status === STATUS_SUBMITTED) {
        $revStmt = $pdo->prepare("UPDATE document_revisions SET resubmitted_at = ? WHERE document_id = ? AND resubmitted_at IS NULL ORDER BY version DESC LIMIT 1");
        $revStmt->execute([$now, $editId]);
    }
} else {
    // Insert new document
    $insertStmt = $pdo->prepare("INSERT INTO documents 
        (title, type, applicant_id, department_id, academic_year_id, description, created_by, status, created_at, submitted_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if (!$insertStmt->execute([$title, $type, $_SESSION['user_id'], $department_id, $academic_year_id, $description, $_SESSION['user_id'], $status, $now, $submittedAt])) {
        $_SESSION['error'] = "Gagal menyimpan surat!";
        header("Location: ?page=form");
        exit;
    }
    $newDocumentId = $pdo->lastInsertId();
}

// Handle syarat_text per requirement
if (!empty($_POST['syarat_text']) && is_array($_POST['syarat_text'])) {
    foreach ($_POST['syarat_text'] as $reqId => $textValue) {
        $textValue = trim($textValue);
        $delStmt = $pdo->prepare("DELETE FROM document_attachments WHERE document_id = ? AND requirement_id = ?");
        $delStmt->execute([$newDocumentId, $reqId]);
        if ($textValue === '') continue;
        $insStmt = $pdo->prepare("INSERT INTO document_attachments (document_id, file_name, file_path, file_type, file_size, requirement_id, text_value) VALUES (?, '', '', '', 0, ?, ?)");
        $insStmt->execute([$newDocumentId, $reqId, $textValue]);
    }
}

// Handle syarat_list (interactive list / daftar anggota) per requirement
if (!empty($_POST['syarat_list']) && is_array($_POST['syarat_list'])) {
    foreach ($_POST['syarat_list'] as $reqId => $rows) {
        if (!is_array($rows)) continue;
        // rows = [ 0 => [colKey => value], 1 => [...] ]
        $items = [];
        foreach ($rows as $row) {
            if (!is_array($row)) continue;
            $cleaned = [];
            foreach ($row as $col => $val) {
                $val = trim((string)$val);
                if ($val === '') continue;
                $cleaned[$col] = $val;
            }
            if (!empty($cleaned)) $items[] = $cleaned;
        }
        $delStmt = $pdo->prepare("DELETE FROM document_attachments WHERE document_id = ? AND requirement_id = ?");
        $delStmt->execute([$newDocumentId, $reqId]);
        if (empty($items)) continue;
        $insStmt = $pdo->prepare("INSERT INTO document_attachments (document_id, file_name, file_path, file_type, file_size, requirement_id, text_value) VALUES (?, '', '', '', 0, ?, ?)");
        $insStmt->execute([$newDocumentId, $reqId, json_encode(array_values($items))]);
    }
}

// Handle syarat_file per requirement
$syaratUploadDir = __DIR__ . '/../../uploads/syarat/';
if (!is_dir($syaratUploadDir)) mkdir($syaratUploadDir, 0755, true);

$errors = [];
if (!empty($_FILES['syarat_file']['name']) && is_array($_FILES['syarat_file']['name'])) {
    foreach ($_FILES['syarat_file']['name'] as $reqId => $filename) {
        if (empty($filename) || $_FILES['syarat_file']['error'][$reqId] !== UPLOAD_ERR_OK) continue;
        $reqStmt = $pdo->prepare("SELECT description, max_size FROM type_requirements WHERE id = ?");
        $reqStmt->execute([$reqId]);
        $req = $reqStmt->fetch();
        if (!$req) continue;
        $maxSizeBytes = ($req['max_size'] ?? 0) * 1024;
        $fileSize = $_FILES['syarat_file']['size'][$reqId];
        if ($maxSizeBytes > 0 && $fileSize > $maxSizeBytes) {
            $sizeLabel = $req['max_size'] >= 1024 ? round($req['max_size'] / 1024, 1) . ' MB' : $req['max_size'] . ' KB';
            $errors[] = "File untuk \"" . htmlspecialchars($req['description']) . "\" melebihi batas maksimal " . $sizeLabel;
            continue;
        }
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $allowed = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'zip', 'rar'];
        if (!in_array($ext, $allowed)) continue;
        $newName = 'syarat_' . $newDocumentId . '_' . $reqId . '_' . uniqid() . '.' . $ext;
        $target = $syaratUploadDir . $newName;
        if (move_uploaded_file($_FILES['syarat_file']['tmp_name'][$reqId], $target)) {
            // Delete old file + DB record for this requirement if exists
            $oldStmt = $pdo->prepare("SELECT file_path FROM document_attachments WHERE document_id = ? AND requirement_id = ?");
            $oldStmt->execute([$newDocumentId, $reqId]);
            $oldFile = $oldStmt->fetchColumn();
            if ($oldFile) {
                $oldAbs = upload_path($oldFile);
                if ($oldAbs && file_exists($oldAbs)) unlink($oldAbs);
            }
            $delStmt = $pdo->prepare("DELETE FROM document_attachments WHERE document_id = ? AND requirement_id = ?");
            $delStmt->execute([$newDocumentId, $reqId]);
            $attStmt = $pdo->prepare("INSERT INTO document_attachments (document_id, file_name, file_path, file_type, file_size, requirement_id) VALUES (?, ?, ?, ?, ?, ?)");
            $attStmt->execute([$newDocumentId, basename($filename), '/uploads/syarat/' . $newName, $ext, $fileSize, $reqId]);
        }
    }
}

if (!empty($errors)) {
    $_SESSION['error'] = "Beberapa file ditolak:\n- " . implode("\n- ", $errors);
    $msg = $isDraft ? "Surat disimpan sebagai draft, namun beberapa file gagal diupload." : "Surat berhasil diajukan, namun beberapa file gagal diupload.";
    $_SESSION['success'] = $msg;
    header("Location: ?page=form");
    exit;
}

if ($isDraft) {
    logActivity('Simpan Draft', 'Menyimpan surat sebagai draft (id ' . $newDocumentId . ')');
    $_SESSION['success'] = $editId ? "Perubahan draft berhasil disimpan!" : "Surat disimpan sebagai draft!";
} else {
    logActivity('Ajukan Surat', 'Mengajukan surat "' . $title . '" (id ' . $newDocumentId . ')');
    $_SESSION['success'] = $editId ? "Draft berhasil diajukan!" : "Surat berhasil diajukan!";
}
header("Location: ?page=list");
exit;