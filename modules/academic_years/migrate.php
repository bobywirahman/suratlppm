<?php
require_once __DIR__ . '/../../config/db.php';
$pdo = db();

try {
    $pdo->query("SELECT id FROM academic_years LIMIT 1");
} catch (Exception $e) {
    $pdo->exec("CREATE TABLE academic_years (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
}

try {
    $pdo->query("SELECT academic_year_id FROM documents LIMIT 1");
} catch (Exception $e) {
    $pdo->exec("ALTER TABLE documents ADD COLUMN academic_year_id INT DEFAULT NULL AFTER department_id");
}

$_SESSION['success'] = "Database berhasil diperbarui!";
header("Location: ?page=academic-years");
exit;
