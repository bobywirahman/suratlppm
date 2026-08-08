<?php
$pdo = db();
$id = $_POST['id'] ?? 0;
$template = $_POST['template'] ?? '';
$stmt = $pdo->prepare("UPDATE document_types SET template = ? WHERE id = ?");
$stmt->execute([$template, $id]);
$_SESSION['success'] = "Template berhasil disimpan";
header("Location: ?page=types");
exit;
