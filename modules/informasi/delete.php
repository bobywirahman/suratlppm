<?php
$pdo = db();
$id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("SELECT thumbnail, lampiran FROM informasi_lpm WHERE id = ?");
$stmt->execute([$id]);
$info = $stmt->fetch();

if ($info) {
    foreach (['thumbnail', 'lampiran'] as $col) {
        if (!empty($info[$col])) {
            $fullPath = str_replace('/', DIRECTORY_SEPARATOR, substr($info[$col], strlen('')));
            $fullPath = __DIR__ . '/../..' . $fullPath;
            if (file_exists($fullPath)) unlink($fullPath);
        }
    }
    $pdo->prepare("DELETE FROM informasi_lpm WHERE id = ?")->execute([$id]);
    $_SESSION['success'] = "Informasi berhasil dihapus";
} else {
    $_SESSION['error'] = "Data tidak ditemukan";
}

header("Location: ?page=informasi");
exit;