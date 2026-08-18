<?php
/**
 * audit_security.php
 * Audit file & deteksi injeksi/webshell di luar sistem.
 *
 * CLI : php audit_security.php [--baseline] [--json]
 * Web : audit_security.php?action=audit    (wajib login admin)
 *       audit_security.php?action=baseline (regenerate manifest, wajib admin)
 */

/* ===================== KONFIGURASI ===================== */
define('AUDIT_MANIFEST_FILE', __DIR__ . '/audit_manifest.json');
define('AUDIT_MAX_READ', 2097152);              // batas baca file (byte)
define('AUDIT_RECENT_DAYS', 7);                 // tanda "baru dimodifikasi" (hari)
define('AUDIT_ALLOW_IPS', []);                  // kosong = tanpa batasan IP; isi mis. ['203.0.113.5']
define('AUDIT_EXCLUDE_DIRS', ['.git', 'assets/lib']);
define('AUDIT_RISKY_EXT', ['php', 'phtml', 'php3', 'php4', 'php5', 'php6', 'php7', 'php8', 'phar', 'pht', 'shtml', 'cgi']);

$isCli = (PHP_SAPI === 'cli');
$actionBaseline = false;
$wantJson = false;

if ($isCli) {
    foreach (array_slice($argv ?? [], 1) as $arg) {
        if ($arg === '--baseline') $actionBaseline = true;
        if ($arg === '--json') $wantJson = true;
    }
} else {
    $actionBaseline = (($_GET['action'] ?? 'audit') === 'baseline');
    session_start();
    @include_once __DIR__ . '/config/db.php';
    if (!audit_checkAdmin()) {
        http_response_code(403);
        header('Content-Type: text/html; charset=utf-8');
        exit('<!DOCTYPE html><html><head><meta charset="utf-8"><title>403</title></head><body style="font-family:sans-serif;text-align:center;padding:60px"><h1>403 Forbidden</h1><p>Anda harus login sebagai admin untuk mengakses audit keamanan.</p></body></html>');
    }
    $clientIp = $_SERVER['REMOTE_ADDR'] ?? '';
    if (!empty(AUDIT_ALLOW_IPS) && !in_array($clientIp, AUDIT_ALLOW_IPS, true)) {
        http_response_code(403);
        exit('403 Forbidden');
    }
}

/* ===================== FUNGSI INTI ===================== */

