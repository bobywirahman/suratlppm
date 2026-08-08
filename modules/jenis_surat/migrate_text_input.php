<?php
require_once __DIR__ . '/../../config/db.php';
$pdo = db();

try {
    $pdo->query("SELECT input_type FROM type_requirements LIMIT 1");
} catch (Exception $e) {
    $pdo->exec("ALTER TABLE type_requirements ADD COLUMN input_type ENUM('file','text') DEFAULT 'file' AFTER max_size");
}
try {
    $pdo->query("SELECT sort_order FROM type_requirements LIMIT 1");
} catch (Exception $e) {
    $pdo->exec("ALTER TABLE type_requirements ADD COLUMN sort_order INT DEFAULT 0 AFTER input_type");
}
try {
    $pdo->query("SELECT text_value FROM document_attachments LIMIT 1");
} catch (Exception $e) {
    $pdo->exec("ALTER TABLE document_attachments ADD COLUMN text_value TEXT DEFAULT NULL AFTER file_size");
}

$_SESSION['success'] = "Database berhasil diperbarui!";
header("Location: ?page=types");
exit;
