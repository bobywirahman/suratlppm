<?php
$pdo = db();
$id = $_POST['id'] ?? null;

// Kembali ke daftar sesuai pencarian/pagination sebelumnya (hanya izinkan key valid)
$back = $_POST['back'] ?? '';
if ($back !== '') {
    parse_str($back, $backParts);
    if (!empty(array_diff(array_keys($backParts), ['q', 'per_page', 'status', 'p']))) $back = '';
    $back = preg_replace('/[^a-zA-Z0-9_%&=+ ]/', '', $back);
}
$redirect = '?page=users' . ($back !== '' ? '&' . $back : '');

// Reset password user (halaman users-save hanya untuk admin)
if (isset($_POST['reset_password'])) {
    $resetId = (int)($_POST['id'] ?? 0);
    $newPass = $_POST['new_password'] ?? '';
    $newPassConfirm = $_POST['new_password_confirm'] ?? '';
    $adminIds = $pdo->query("SELECT user_id FROM user_roles WHERE role_id = (SELECT id FROM roles WHERE name = 'admin')")->fetchAll(PDO::FETCH_COLUMN);
    $resetGuard = empty($adminIds) ? '1=1' : 'id NOT IN (' . implode(',', $adminIds) . ')';
    $target = $pdo->prepare("SELECT id, full_name, username FROM users WHERE id = ? AND $resetGuard");
    $target->execute([$resetId]);
    $targetUser = $target->fetch();
    if (!$targetUser) {
        $_SESSION['error'] = "User tidak ditemukan atau password akun admin tidak dapat direset";
        header("Location: " . $redirect);
        exit;
    }
    if (strlen($newPass) < 8) {
        $_SESSION['error'] = "Password minimal 8 karakter";
        header("Location: " . $redirect);
        exit;
    }
    if ($newPass !== $newPassConfirm) {
        $_SESSION['error'] = "Konfirmasi password tidak cocok";
        header("Location: " . $redirect);
        exit;
    }
    $pdo->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([password_hash($newPass, PASSWORD_DEFAULT), $resetId]);
    logActivity('Reset Password', 'Merreset password user "' . $targetUser['full_name'] . '" (id ' . $resetId . ')');
    $_SESSION['success'] = "Password user \"" . $targetUser['full_name'] . "\" berhasil direset. Password baru: " . $newPass;
    header("Location: " . $redirect);
    exit;
}

$full_name = trim($_POST['full_name'] ?? '');
$username = trim($_POST['username'] ?? '') ?: null;
$nip = trim($_POST['nip'] ?? '') ?: null;
$nim = trim($_POST['nim'] ?? '') ?: null;
$no_hp = trim($_POST['no_hp'] ?? '') ?: null;
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$role = $_POST['role'] ?? 'mahasiswa';
$department_id = $_POST['department_id'] ?: null;
$alamat = trim($_POST['alamat'] ?? '');
$is_active = isset($_POST['is_active']) ? 1 : 0;

if (empty($full_name) || empty($email)) {
    $_SESSION['error'] = "Nama dan email wajib diisi";
    header("Location: " . $redirect);
    exit;
}

$valid = $pdo->prepare("SELECT id FROM roles WHERE name = ?");
$valid->execute([$role]);
$roleRow = $valid->fetch();
if (!$roleRow) {
    $_SESSION['error'] = "Role tidak valid";
    header("Location: " . $redirect);
    exit;
}
$role_id = $roleRow['id'];

$check = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
$check->execute([$email, $id ?? 0]);
if ($check->fetch()) {
    $_SESSION['error'] = "Email sudah digunakan";
    header("Location: " . $redirect);
    exit;
}

if ($username) {
    $check = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
    $check->execute([$username, $id ?? 0]);
    if ($check->fetch()) {
        $_SESSION['error'] = "Username sudah digunakan";
        header("Location: " . $redirect);
        exit;
    }
}

if ($id) {
    if (!empty($password)) {
        $stmt = $pdo->prepare("UPDATE users SET full_name=?, username=?, nip=?, nim=?, no_hp=?, email=?, password=?, department_id=?, alamat=?, is_active=? WHERE id=?");
        $stmt->execute([$full_name, $username, $nip, $nim, $no_hp, $email, password_hash($password, PASSWORD_DEFAULT), $department_id, $alamat, $is_active, $id]);
    } else {
        $stmt = $pdo->prepare("UPDATE users SET full_name=?, username=?, nip=?, nim=?, no_hp=?, email=?, department_id=?, alamat=?, is_active=? WHERE id=?");
        $stmt->execute([$full_name, $username, $nip, $nim, $no_hp, $email, $department_id, $alamat, $is_active, $id]);
    }
    $pdo->prepare("DELETE FROM user_roles WHERE user_id = ?")->execute([$id]);
    $pdo->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)")->execute([$id, $role_id]);
    logActivity('Update User', 'Memperbarui user "' . $full_name . '" (id ' . $id . ')');
    $_SESSION['success'] = "User berhasil diupdate";
} else {
    $stmt = $pdo->prepare("INSERT INTO users (full_name, username, nip, nim, no_hp, email, password, department_id, alamat, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$full_name, $username, $nip, $nim, $no_hp, $email, password_hash($password, PASSWORD_DEFAULT), $department_id, $alamat, $is_active]);
    $newUserId = $pdo->lastInsertId();
    $pdo->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)")->execute([$newUserId, $role_id]);
    logActivity('Tambah User', 'Menambahkan user "' . $full_name . '" (id ' . $newUserId . ')');
    $_SESSION['success'] = "User berhasil ditambahkan";
}

header("Location: " . $redirect);
exit;
