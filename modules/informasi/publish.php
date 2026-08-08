<?php
$pdo = db();
$id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("UPDATE informasi_lpm SET is_active = 1 - is_active WHERE id = ?");
$stmt->execute([$id]);

$_SESSION['success'] = "Status informasi diubah";
header("Location: ?page=informasi");
exit;