<?php
$pdo = db();
$id = $_POST['id'] ?? null;
$code = $_POST['code'] ?? '';
$name = $_POST['name'] ?? '';
$description = $_POST['description'] ?? '';
$is_active = isset($_POST['is_active']) ? 1 : 0;
$roleIds = [];
foreach ((array)($_POST['role_ids'] ?? []) as $rid) {
    if (ctype_digit((string)$rid)) $roleIds[] = (int)$rid;
}
$roleIds = array_unique($roleIds);

if (empty($code) || empty($name)) {
    $_SESSION['error'] = "Kode dan nama wajib diisi";
    header("Location: ?page=types");
    exit;
}

// Check duplicate code
$check = $pdo->prepare("SELECT id FROM document_types WHERE code = ? AND id != ?");
$check->execute([$code, $id ?? 0]);
if ($check->fetch()) {
    $_SESSION['error'] = "Kode sudah digunakan";
    header("Location: ?page=types");
    exit;
}

if ($id) {
    $stmt = $pdo->prepare("UPDATE document_types SET code=?, name=?, description=?, is_active=? WHERE id=?");
    $stmt->execute([$code, $name, $description, $is_active, $id]);
    $_SESSION['success'] = "Jenis surat berhasil diupdate";
} else {
    $stmt = $pdo->prepare("INSERT INTO document_types (code, name, description, is_active) VALUES (?, ?, ?, ?)");
    $stmt->execute([$code, $name, $description, $is_active]);
    $id = (int)$pdo->lastInsertId();
    $_SESSION['success'] = "Jenis surat berhasil ditambahkan";
}

// Simpan relasi role untuk jenis surat ini
$pdo->prepare("DELETE FROM document_type_roles WHERE type_id = ?")->execute([$id]);
$roleStmt = $pdo->prepare("INSERT INTO document_type_roles (type_id, role_id) VALUES (?, ?)");
foreach ($roleIds as $rid) {
    $roleStmt->execute([$id, $rid]);
}

header("Location: ?page=types");
exit;
