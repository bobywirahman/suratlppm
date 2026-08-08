<?php
$pdo = db();
$id = $_POST['id'] ?? null;
$name = trim($_POST['name'] ?? '');

if (empty($name)) {
    $_SESSION['error'] = "Nama tahun ajaran wajib diisi";
    header("Location: ?page=academic-years");
    exit;
}

$check = $pdo->prepare("SELECT id FROM academic_years WHERE name = ? AND id != ?");
$check->execute([$name, $id ?? 0]);
if ($check->fetch()) {
    $_SESSION['error'] = "Nama tahun ajaran sudah digunakan";
    header("Location: ?page=academic-years");
    exit;
}

if ($id) {
    $stmt = $pdo->prepare("UPDATE academic_years SET name=? WHERE id=?");
    $stmt->execute([$name, $id]);
    $_SESSION['success'] = "Tahun ajaran berhasil diupdate";
} else {
    $stmt = $pdo->prepare("INSERT INTO academic_years (name) VALUES (?)");
    $stmt->execute([$name]);
    $_SESSION['success'] = "Tahun ajaran berhasil ditambahkan";
}

header("Location: ?page=academic-years");
exit;
