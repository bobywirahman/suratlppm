<?php
$pdo = db();
$id = $_POST['id'] ?? null;
$name = $_POST['name'] ?? '';
$display_name = $_POST['display_name'] ?? '';
$description = $_POST['description'] ?? '';

if (empty($name) || empty($display_name)) {
    $_SESSION['error'] = "Nama role dan nama tampilan wajib diisi";
    header("Location: ?page=roles");
    exit;
}

$check = $pdo->prepare("SELECT id FROM roles WHERE name = ? AND id != ?");
$check->execute([$name, $id ?? 0]);
if ($check->fetch()) {
    $_SESSION['error'] = "Nama role sudah digunakan";
    header("Location: ?page=roles");
    exit;
}

if ($id) {
    $old = $pdo->prepare("SELECT name FROM roles WHERE id = ?");
    $old->execute([$id]);
    $oldRole = $old->fetch();

    $stmt = $pdo->prepare("UPDATE roles SET name=?, display_name=?, description=? WHERE id=?");
    $stmt->execute([$name, $display_name, $description, $id]);
    $_SESSION['success'] = "Role berhasil diupdate";
} else {
    $stmt = $pdo->prepare("INSERT INTO roles (name, display_name, description) VALUES (?, ?, ?)");
    $stmt->execute([$name, $display_name, $description]);
    $_SESSION['success'] = "Role berhasil ditambahkan";
}

header("Location: ?page=roles");
exit;
