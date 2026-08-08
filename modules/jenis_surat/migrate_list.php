<?php
require_once __DIR__ . '/../../config/db.php';
$pdo = db();

$hasList = false;
try {
    $col = $pdo->query("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'type_requirements' AND COLUMN_NAME = 'input_type'")->fetchColumn();
    if ($col && stripos($col, 'list') !== false) $hasList = true;
} catch (Exception $e) {}

if (!$hasList) {
    $pdo->exec("ALTER TABLE type_requirements MODIFY COLUMN input_type ENUM('file','text','list') NOT NULL DEFAULT 'file'");
}

try {
    $pdo->query("SELECT config FROM type_requirements LIMIT 1");
} catch (Exception $e) {
    $pdo->exec("ALTER TABLE type_requirements ADD COLUMN config TEXT DEFAULT NULL AFTER input_type");
}

$_SESSION['success'] = "Database berhasil diperbarui (tipe input List)!";
header("Location: ?page=types");
exit;
