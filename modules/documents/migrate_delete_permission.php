<?php
require_once __DIR__ . '/../../config/db.php';
$pdo = db();

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

$pdo->exec("INSERT IGNORE INTO permissions (`key`, name, description) VALUES ('delete_approved_documents', 'Hapus Surat Terverifikasi', 'Menghapus surat yang statusnya bukan draft (sudah diajukan/disetujui)')");

$pdo->prepare("INSERT IGNORE INTO role_permissions (role_id, permission_id) SELECT r.id, p.id FROM roles r, permissions p WHERE r.name = 'admin' AND p.`key` = 'delete_approved_documents'")
    ->execute();

$_SESSION['success'] = "Permission 'delete_approved_documents' berhasil dibuat & diberikan ke admin!";
header("Location: ?page=roles");
exit;