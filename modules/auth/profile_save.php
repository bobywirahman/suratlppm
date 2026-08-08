<?php
$pdo = db();
$userId = $_SESSION['user_id'];

$full_name = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$no_hp = trim($_POST['no_hp'] ?? '');
$alamat = trim($_POST['alamat'] ?? '');
$password = $_POST['password'] ?? '';
$konfirmasi = $_POST['konfirmasi_password'] ?? '';

if (empty($full_name) || empty($email)) {
    $_SESSION['error'] = 'Nama lengkap dan email wajib diisi';
    header("Location: " . SITE_URL . "?page=profile");
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = 'Format email tidak valid';
    header("Location: " . SITE_URL . "?page=profile");
    exit;
}

$check = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
$check->execute([$email, $userId]);
if ($check->fetch()) {
    $_SESSION['error'] = 'Email sudah digunakan oleh pengguna lain';
    header("Location: " . SITE_URL . "?page=profile");
    exit;
}

if (!empty($password) || !empty($konfirmasi)) {
    if (strlen($password) < 8) {
        $_SESSION['error'] = 'Password minimal 8 karakter';
        header("Location: " . SITE_URL . "?page=profile");
        exit;
    }
    if ($password !== $konfirmasi) {
        $_SESSION['error'] = 'Konfirmasi password tidak cocok';
        header("Location: " . SITE_URL . "?page=profile");
        exit;
    }
    $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, no_hp = ?, alamat = ?, password = ? WHERE id = ?");
    $stmt->execute([
        htmlspecialchars($full_name, ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($email, ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($no_hp, ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($alamat, ENT_QUOTES, 'UTF-8'),
        password_hash($password, PASSWORD_DEFAULT),
        $userId
    ]);
} else {
    $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, no_hp = ?, alamat = ? WHERE id = ?");
    $stmt->execute([
        htmlspecialchars($full_name, ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($email, ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($no_hp, ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($alamat, ENT_QUOTES, 'UTF-8'),
        $userId
    ]);
}

$_SESSION['success'] = 'Profil berhasil diperbarui';
header("Location: " . SITE_URL . "?page=profile");
exit;
