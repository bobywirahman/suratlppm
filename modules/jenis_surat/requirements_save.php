<?php
$pdo = db();

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM type_requirements WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    $_SESSION['success'] = "Syarat berhasil dihapus";
    header("Location: ?page=types-requirements&id=" . ($_GET['type_id'] ?? 0));
    exit;
}

$type_id = $_POST['type_id'] ?? 0;
$description = trim($_POST['description'] ?? '');
$is_required = isset($_POST['is_required']) ? 1 : 0;
$max_size = max(0, (int)($_POST['max_size'] ?? 2048));

if (empty($description)) {
    $_SESSION['error'] = "Syarat tidak boleh kosong";
    header("Location: ?page=types-requirements&id=$type_id");
    exit;
}

// Check if new columns exist
$hasInputType = false;
try { $pdo->query("SELECT input_type FROM type_requirements LIMIT 1"); $hasInputType = true; } catch (Exception $e) {}

function build_config_from_raw($raw) {
    $lines = preg_split('/\r\n|\r|\n/', trim((string)$raw));
    $cols = [];
    foreach ($lines as $line) {
        $label = trim($line);
        if ($label === '') continue;
        $key = strtolower(trim(preg_replace('/[^A-Za-z0-9]/', ' ', $label)));
        $key = preg_replace('/\s+/', '_', $key);
        if ($key === '') $key = 'kolom';
        $cols[] = ['key' => $key, 'label' => $label];
    }
    if (empty($cols)) $cols[] = ['key' => 'nama', 'label' => 'Nama Anggota'];
    return json_encode($cols);
}

$input_type = $hasInputType ? ($_POST['input_type'] ?? 'file') : 'file';
if (in_array($input_type, ['text', 'list'])) { $max_size = 0; }
$config = null;
if ($input_type === 'list') {
    $config = build_config_from_raw($_POST['config'] ?? '');
}

if (isset($_POST['edit_id'])) {
    if ($hasInputType) {
        $stmt = $pdo->prepare("UPDATE type_requirements SET description=?, is_required=?, max_size=?, input_type=?, config=? WHERE id=?");
        $stmt->execute([$description, $is_required, $max_size, $input_type, $config, $_POST['edit_id']]);
    } else {
        $stmt = $pdo->prepare("UPDATE type_requirements SET description=?, is_required=?, max_size=? WHERE id=?");
        $stmt->execute([$description, $is_required, $max_size, $_POST['edit_id']]);
    }
    $_SESSION['success'] = "Syarat berhasil diupdate";
} else {
    if ($hasInputType) {
        $sort = $pdo->prepare("SELECT COALESCE(MAX(sort_order), 0) + 1 FROM type_requirements WHERE type_id = ?");
        $sort->execute([$type_id]);
        $nextSort = $sort->fetchColumn();
        $stmt = $pdo->prepare("INSERT INTO type_requirements (type_id, description, is_required, max_size, input_type, config, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$type_id, $description, $is_required, $max_size, $input_type, $config, $nextSort]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO type_requirements (type_id, description, is_required, max_size) VALUES (?, ?, ?, ?)");
        $stmt->execute([$type_id, $description, $is_required, $max_size]);
    }
    $_SESSION['success'] = "Syarat berhasil ditambahkan";
}

header("Location: ?page=types-requirements&id=$type_id");
exit;
