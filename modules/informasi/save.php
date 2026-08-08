<?php
$pdo = db();
$id = $_POST['id'] ?? null;
$judul = trim($_POST['judul'] ?? '');
$konten = $_POST['konten'] ?? '';

if ($judul === '') {
    $_SESSION['error'] = "Judul wajib diisi";
    header("Location: ?page=informasi");
    exit;
}

$uploadDir = __DIR__ . '/../../uploads/informasi/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

function infoFullPath($url) {
    if (strpos($url, '/') === 0) {
        return __DIR__ . '/../..' . str_replace('/', DIRECTORY_SEPARATOR, substr($url, strlen('')));
    }
    return $url;
}

$old = ['thumbnail' => '', 'lampiran' => ''];
if ($id) {
    $stmt = $pdo->prepare("SELECT thumbnail, lampiran FROM informasi_lpm WHERE id = ?");
    $stmt->execute([$id]);
    $old = $stmt->fetch() ?: $old;
    if (!$old) { $_SESSION['error'] = "Data tidak ditemukan"; header("Location: ?page=informasi"); exit; }
}

$thumbnail = $old['thumbnail'] ?? '';
$lampiran = $old['lampiran'] ?? '';

// Upload thumbnail (gambar)
if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
    $ext = strtolower(pathinfo($_FILES['thumbnail']['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
        $_SESSION['error'] = "Format thumbnail tidak diizinkan (jpg, jpeg, png, gif, webp)";
        header("Location: ?page=informasi&action=edit&id=" . ($id ?: ''));
        exit;
    }
    $name = 'thumb_' . uniqid() . '.' . $ext;
    if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $uploadDir . $name)) {
        if ($thumbnail && file_exists(infoFullPath($thumbnail))) unlink(infoFullPath($thumbnail));
        $thumbnail = '/uploads/informasi/' . $name;
    }
}
if (!empty($_POST['thumbnail_remove']) && $thumbnail) {
    if (file_exists(infoFullPath($thumbnail))) unlink(infoFullPath($thumbnail));
    $thumbnail = '';
}

// Upload lampiran (PDF)
if (isset($_FILES['lampiran']) && $_FILES['lampiran']['error'] === UPLOAD_ERR_OK) {
    $ext = strtolower(pathinfo($_FILES['lampiran']['name'], PATHINFO_EXTENSION));
    if ($ext !== 'pdf') {
        $_SESSION['error'] = "Lampiran hanya boleh berformat PDF";
        header("Location: ?page=informasi&action=edit&id=" . ($id ?: ''));
        exit;
    }
    $name = 'lamp_' . uniqid() . '.pdf';
    if (move_uploaded_file($_FILES['lampiran']['tmp_name'], $uploadDir . $name)) {
        if ($lampiran && file_exists(infoFullPath($lampiran))) unlink(infoFullPath($lampiran));
        $lampiran = '/uploads/informasi/' . $name;
    }
}
if (!empty($_POST['lampiran_remove']) && $lampiran) {
    if (file_exists(infoFullPath($lampiran))) unlink(infoFullPath($lampiran));
    $lampiran = '';
}

if ($id) {
    $pdo->prepare("UPDATE informasi_lpm SET judul = ?, konten = ?, thumbnail = ?, lampiran = ? WHERE id = ?")
        ->execute([$judul, $konten, $thumbnail, $lampiran, $id]);
    $_SESSION['success'] = "Informasi berhasil diupdate";
} else {
    $pdo->prepare("INSERT INTO informasi_lpm (judul, konten, thumbnail, lampiran) VALUES (?, ?, ?, ?)")
        ->execute([$judul, $konten, $thumbnail, $lampiran]);
    $_SESSION['success'] = "Informasi berhasil ditambahkan";
}

header("Location: ?page=informasi");
exit;