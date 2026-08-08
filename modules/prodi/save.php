<?php
$pdo = db();
$id = $_POST['id'] ?? null;
$code = $_POST['code'] ?? '';
$name = $_POST['name'] ?? '';
$faculty_id = $_POST['faculty_id'] ?: null;

if (empty($code) || empty($name)) {
    $_SESSION['error'] = "Kode dan nama wajib diisi";
    header("Location: ?page=prodi");
    exit;
}

$check = $pdo->prepare("SELECT id FROM departments WHERE code = ? AND id != ?");
$check->execute([$code, $id ?? 0]);
if ($check->fetch()) {
    $_SESSION['error'] = "Kode sudah digunakan";
    header("Location: ?page=prodi");
    exit;
}

if ($id) {
    $stmt = $pdo->prepare("UPDATE departments SET code=?, name=?, faculty_id=? WHERE id=?");
    $stmt->execute([$code, $name, $faculty_id, $id]);
    $_SESSION['success'] = "Program Studi berhasil diupdate";
} else {
    $stmt = $pdo->prepare("INSERT INTO departments (code, name, faculty_id) VALUES (?, ?, ?)");
    $stmt->execute([$code, $name, $faculty_id]);
    $_SESSION['success'] = "Program Studi berhasil ditambahkan";
}

header("Location: ?page=prodi");
exit;
