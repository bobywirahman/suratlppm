<?php
$pdo = db();

$uploadDir = __DIR__ . '/../../uploads/settings/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

function saveSetting($key, $value) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO app_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
    $stmt->execute([$key, $value, $value]);
}

// Save text fields
saveSetting('site_name', trim($_POST['site_name'] ?? APP_NAME));
saveSetting('site_description', trim($_POST['site_description'] ?? ''));
saveSetting('admin_phone', trim($_POST['admin_phone'] ?? ''));

// Handle favicon upload
if (isset($_FILES['favicon']) && $_FILES['favicon']['error'] === UPLOAD_ERR_OK) {
    $ext = strtolower(pathinfo($_FILES['favicon']['name'], PATHINFO_EXTENSION));
    if (in_array($ext, ['ico', 'png'])) {
        $name = 'favicon.' . $ext;
        $target = $uploadDir . $name;
        move_uploaded_file($_FILES['favicon']['tmp_name'], $target);
        saveSetting('favicon', '/uploads/settings/' . $name);
    }
}
if (!empty($_POST['delete_favicon'])) {
    $old = $pdo->prepare("SELECT setting_value FROM app_settings WHERE setting_key = 'favicon'");
    $old->execute();
    $oldFile = $old->fetchColumn();
    if ($oldFile) {
        $path = __DIR__ . '/../..' . $oldFile;
        if (file_exists($path)) unlink($path);
    }
    saveSetting('favicon', '');
}

// Handle logo upload
if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
    $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
    if (in_array($ext, ['png', 'jpg', 'jpeg', 'svg'])) {
        $name = 'logo.' . $ext;
        $target = $uploadDir . $name;
        move_uploaded_file($_FILES['logo']['tmp_name'], $target);
        saveSetting('logo', '/uploads/settings/' . $name);
    }
}
if (!empty($_POST['delete_logo'])) {
    $old = $pdo->prepare("SELECT setting_value FROM app_settings WHERE setting_key = 'logo'");
    $old->execute();
    $oldFile = $old->fetchColumn();
    if ($oldFile) {
        $path = __DIR__ . '/../..' . $oldFile;
        if (file_exists($path)) unlink($path);
    }
    saveSetting('logo', '');
}

$_SESSION['success'] = "Pengaturan berhasil disimpan!";
header("Location: ?page=settings");
exit;