function audit_checkAdmin() {
    if (empty($_SESSION['user_id'])) return false;
    try {
        if (!function_exists('db')) return false;
        $pdo = db();
        $st = $pdo->prepare("SELECT u.id FROM users u
            JOIN user_roles ur ON u.id = ur.user_id
            JOIN roles r ON ur.role_id = r.id
            WHERE u.id = ? AND u.is_active = 1 AND r.name = 'admin' LIMIT 1");
        $st->execute([$_SESSION['user_id']]);
        return (bool)$st->fetch();
    } catch (Throwable $e) {
        return false;
    }
}

function audit_loadManifest() {
    if (!is_file(AUDIT_MANIFEST_FILE)) return null;
    $data = json_decode((string)@file_get_contents(AUDIT_MANIFEST_FILE), true);
    if (!is_array($data) || !isset($data['files']) || !is_array($data['files'])) return null;
    return $data;
}

function audit_saveManifest(array $paths) {
    $json = json_encode(['generated_at' => date('c'), 'files' => $paths], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    return @file_put_contents(AUDIT_MANIFEST_FILE, $json) !== false;
}

function audit_walk() {
    $root = __DIR__;
    $exclude = AUDIT_EXCLUDE_DIRS;
    $dirIt = new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS);
    $filter = new RecursiveCallbackFilterIterator($dirIt, function ($cur) use ($root, $exclude) {
        if ($cur->isDir()) {
            $rel = str_replace('\\', '/', substr($cur->getPathname(), strlen($root) + 1));
            foreach ($exclude as $d) {
                if ($rel === $d || strpos($rel, $d . '/') === 0) return false;
            }
        }
        return true;
    });
    $files = [];
    foreach (new RecursiveIteratorIterator($filter, RecursiveIteratorIterator::LEAVES_ONLY) as $f) {
        if (!$f->isFile()) continue;
        $abs = $f->getPathname();
        $rel = str_replace('\\', '/', substr($abs, strlen($root) + 1));
        if ($rel === 'audit_security.php' || $rel === 'audit_manifest.json') continue;
        $files[$rel] = $abs;
    }
    return $files;
}

function audit_read($abs) {
    $size = @filesize($abs);
    if ($size !== false && $size > AUDIT_MAX_READ) return false;
    $content = @file_get_contents($abs);
    if ($content === false) return false;
    if (strpos($content, "\0") !== false) return false;
    return $content;
}

function audit_line($content, $offset) {
    return substr_count(substr($content, 0, $offset), "\n") + 1;
}

function audit_add(array &$findings, $rel, $abs, $sev, $reason, $line = null, $mtime = null) {
    $findings[] = [
        'severity' => $sev,
        'reason'   => $reason,
        'path'     => $rel,
        'absolute' => $abs,
        'line'     => $line,
        'mtime'    => $mtime,
        'recent'   => $mtime && (time() - $mtime) < AUDIT_RECENT_DAYS * 86400,
    ];
}

function audit_filenameChecks($rel, $base, $ext, $risky, $inUploads) {
    $out = [];
    $lower = strtolower($base);

    if (isset($lower[0]) && $lower[0] === '.' && !in_array($lower, ['.htaccess', '.gitignore', '.gitattributes', '.env.example'], true)) {
        $sev = ($inUploads && !$risky) ? 'LOW' : 'HIGH';
        $out[] = ['sev' => $sev, 'reason' => 'Hidden file (dotfile) mencurigakan'];
    }

    if ($risky && substr_count($lower, '.') >= 2) {
        $out[] = ['sev' => 'HIGH', 'reason' => 'Eksekusi dengan ekstensi ganda (mis. file.php.jpg)'];
    }

    $name = preg_replace('/\.(?:phtml|phar|pht|php\d*)$/i', '', $lower);
    if (preg_match('/shell|backdoor|webshell|c99|r57|b374k|alfashell|phpspy|p0wny|k4pwn|hack|inject|rootkit|bypass/i', $name)) {
        $out[] = ['sev' => 'HIGH', 'reason' => 'Nama file mencurigakan (indikasi webshell)'];
    }

    if (preg_match('/^[0-9a-f]{16,32}$/', $name)) {
        $out[] = ['sev' => 'MEDIUM', 'reason' => 'Nama file berupa hex acak (potensi shell ter-upload)'];
    }

    if (!$risky && preg_match('/akun|password|passwd|kredensial|credential|secret/i', $lower)) {
        $out[] = ['sev' => 'LOW', 'reason' => 'File berpotensi berisi kredensial dalam teks polos'];
    }

    return $out;
}

function audit_signatureChecks($content) {
    $out = [];
    $patterns = [
        ["#eval\s*\(\s*(?:base64_decode|gzinflate|gzuncompress|str_rot13|gzdecode|pack)\s*\(#i", 'CRITICAL', 'eval() pada payload ter-enkode (obfuscated webshell)'],
        ["#eval\s*\(\s*\$_#i", 'CRITICAL', 'eval() dari variabel superglobal ($_GET/$_POST/$_REQUEST)'],
        ["#\beval\s*\(#i", 'HIGH', 'Penggunaan eval()'],
        ["#\bassert\s*\(\s*\$#i", 'HIGH', 'assert() dengan argumen variabel (rawan eksekusi kode)'],
        ["#\bcreate_function\s*\(#i", 'HIGH', 'create_function() deprecated (rawan RCE)'],
        ["#(?<![\w>\$.:])\b(?:system|exec|shell_exec|passthru|popen|proc_open|pcntl_exec)\s*\(#i", 'HIGH', 'Eksekusi perintah sistem (system/exec/shell_exec/dll)'],
        ["#call_user_func\s*\(\s*\$_#i", 'CRITICAL', 'call_user_func() dari input superglobal'],
        ["#\$(?:GET|POST|REQUEST|COOKIE|SERVER|FILES)\[[^]]+\]\s*\(#i", 'CRITICAL', 'Invoke fungsi dinamis dari superglobal (mis. $_GET[\'cmd\']())'],
        ["#preg_replace\s*\(\s*[\"'][^\"']*e[\"']\s*,#i", 'HIGH', 'preg_replace() dengan modifier /e (deprecated, rawan RCE)'],
        ["#base64_decode\s*\(\s*[\"'][^\"']{500,}#i", 'HIGH', 'String base64 sangat panjang dalam base64_decode()'],
        ["#gzinflate\s*\(#i", 'HIGH', 'gzinflate() digunakan (deflate payload, umum di webshell)'],
        ["#gzuncompress\s*\(#i", 'HIGH', 'gzuncompress() digunakan'],
        ["#str_rot13\s*\(#i", 'HIGH', 'str_rot13() digunakan (obfuscation)'],
    ];
    foreach ($patterns as $p) {
        if (preg_match($p[0], $content, $m, PREG_OFFSET_CAPTURE)) {
            $out[] = ['sev' => $p[1], 'reason' => $p[2], 'line' => audit_line($content, $m[0][1])];
        }
    }

    $shells = ['c99shell', 'r57shell', 'b374k', 'alfashell', 'phpspy', 'p0wny', 'weevly', 'k4pwn', 'china chopper', 'wso shell', 'jedi shell', 'omega shell', '@eval($_', '@assert($_', '@system($_', 'eval(gzuncompress(base64_decode', 'eval(gzinflate(base64_decode'];
    foreach ($shells as $s) {
        $pos = stripos($content, $s);
        if ($pos !== false) {
            $out[] = ['sev' => 'CRITICAL', 'reason' => 'Signatur webshell: "' . $s . '"', 'line' => audit_line($content, $pos)];
        }
    }

    return $out;
}

function audit_htaccessChecks($content) {
    $out = [];
    $bad = [
        'AddType application/x-httpd-php',
        'php_value auto_prepend_file',
        'php_value auto_append_file',
        'SetHandler',
        'Options +ExecCGI',
        'AddHandler cgi-script',
        'php_flag engine',
    ];
    foreach ($bad as $b) {
        $pos = stripos($content, $b);
        if ($pos !== false) {
            $out[] = ['sev' => 'HIGH', 'reason' => 'Direktif .htaccess berbahaya: ' . $b, 'line' => audit_line($content, $pos)];
        }
    }
    return $out;
}

function audit_normalize(array $f) {
    return [
        'severity'      => $f['severity'],
        'path'          => $f['path'],
        'absolute_path' => $f['absolute'],
        'line'          => $f['line'],
        'reason'        => $f['reason'],
        'mtime'         => $f['mtime'] ? date('Y-m-d H:i:s', $f['mtime']) : null,
        'recent'        => !empty($f['recent']),
    ];
}

/* ===================== BASELINE ===================== */

$baselineMsg = null;
if ($actionBaseline) {
    $paths = array_keys(audit_walk());
    sort($paths);
    $baselineMsg = audit_saveManifest($paths)
        ? 'Baseline diperbarui: ' . count($paths) . ' file dicatat sebagai sistem.'
        : 'GAGAL menulis manifest. Periksa izin tulis folder.';
}

/* ===================== AUDIT ===================== */

$files = audit_walk();
$manifest = audit_loadManifest();
$known = $manifest ? array_flip($manifest['files']) : [];
$findings = [];
$totalFiles = 0;
$execFiles = 0;

foreach ($files as $rel => $abs) {
    $totalFiles++;
    $base = basename($rel);
    $ext = strtolower((string)pathinfo($rel, PATHINFO_EXTENSION));
    $risky = in_array($ext, AUDIT_RISKY_EXT, true);
    $isHtaccess = (strtolower($base) === '.htaccess');
    $inUploads = (strpos($rel, 'uploads/') === 0);
    $mtime = @filemtime($abs);

    foreach (audit_filenameChecks($rel, $base, $ext, $risky, $inUploads) as $c) {
        audit_add($findings, $rel, $abs, $c['sev'], $c['reason'], null, $mtime);
    }

    if ($isHtaccess) {
        $content = audit_read($abs);
        if ($content !== false) {
            foreach (audit_htaccessChecks($content) as $c) {
                audit_add($findings, $rel, $abs, $c['sev'], $c['reason'], $c['line'], $mtime);
            }
        }
    }

    $isKnown = isset($known[$rel]);
    if (!$isKnown && !$isHtaccess) {
        if ($risky) {
            $sev = $inUploads ? 'CRITICAL' : 'HIGH';
            $reason = $inUploads
                ? 'File PHP baru di folder uploads (potensi webshell ter-upload)'
                : 'File PHP tidak dikenal — bukan bagian dari sistem';
            audit_add($findings, $rel, $abs, $sev, $reason, null, $mtime);
        } elseif ($inUploads) {
            audit_add($findings, $rel, $abs, 'INFO', 'File unggahan baru (non-eksekusi)', null, $mtime);
        } else {
            audit_add($findings, $rel, $abs, 'LOW', 'File tidak dikenal di luar manifest sistem', null, $mtime);
        }
    }

    if ($risky) {
        $execFiles++;
        $content = audit_read($abs);
        if ($content !== false) {
            foreach (audit_signatureChecks($content) as $c) {
                audit_add($findings, $rel, $abs, $c['sev'], $c['reason'], $c['line'], $mtime);
            }
        }
    }
}

if ($manifest) {
    foreach ($manifest['files'] as $mf) {
        if (!isset($files[$mf])) {
            audit_add($findings, $mf, __DIR__ . '/' . $mf, 'INFO', 'File dalam manifest tidak ditemukan (mungkin dihapus)', null, null);
        }
    }
}

$severityOrder = ['CRITICAL', 'HIGH', 'MEDIUM', 'LOW', 'INFO'];
$counts = array_fill_keys($severityOrder, 0);
foreach ($findings as $f) {
    if (isset($counts[$f['severity']])) $counts[$f['severity']]++;
}
$hasFindings = ($counts['CRITICAL'] + $counts['HIGH'] + $counts['MEDIUM']) > 0;

$report = [
    'generated_at'     => date('c'),
    'scope'            => __DIR__,
    'manifest'         => $manifest ? count($manifest['files']) : null,
    'total_files'      => $totalFiles,
    'executable_files' => $execFiles,
    'summary'          => $counts,
    'findings'         => array_map('audit_normalize', $findings),
];

/* ===================== EXPORT DAERAH TEMUAN (browser, read-only) ===================== */

if (!$isCli && ($_GET['action'] ?? 'audit') === 'export') {
    header('Content-Type: text/plain; charset=utf-8');
    header('Content-Disposition: attachment; filename="audit-findings-' . date('Ymd-His') . '.txt"');
    echo 'DAFTAR PATH TEMUAN AUDIT KEAMANAN', PHP_EOL;
    echo 'Waktu  : ', date('Y-m-d H:i:s'), PHP_EOL;
    echo 'Scope  : ', __DIR__, PHP_EOL;
    echo '============================================================', PHP_EOL, PHP_EOL;
    if ($findings) {
        foreach ($findings as $i => $f) {
            echo ($i + 1), '. [', $f['severity'], '] ', $f['path'], PHP_EOL;
            echo '   Absolut : ', $f['absolute'], PHP_EOL;
            echo '   Alasan  : ', $f['reason'], PHP_EOL;
            if ($f['line'] !== null) echo '   Baris   : ', $f['line'], PHP_EOL;
            echo PHP_EOL;
        }
    } else {
        echo 'Tidak ada temuan.', PHP_EOL;
    }
    exit;
}

/* ===================== OUTPUT ===================== */

if ($isCli) {
    if ($wantJson) {
        if ($baselineMsg) $report['baseline'] = $baselineMsg;
        echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), PHP_EOL;
        exit($hasFindings ? 1 : 0);
    }

    $sep = str_repeat('=', 60);
    echo $sep, PHP_EOL;
    echo ' AUDIT KEAMANAN FILE', PHP_EOL;
    echo ' Lokasi : ', __DIR__, PHP_EOL;
    echo ' Waktu  : ', date('Y-m-d H:i:s'), PHP_EOL;
    echo $sep, PHP_EOL;

    if ($baselineMsg) {
        echo ' Baseline: ', $baselineMsg, PHP_EOL, PHP_EOL;
    }
    if (!$manifest) {
        echo ' [PERHATIAN] Manifest belum ada. Jalankan: php audit_security.php --baseline', PHP_EOL, PHP_EOL;
    }

    echo ' Total file        : ', $totalFiles, PHP_EOL;
    echo ' File eksekusi     : ', $execFiles, PHP_EOL;
    echo ' Daftar manifest   : ', ($manifest ? count($manifest['files']) : '-'), PHP_EOL;
    echo $sep, PHP_EOL;
    echo ' Ringkasan:', PHP_EOL;
    foreach ($severityOrder as $s) {
        printf('   %-9s: %d%s', $s, $counts[$s], PHP_EOL);
    }
    echo $sep, PHP_EOL;

    if ($findings) {
        echo ' TEMUAN:', PHP_EOL;
        $colors = ['CRITICAL' => "\033[1;31m", 'HIGH' => "\033[0;31m", 'MEDIUM' => "\033[0;33m", 'LOW' => "\033[0;34m", 'INFO' => "\033[0;37m"];
        foreach ($findings as $f) {
            $badge = $colors[$f['severity']] ?? '';
            $reset = "\033[0m";
            echo '  ', $badge, '[' . $f['severity'] . ']', $reset, ' ', $f['path'];
            if ($f['line'] !== null) echo ' (baris ', $f['line'], ')';
            if ($f['recent']) echo ' [baru dimodifikasi]';
            echo PHP_EOL;
            echo '      Lokasi: ', $f['absolute'], PHP_EOL;
            echo '      Alasan: ', $f['reason'], PHP_EOL;
        }
    } else {
        echo ' Tidak ada temuan. Sistem bersih.', PHP_EOL;
    }

    echo $sep, PHP_EOL;
    echo $hasFindings ? ' HASIL: DITEMUKAN TEMUAN (periksa daftar di atas)' : ' HASIL: BERSIH', PHP_EOL;
    exit($hasFindings ? 1 : 0);
}

