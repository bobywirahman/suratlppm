<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/helpers.php';

$siteName = getSetting('site_name', 'LPPM UFDK');
$siteDesc = getSetting('site_description', 'Lembaga Penelitian & Pengabdian Masyarakat');
$landLogo = getSetting('logo', '');
$footerDesc = getSetting('footer_desc', 'Mendorong inovasi riset dan pengabdian yang berdampak nyata bagi masyarakat.');
$socialIg = getSetting('social_instagram', '#');
$socialYt = getSetting('social_youtube', '#');
$socialFb = getSetting('social_facebook', '#');

$pdo = db();

$result = null; // 'valid' | 'invalid'
$error = '';
$data = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nomor = trim($_POST['nomor'] ?? '');
    if ($nomor === '') {
        $error = 'Silakan masukkan nomor surat terlebih dahulu.';
    } else {
        $stmt = $pdo->prepare("SELECT d.id, d.document_number, d.title, d.type, d.created_at,
                        u.full_name AS applicant_name, u.nim,
                        de.name AS department_name, f.name AS faculty_name,
                        dt.name AS type_name
                        FROM documents d
                        JOIN users u ON d.applicant_id = u.id
                        JOIN departments de ON d.department_id = de.id
                        LEFT JOIN faculties f ON de.faculty_id = f.id
                        LEFT JOIN document_types dt ON d.type = dt.code
                        WHERE d.document_number = ? AND d.status IN ('approved','completed')
                        ORDER BY d.id DESC LIMIT 1");
        $stmt->execute([$nomor]);
        $data = $stmt->fetch();

        if ($data) {
            // Metadata (syarat) sesuai jenis surat + nilai isian dari dokumen
            $reqStmt = $pdo->prepare("SELECT * FROM type_requirements WHERE type_id = (SELECT id FROM document_types WHERE code = ?) ORDER BY sort_order ASC, id ASC");
            $reqStmt->execute([$data['type']]);
            $requirements = $reqStmt->fetchAll();

            $attStmt = $pdo->prepare("SELECT * FROM document_attachments WHERE document_id = ?");
            $attStmt->execute([$data['id']]);
            $attByReq = [];
            foreach ($attStmt->fetchAll() as $a) {
                $attByReq[$a['requirement_id']] = $a;
            }

            $data['requirements'] = $requirements;
            $data['attByReq'] = $attByReq;
            $result = 'valid';
        } else {
            $result = 'invalid';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Surat - <?php echo htmlspecialchars($siteName); ?></title>
    <?php if (getSetting('favicon')): ?>
    <link rel="icon" href="<?php echo htmlspecialchars(asset(getSetting('favicon'))); ?>">
    <?php endif; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background: linear-gradient(135deg, #fff 0%, #fff5f0 50%, #fff 100%); min-height: 100vh; }
        .navbar { background: rgba(255,255,255,0.95); backdrop-filter: blur(10px); border-bottom: 1px solid rgba(0,0,0,0.05); }
        .navbar-brand { font-weight: 800; font-size: 1.15rem; color: #1a1a2e; }
        .navbar-brand span { color: #FF6B35; }
        .nav-link { font-weight: 500; color: #555; margin: 0 8px; font-size: 0.85rem; transition: .2s; }
        .nav-link:hover, .nav-link.active { color: #FF6B35; }
        .verify-card { border: none; border-radius: 16px; box-shadow: 0 12px 40px rgba(0,0,0,0.08); font-size: 0.9rem; }
        .verify-icon {
            width: 48px; height: 48px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            background: rgba(255,107,53,0.1); color: #FF6B35; font-size: 1.25rem; margin: 0 auto;
        }
        .verify-card .btn-primary {
            background: #FF6B35; border: none; border-radius: 50px; padding: 6px 20px;
            font-weight: 600; font-size: 0.8rem; transition: .3s; box-shadow: 0 4px 16px rgba(255,107,53,0.25);
        }
        .verify-card .btn-primary:hover { background: #e85d2a; transform: translateY(-2px); }
        .verify-card .form-control { font-size: 0.9rem; }
        .result-valid { background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 12px; }
        .result-invalid { background: #fef2f2; border: 1px solid #fecaca; border-radius: 12px; }
        .detail-table th { width: 40%; font-weight: 600; color: #1a1a2e; background: #f8f9fa; font-size: 0.85rem; }
        .detail-table td { color: #444; font-size: 0.85rem; }
        .footer { background: #1a1a2e; padding: 40px 0 24px; }
        .footer h6 { color: #fff; font-weight: 700; font-size: 0.9rem; }
        .footer p, .footer a { color: rgba(255,255,255,0.6); font-size: 0.85rem; text-decoration: none; }
        .footer a:hover { color: #FF6B35; }
        .footer-divider { border-color: rgba(255,255,255,0.08); }
        .print-header { display: none; }
        .verify-loading {
            display: none; position: absolute; inset: 0; z-index: 10;
            background: rgba(255,255,255,0.92); backdrop-filter: blur(2px);
            border-radius: 16px; align-items: center; justify-content: center;
        }
        .verify-loading .spinner-box { text-align: center; }
        .verify-loading .spinner-box .spinner-border { width: 3rem; height: 3rem; color: #FF6B35; }
        .verify-loading .spinner-box p { margin-top: 12px; font-size: 0.85rem; color: #666; }
        @media print {
            .navbar, .no-print, .verify-form, .footer { display: none !important; }
            body { background: #fff; }
            .container { max-width: 100%; }
            .verify-card { box-shadow: none; border: 1px solid #ddd; border-radius: 8px; }
            .print-header { display: block; }
            .print-header h5 { font-weight: 800; color: #1a1a2e; margin: 0; }
            .print-header small { color: #666; }
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg sticky-top py-2">
    <div class="container">
        <a class="navbar-brand" href="<?php echo BASE_URL; ?>/"><?php if ($landLogo): ?><img src="<?php echo htmlspecialchars(asset($landLogo)); ?>" style="height: 40px; width: auto; object-fit: contain; margin-right: 10px;" alt="logo"><?php else: ?><i class="fas fa-flask me-2" style="color:#FF6B35;"></i><?php endif; ?><span><?php echo htmlspecialchars($siteName); ?></span></a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navmenu">
            <i class="fas fa-bars" style="color:#1a1a2e;"></i>
        </button>
        <div class="collapse navbar-collapse" id="navmenu">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/"><i class="fas fa-house me-1" style="color:#FF6B35;"></i>Beranda</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/#layanan"><i class="fas fa-cubes me-1" style="color:#FF6B35;"></i>Layanan</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/#tentang"><i class="fas fa-info-circle me-1" style="color:#FF6B35;"></i>Tentang</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/#kontak"><i class="fas fa-address-card me-1" style="color:#FF6B35;"></i>Kontak</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/daftar_informasi.php"><i class="fas fa-bullhorn me-1" style="color:#FF6B35;"></i>Informasi</a></li>
                <li class="nav-item"><a class="nav-link active" href="<?php echo BASE_URL; ?>/verifikasi.php"><i class="fas fa-shield-alt me-1" style="color:#FF6B35;"></i>Verifikasi Surat</a></li>
            </ul>
            <div class="d-flex gap-2">
                <a href="<?php echo BASE_URL; ?>/aplikasi.php" class="btn btn-outline-dark rounded-pill px-4 fw-semibold">Masuk</a>
                <a href="<?php echo BASE_URL; ?>/aplikasi.php?page=register" class="btn btn-primary rounded-pill px-4" style="background:#FF6B35;border:none;">Daftar</a>
            </div>
        </div>
    </div>
</nav>

<div class="container py-4" style="min-height: 75vh;">
    <div class="row justify-content-center">
        <div class="col-lg-7 col-md-9">
            <div class="card verify-card p-4 position-relative">
                <div class="verify-loading" id="verifyLoading">
                    <div class="spinner-box">
                        <div class="spinner-border" role="status"></div>
                        <p>Memverifikasi surat, harap tunggu...</p>
                    </div>
                </div>
                <div class="text-center mb-3">
                    <div class="verify-icon mb-2">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 2L4 5v6c0 5.25 3.4 9.74 8 11 4.6-1.26 8-5.75 8-11V5l-8-3z" fill="#FF6B35"/>
                            <path d="M8.5 12l2.4 2.4 4.6-4.8" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                        </svg>
                    </div>
                    <h4 class="fw-bold mb-1" style="color:#1a1a2e;">Verifikasi Surat</h4>
                    <p class="text-muted mb-0 small">Periksa keaslian surat yang diterbitkan oleh <?php echo htmlspecialchars($siteName); ?></p>
                </div>

                <form method="POST" class="row g-2 verify-form" id="verifyForm">
                    <div class="col-md-9">
                        <input type="text" name="nomor" class="form-control" placeholder="Masukkan Nomor Surat" value="<?php echo htmlspecialchars(trim($_POST['nomor'] ?? '')); ?>">
                    </div>
                    <div class="col-md-3 d-flex align-items-center">
                        <button type="submit" class="btn btn-primary ms-auto"><i class="fas fa-search me-1"></i>Verifikasi</button>
                    </div>
                </form>

                <?php if ($error !== ''): ?>
                    <div class="alert alert-warning mt-3 mb-0 rounded-3 py-2 small">
                        <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <?php if ($result === 'valid' && $data): ?>
                    <div class="result-valid p-3 mt-3 text-center">
                        <i class="fas fa-check-circle text-success" style="font-size: 2rem;"></i>
                        <h5 class="fw-bold text-success mb-1 mt-1">Surat ini ASLI dan VALID</h5>
                        <p class="mb-0 text-muted small">Surat dengan nomor berikut diterbitkan secara resmi dan sah oleh <?php echo htmlspecialchars($siteName); ?>.</p>
                    </div>

                    <div class="print-header text-center border-bottom pb-2 mb-3">
                        <h5><?php echo htmlspecialchars($siteName); ?></h5>
                        <small>Hasil Verifikasi Surat</small>
                    </div>

                    <div class="table-responsive mt-2">
                        <table class="table table-bordered detail-table align-middle mb-0 rounded-3">
                            <tbody>
                                <tr>
                                    <th>Nomor Surat</th>
                                    <td><?php echo htmlspecialchars($data['document_number']); ?></td>
                                </tr>
                                <tr>
                                    <th>Tanggal Surat</th>
                                    <td><?php echo htmlspecialchars(!empty($data['created_at']) ? date('d-m-Y', strtotime($data['created_at'])) : '-'); ?></td>
                                </tr>
                                <tr>
                                    <th>Jenis Surat</th>
                                    <td><?php echo htmlspecialchars($data['type_name'] ?? $data['type'] ?? '-'); ?></td>
                                </tr>
                                <tr>
                                    <th>Nama Pengaju</th>
                                    <td><?php echo htmlspecialchars($data['applicant_name']); ?></td>
                                </tr>
                                <tr>
                                    <th>Program Studi</th>
                                    <td><?php echo htmlspecialchars($data['department_name'] ?? '-'); ?></td>
                                </tr>
                                <tr>
                                    <th>NIM</th>
                                    <td><?php echo htmlspecialchars($data['nim'] ?? '-'); ?></td>
                                </tr>
                                <?php foreach ($data['requirements'] as $r):
                                    $att = $data['attByReq'][$r['id']] ?? null;
                                    $it = $r['input_type'] ?? 'file';
                                    $hasVal = $att !== null && !empty($att['file_name'] ?: $att['text_value']);
                                ?>
                                <tr>
                                    <th><?php echo htmlspecialchars($r['description']); ?></th>
                                    <td>
                                        <?php if ($it === 'list'): ?>
                                            <?php
                                                $items = [];
                                                $decoded = $att ? json_decode($att['text_value'], true) : null;
                                                if (is_array($decoded)) $items = $decoded;
                                            ?>
                                            <?php if (!empty($items)): ?>
                                                <ol class="mb-0 ps-3">
                                                    <?php foreach ($items as $li):
                                                        $parts = is_array($li) ? array_values(array_filter(array_map(function ($v) { return trim((string)$v); }, $li), function ($v) { return $v !== ''; })) : [trim((string)$li)];
                                                    ?>
                                                    <li><?php echo htmlspecialchars(implode(' — ', $parts)); ?></li>
                                                    <?php endforeach; ?>
                                                </ol>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        <?php elseif ($it === 'text'): ?>
                                            <?php if ($att && trim($att['text_value'] ?? '') !== ''): ?>
                                                <?php echo nl2br(htmlspecialchars($att['text_value'])); ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <?php
                                                $fp = $att['file_path'] ?? '';
                                                $fname = $att['file_name'] ?? '';
                                                $furl = upload_url($fp);
                                            ?>
                                            <?php if ($fname): ?>
                                                <?php if ($fp && $furl !== '#'): ?>
                                                    <a href="<?php echo htmlspecialchars($furl); ?>" target="_blank" rel="noopener"><i class="fas fa-paperclip me-1"></i><?php echo htmlspecialchars($fname); ?></a>
                                                <?php else: ?>
                                                    <i class="fas fa-paperclip me-1"></i><?php echo htmlspecialchars($fname); ?>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="text-center mt-3 no-print">
                        <button type="button" class="btn btn-primary rounded-pill px-4" onclick="window.print()">
                            <i class="fas fa-print me-1"></i>Cetak PDF
                        </button>
                    </div>
                <?php elseif ($result === 'invalid'): ?>
                    <div class="result-invalid p-3 mt-3 text-center">
                        <i class="fas fa-times-circle text-danger" style="font-size: 2rem;"></i>
                        <h5 class="fw-bold text-danger mb-1 mt-1">Nomor Surat Tidak Ditemukan</h5>
                        <p class="mb-0 text-muted small">Surat dengan nomor tersebut tidak terdaftar atau tidak diterbitkan oleh sistem <?php echo htmlspecialchars($siteName); ?>. Periksa kembali nomor surat Anda.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<footer class="footer">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-5">
                <h6 class="text-uppercase mb-3" style="letter-spacing:1px;"><?php if ($landLogo): ?><img src="<?php echo htmlspecialchars(asset($landLogo)); ?>" style="height: 30px; width: auto; object-fit: contain; margin-right: 8px;" alt="logo"><?php else: ?><i class="fas fa-flask me-2" style="color:#FF6B35;"></i><?php endif; ?><?php echo htmlspecialchars($siteName); ?></h6>
                <p><?php echo htmlspecialchars($siteDesc); ?>. <?php echo htmlspecialchars($footerDesc); ?></p>
            </div>
            <div class="col-md-3 offset-md-1">
                <h6 class="text-uppercase mb-3" style="letter-spacing:1px;">Navigasi</h6>
                <div class="d-flex flex-column gap-2">
                    <a href="<?php echo BASE_URL; ?>/"><i class="fas fa-house me-2" style="color:#FF6B35;"></i>Beranda</a>
                    <a href="<?php echo BASE_URL; ?>/#layanan"><i class="fas fa-cubes me-2" style="color:#FF6B35;"></i>Layanan</a>
                    <a href="<?php echo BASE_URL; ?>/#tentang"><i class="fas fa-info-circle me-2" style="color:#FF6B35;"></i>Tentang</a>
                    <a href="<?php echo BASE_URL; ?>/#informasi"><i class="fas fa-bullhorn me-2" style="color:#FF6B35;"></i>Informasi</a>
                    <a href="<?php echo BASE_URL; ?>/#kontak"><i class="fas fa-address-card me-2" style="color:#FF6B35;"></i>Kontak</a>
                </div>
            </div>
            <div class="col-md-3">
                <h6 class="text-uppercase mb-3" style="letter-spacing:1px;">Akses</h6>
                <div class="d-flex flex-column gap-2">
                    <a href="<?php echo BASE_URL; ?>/aplikasi.php"><i class="fas fa-sign-in-alt me-2" style="color:#FF6B35;"></i>Masuk</a>
                    <a href="<?php echo BASE_URL; ?>/aplikasi.php?page=register"><i class="fas fa-user-plus me-2" style="color:#FF6B35;"></i>Daftar Akun</a>
                    <a href="<?php echo BASE_URL; ?>/aplikasi.php"><i class="fas fa-file-alt me-2" style="color:#FF6B35;"></i>Pengajuan Surat</a>
                    <a href="<?php echo BASE_URL; ?>/verifikasi.php"><i class="fas fa-shield-alt me-2" style="color:#FF6B35;"></i>Verifikasi Surat</a>
                </div>
            </div>
        </div>
        <hr class="footer-divider my-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center">
            <p class="mb-0">&copy; 2026 <?php echo htmlspecialchars($siteName); ?>. All rights reserved.</p>
            <div class="d-flex gap-3">
                <a href="<?php echo htmlspecialchars($socialIg); ?>"><i class="fab fa-instagram"></i></a>
                <a href="<?php echo htmlspecialchars($socialYt); ?>"><i class="fab fa-youtube"></i></a>
                <a href="<?php echo htmlspecialchars($socialFb); ?>"><i class="fab fa-facebook"></i></a>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('verifyForm').addEventListener('submit', function(e) {
        e.preventDefault();
        var loading = document.getElementById('verifyLoading');
        loading.style.display = 'flex';
        setTimeout(function() {
            e.target.submit();
        }, 2000);
    });
</script>
</body>
</html>
