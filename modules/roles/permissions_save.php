<?php
$pdo = db();
$roleId = $_POST['role_id'] ?? null;
$permissions = $_POST['permissions'] ?? [];

if (!$roleId) {
    $_SESSION['error'] = "Role tidak ditemukan";
    header("Location: ?page=roles");
    exit;
}

$check = $pdo->prepare("SELECT id FROM roles WHERE id = ?");
$check->execute([$roleId]);
if (!$check->fetch()) {
    $_SESSION['error'] = "Role tidak ditemukan";
    header("Location: ?page=roles");
    exit;
}

$pdo->prepare("DELETE FROM role_permissions WHERE role_id = ?")->execute([$roleId]);

if (!empty($permissions)) {
    $stmt = $pdo->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
    foreach ($permissions as $permId) {
        $stmt->execute([$roleId, $permId]);
    }
}

$_SESSION['success'] = "Hak akses berhasil diperbarui!";
header("Location: ?page=roles");
exit;