audit_html($report, $baselineMsg, $manifest, $findings);

function audit_html(array $report, $baselineMsg, $manifest, array $findings = []) {
    $c = $report['summary'];
    $order = ['CRITICAL', 'HIGH', 'MEDIUM', 'LOW', 'INFO'];
    $sevColors = [
        'CRITICAL' => '#b91c1c',
        'HIGH'     => '#dc2626',
        'MEDIUM'   => '#d97706',
        'LOW'      => '#2563eb',
        'INFO'     => '#6b7280',
    ];
    $h = function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };
    $clean = ($c['CRITICAL'] + $c['HIGH'] + $c['MEDIUM']) === 0;

    $badgeBtn = '';
    if ($manifest) {
        $badgeBtn = '<button type="submit" class="btn btn-sm btn-warning" onclick="return confirm(\'Perbarui baseline dengan kondisi file saat ini? Pastikan tidak ada file asing.\')">Perbarui Baseline</button>';
    } else {
        $badgeBtn = '<button type="submit" class="btn btn-sm btn-warning" onclick="return confirm(\'Buat baseline dari kondisi file saat ini?\')">Buat Baseline</button>';
    }

    echo '<!DOCTYPE html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">';
    echo '<title>Audit Keamanan File</title><style>';
    echo 'body{font-family:"Segoe UI",system-ui,Arial,sans-serif;background:#f5f6f8;margin:0;color:#1f2937}';
    echo '.wrap{max-width:980px;margin:0 auto;padding:24px 16px 60px}';
    echo '.head{background:linear-gradient(135deg,#1f2937,#111827);color:#fff;border-radius:14px;padding:22px 26px;display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap}';
    echo '.head h1{margin:0;font-size:20px}.head p{margin:4px 0 0;font-size:12px;color:#cbd5e1}';
    echo '.cards{display:flex;gap:10px;flex-wrap:wrap;margin:18px 0}';
    echo '.card{background:#fff;border-radius:10px;padding:14px 18px;box-shadow:0 1px 3px rgba(0,0,0,.08);min-width:110px}';
    echo '.card .n{font-size:24px;font-weight:800}.card .l{font-size:11px;color:#6b7280;text-transform:uppercase;letter-spacing:.5px}';
    echo '.banner{border-radius:12px;padding:16px 20px;margin:0 0 18px;font-weight:600;color:#fff}';
    echo '.banner.ok{background:#16a34a}.banner.bad{background:#dc2626}';
    echo 'table{width:100%;border-collapse:collapse;background:#fff;border-radius:10px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.08)}';
    echo 'th,td{padding:10px 12px;text-align:left;vertical-align:top;font-size:13px;border-bottom:1px solid #eef0f2}';
    echo 'th{background:#f9fafb;font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:#6b7280}';
    echo 'tr:last-child td{border-bottom:none}';
    echo '.sev{display:inline-block;color:#fff;font-size:10px;font-weight:700;padding:2px 8px;border-radius:99px;letter-spacing:.4px}';
    echo '.mono{font-family:Consolas,Menlo,monospace;font-size:12px}';
    echo '.meta{color:#6b7280;font-size:11px}';
    echo '.badge-recent{background:#fef3c7;color:#92400e;font-size:10px;padding:1px 7px;border-radius:99px;margin-left:6px}';
    echo '.note{background:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:12px 16px;font-size:13px;margin-bottom:16px}';
    echo '.empty{background:#fff;border-radius:10px;padding:30px;text-align:center;color:#6b7280;box-shadow:0 1px 3px rgba(0,0,0,.08)}';
    echo '.foot{margin-top:18px;font-size:12px;color:#6b7280}';
    echo '.btn{display:inline-block;border:0;border-radius:8px;padding:7px 12px;font-size:12px;font-weight:600;cursor:pointer;text-decoration:none;color:#fff}';
    echo '.btn-sm{padding:4px 10px;font-size:11px;border-radius:6px}';
    echo '.btn-warning{background:#d97706}.btn-warning:hover{background:#b45309}';
    echo '.btn-copy{background:#2563eb}.btn-copy:hover{background:#1d4ed8}.btn-copy.copied{background:#16a34a}';
    echo '.btn-download{background:#7c3aed}.btn-download:hover{background:#6d28d9}';
    echo '</style></head><body><div class="wrap">';

    echo '<div class="head"><div><h1>Audit Keamanan File</h1>';
    echo '<p>Scope: ' . $h($report['scope']) . ' &middot; Waktu: ' . $h($report['generated_at']) . '</p></div>';
    echo '<form method="get" style="margin:0">';
    echo '<input type="hidden" name="action" value="baseline">';
    echo $badgeBtn;
    echo '</form> <a class="btn btn-sm btn-download" href="?action=export">Download Daftar Path (.txt)</a></div>';

    if ($baselineMsg) {
        echo '<div class="banner ok" style="margin-top:14px">' . $h($baselineMsg) . '</div>';
    }
    if (!$manifest) {
        echo '<div class="note"><b>Manifest belum ada.</b> Klik "Buat Baseline" agar file sistem dicatat, sehingga file injeksi baru bisa terdeteksi.</div>';
    }

    echo '<div class="cards">';
    echo '<div class="card"><div class="n">' . $report['total_files'] . '</div><div class="l">Total File</div></div>';
    echo '<div class="card"><div class="n">' . $report['executable_files'] . '</div><div class="l">File Eksekusi</div></div>';
    echo '<div class="card"><div class="n">' . ($report['manifest'] === null ? '-' : $report['manifest']) . '</div><div class="l">Manifest</div></div>';
    foreach ($order as $s) {
        echo '<div class="card"><div class="n" style="color:' . $sevColors[$s] . '">' . $c[$s] . '</div><div class="l">' . $s . '</div></div>';
    }
    echo '</div>';

    if ($clean && !$findings) {
        echo '<div class="banner ok">Tidak ada temuan. Sistem bersih.</div>';
    } elseif ($clean && $findings) {
        echo '<div class="banner ok">Tidak ada temuan serius. Hanya catatan informasional di bawah.</div>';
    } else {
        echo '<div class="banner bad">Ditemukan ' . ($c['CRITICAL'] + $c['HIGH'] + $c['MEDIUM']) . ' temuan yang perlu ditindaklanjuti.</div>';
    }

    if ($findings) {
        echo '<table><thead><tr><th>Severity</th><th>Lokasi File</th><th>Alasan</th><th>Baris</th><th>Dimodifikasi</th><th>Aksi</th></tr></thead><tbody>';
        foreach ($report['findings'] as $f) {
            echo '<tr>';
            echo '<td><span class="sev" style="background:' . $sevColors[$f['severity']] . '">' . $h($f['severity']) . '</span></td>';
            echo '<td class="mono">' . $h($f['path']);
            if (!empty($f['recent'])) echo '<span class="badge-recent">baru</span>';
            echo '<br><span class="meta">' . $h($f['absolute_path']) . '</span></td>';
            echo '<td>' . $h($f['reason']) . '</td>';
            echo '<td>' . ($f['line'] !== null ? $h($f['line']) : '-') . '</td>';
            echo '<td class="meta">' . ($f['mtime'] ? $h($f['mtime']) : '-') . '</td>';
            echo '<td><button type="button" class="btn btn-sm btn-copy" data-path="' . $h($f['absolute_path']) . '" onclick="auditCopy(this)">Copy Path</button></td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    } else {
        echo '<div class="empty">Tidak ada file yang terdeteksi mencurigakan.</div>';
    }

    echo '<div class="foot">Audit membandingkan file terhadap manifest (file sistem) dan memindai pola backdoor/webshell. Direktori yang dikecualikan: ' . $h(implode(', ', AUDIT_EXCLUDE_DIRS)) . '.</div>';
    echo '</div></body><script>
function auditCopy(el){
  var t = el.getAttribute("data-path");
  if (navigator.clipboard && window.isSecureContext) {
    navigator.clipboard.writeText(t).then(function(){ auditCopied(el); });
  } else {
    var ta = document.createElement("textarea");
    ta.value = t; ta.style.position = "fixed"; ta.style.opacity = "0";
    document.body.appendChild(ta); ta.select();
    try { document.execCommand("copy"); } catch(e) {}
    document.body.removeChild(ta); auditCopied(el);
  }
}
function auditCopied(el){
  var o = el.textContent; el.textContent = "Copied!"; el.classList.add("copied");
  setTimeout(function(){ el.textContent = o; el.classList.remove("copied"); }, 1200);
}
</script></html>';
}