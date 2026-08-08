<?php
require_once __DIR__ . '/../../config/db.php';
$pdo = db();

try {
    $pdo->query("SELECT id FROM informasi_lpm LIMIT 1");
} catch (Exception $e) {
    $pdo->exec("CREATE TABLE IF NOT EXISTS informasi_lpm (
        id INT AUTO_INCREMENT PRIMARY KEY,
        judul VARCHAR(255) NOT NULL COMMENT 'Judul informasi',
        konten TEXT DEFAULT NULL COMMENT 'Isi/informasi teks',
        thumbnail VARCHAR(255) DEFAULT NULL COMMENT 'Path gambar thumbnail',
        lampiran VARCHAR(255) DEFAULT NULL COMMENT 'Path lampiran PDF',
        is_active TINYINT(1) DEFAULT 1 COMMENT 'Status publish 1=aktif 0=nonaktif',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
}

// Pastikan tabel permissions & role_permissions ada
$pdo->exec("CREATE TABLE IF NOT EXISTS permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    `key` VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");
$pdo->exec("CREATE TABLE IF NOT EXISTS role_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL,
    permission_id INT NOT NULL,
    UNIQUE KEY unique_role_perm (role_id, permission_id),
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
)");

// Seed permission manage_informasi
$pdo->exec("INSERT IGNORE INTO permissions (`key`, name, description) VALUES ('manage_informasi', 'Informasi LPPM', 'Mengelola informasi LPPM di landing page')");

// Grant ke role admin
$pdo->prepare("INSERT IGNORE INTO role_permissions (role_id, permission_id) SELECT r.id, p.id FROM roles r, permissions p WHERE r.name = 'admin' AND p.`key` = 'manage_informasi'")
    ->execute();

$_SESSION['success'] = "Tabel informasi & permission 'manage_informasi' berhasil dibuat!";
header("Location: ?page=informasi");
exit;