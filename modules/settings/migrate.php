<?php
require_once __DIR__ . '/../../config/db.php';
$pdo = db();

try {
    $pdo->query("SELECT id FROM app_settings LIMIT 1");
} catch (Exception $e) {
    $pdo->exec("CREATE TABLE app_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(50) NOT NULL UNIQUE,
        setting_value TEXT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
}

$_SESSION['success'] = "Tabel pengaturan berhasil dibuat!";
header("Location: ?page=settings");
exit;
