<?php
$pdo = db();
$id = $_POST['id'] ?? null;
$code = $_POST['code'] ?? '';
$name = $_POST['name'] ?? '';

if (empty($code) || empty($name)) {
    $_SESSION['error'] = "Kode dan nama wajib diisi";
    header("Location: ?page=faculties");
    exit;
}

$check = $pdo->prepare("SELECT id FROM faculties WHERE code = ? AND id != ?");
$check->execute([$code, $id ?? 0]);
if ($check->fetch()) {
    $_SESSION['error'] = "Kode sudah digunakan";
    header("Location: ?page=faculties");
    exit;
}

if ($id) {
    $stmt = $pdo->prepare("UPDATE faculties SET code=?, name=? WHERE id=?");
    $stmt->execute([$code, $name, $id]);
    $_SESSION['success'] = "Fakultas berhasil diupdate";
} else {
    $stmt = $pdo->prepare("INSERT INTO faculties (code, name) VALUES (?, ?)");
    $stmt->execute([$code, $name]);
    $_SESSION['success'] = "Fakultas berhasil ditambahkan";
}

header("Location: ?page=faculties");
exit;
