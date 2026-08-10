<?php
function url($path = '') {
    $base = rtrim(BASE_URL, '/');
    $p = '/' . ltrim($path, '/');
    if ($p === '/') return $base === '' ? '/' : $base . '/';
    if ($base !== '') {
        $prefix = $base . '/';
        // Collapse repeated base prefixes so corrupt stored paths still resolve.
        while (strpos($p, $prefix) === 0) {
            $p = substr($p, strlen($base));
        }
    }
    return $base . $p;
}

function asset($path = '') {
    if ($path === '') return '';
    if (preg_match('#^https?://#i', $path)) return $path;
    return url($path);
}

// Absolute server path where the application lives (project root)
function app_root() {
    return dirname(__DIR__);
}

// Convert a DB file_path (root-relative "/uploads/..." OR absolute) into a flexible URL
function upload_url($file_path) {
    $fp = str_replace('\\', '/', (string)$file_path);
    if ($fp === '') return '';
    if (preg_match('#^https?://#i', $fp)) return $fp;
    if (strpos($fp, '/uploads/') === 0) {
        return url($fp);
    }
    $docRoot = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
    if ($docRoot && strpos($fp, $docRoot) === 0) {
        return url(substr($fp, strlen($docRoot)));
    }
    $pos = strpos($fp, '/uploads/');
    if ($pos !== false) return url(substr($fp, $pos));
    return '#';
}

// Convert a DB file_path into an absolute server path for file_exists/unlink
function upload_path($file_path) {
    $fp = str_replace('\\', '/', (string)$file_path);
    if (preg_match('#^/?uploads/#', $fp)) {
        return app_root() . '/' . ltrim($fp, '/');
    }
    return $file_path;
}

// Normalize any stored path (root-relative, base-prefixed/corrupt, external) to
// a clean root-relative form for DB storage. External http(s) URLs pass through.
function upload_rel($file_path) {
    $fp = str_replace('\\', '/', (string)$file_path);
    if (preg_match('#^https?://#i', $fp)) return $fp;
    $pos = strpos($fp, '/uploads/');
    if ($pos !== false) return substr($fp, $pos);
    return ltrim($fp, '/');
}

function hasPermission($permissionKey) {
    global $pdo, $user;
    if (!$user || !isset($user['id'])) return false;
    if ($user['role'] === 'admin') return true;
    try {
        $pdo->query("SELECT 1 FROM permissions LIMIT 1");
    } catch (Exception $e) {
        return false;
    }
    static $perms = [];
    if (!isset($perms[$user['id']])) {
        $stmt = $pdo->prepare("SELECT p.`key` FROM permissions p
            JOIN role_permissions rp ON p.id = rp.permission_id
            JOIN user_roles ur ON rp.role_id = ur.role_id
            WHERE ur.user_id = ?");
        $stmt->execute([$user['id']]);
        $userPerms = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $perms[$user['id']] = array_flip($userPerms);
    }
    return isset($perms[$user['id']][$permissionKey]);
}

function getSettings() {
    static $settings = null;
    if ($settings === null) {
        $settings = [];
        try {
            $pdo = db();
            $stmt = $pdo->query("SELECT setting_key, setting_value FROM app_settings");
            foreach ($stmt->fetchAll() as $s) {
                $settings[$s['setting_key']] = $s['setting_value'];
            }
        } catch (Exception $e) {}
    }
    return $settings;
}

function getSetting($key, $default = '') {
    $settings = getSettings();
    return $settings[$key] ?? $default;
}

function logActivity($action, $details = '') {
    try {
        $pdo = db();
        $userId = $_SESSION['user_id'] ?? null;
        $stmt = $pdo->prepare("INSERT INTO user_activity (user_id, action, details) VALUES (?, ?, ?)");
        $stmt->execute([$userId, $action, $details]);
    } catch (Exception $e) {}
}

function waNumber($phone) {
    $phone = preg_replace('/[^0-9]/', '', (string)$phone);
    if ($phone === '') return '';
    if (strpos($phone, '0') === 0) {
        $phone = '62' . substr($phone, 1);
    }
    return $phone;
}

function tanggalIndonesia($tanggal = null) {
    $bulan = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',
              7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
    $ts = ($tanggal === null || $tanggal === '') ? time() : strtotime($tanggal);
    return date('j', $ts) . ' ' . $bulan[(int)date('n', $ts)] . ' ' . date('Y', $ts);
}
