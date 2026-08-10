<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/helpers.php';
$landSiteName = getSetting('site_name', 'LPPM UFDK');
$landLogo = getSetting('logo', '');

$id = (int)($_GET['id'] ?? 0);
$info = null;
if ($id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM informasi_lpm WHERE id = ? AND is_active = 1");
    $stmt->execute([$id]);
    $info = $stmt->fetch();
}
if (!$info) {
    header("Location: " . BASE_URL . "/");
    exit;
}

$latestNews = [];
$stmt = $pdo->prepare("SELECT id, judul, thumbnail, created_at FROM informasi_lpm WHERE is_active = 1 AND id != ? ORDER BY created_at DESC, id DESC LIMIT 5");
$stmt->execute([$id]);
$latestNews = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($info['judul']); ?> - <?php echo htmlspecialchars($landSiteName); ?></title>
    <?php if (getSetting('favicon')): ?>
    <link rel="icon" href="<?php echo htmlspecialchars(asset(getSetting('favicon'))); ?>">
    <?php endif; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background: #fff; }
        .navbar { background: rgba(255,255,255,0.95); backdrop-filter: blur(10px); border-bottom: 1px solid rgba(0,0,0,0.05); }
        .navbar-brand { font-weight: 800; font-size: 1.4rem; color: #1a1a2e; }
        .navbar-brand span { color: #FF6B35; }
        .nav-link { font-weight: 500; color: #555; margin: 0 8px; font-size: 0.9rem; transition: .2s; }
        .nav-link:hover { color: #FF6B35; }
        .info-header { background: linear-gradient(135deg, #fff 0%, #fff5f0 50%, #fff 100%); padding: 60px 0 30px; }
        .info-title { font-weight: 800; color: #1a1a2e; }
        .info-date { color: #999; font-size: 0.85rem; }
        .info-body-text { line-height: 1.9; color: #444; font-size: 1.02rem; }
        .hero-badge { display: inline-flex; align-items: center; gap: 8px; background: rgba(255,107,53,0.1); color: #FF6B35; padding: 6px 18px; border-radius: 50px; font-size: 0.8rem; font-weight: 600; }
        .btn-download { background: #FF6B35; color: #fff; border-radius: 50px; font-weight: 600; padding: 10px 24px; border: none; }
        .btn-download:hover { background: #e85d2a; color: #fff; }
        .multitext { white-space: pre-line; }
        .img-cover { width: 100%; border-radius: 16px; box-shadow: 0 16px 44px rgba(0,0,0,0.12); }
        .sidebar-card { border: none; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); padding: 20px; background: #fff; position: sticky; top: 90px; }
        .sidebar-title { font-weight: 700; color: #1a1a2e; border-bottom: 2px solid #FF6B35; padding-bottom: 10px; }
        .sidebar-item { display: flex; gap: 12px; text-decoration: none; align-items: flex-start; padding: 8px; border-radius: 10px; transition: .2s; }
        .sidebar-item:hover { background: #fff5f0; }
        .sidebar-item img { width: 70px; height: 52px; object-fit: cover; border-radius: 8px; flex-shrink: 0; }
        .sidebar-item-title { font-weight: 600; font-size: 0.85rem; color: #1a1a2e; line-height: 1.35; }
        .sidebar-item:hover .sidebar-item-title { color: #FF6B35; }
        .sidebar-item-date { font-size: 0.7rem; color: #999; margin-top: 2px; }
        .sidebar-more { display: block; margin-top: 14px; text-align: center; font-size: 0.78rem; font-weight: 600; color: #FF6B35; text-decoration: none; border-top: 1px solid #f0f0f0; padding-top: 12px; }
        .sidebar-more:hover { color: #e85d2a; }
        .footer { background: #1a1a2e; padding: 40px 0 24px; }
        .footer h6 { color: #fff; font-weight: 700; font-size: 0.9rem; }
        .footer p, .footer a { color: rgba(255,255,255,0.6); font-size: 0.85rem; text-decoration: none; }
        .footer a:hover { color: #FF6B35; }
        .footer-divider { border-color: rgba(255,255,255,0.08); }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg sticky-top py-3">
    <div class="container">
        <a class="navbar-brand" href="<?php echo BASE_URL; ?>/"><?php if ($landLogo): ?><img src="<?php echo htmlspecialchars(asset($landLogo)); ?>" style="height: 40px; width: auto; object-fit: contain; margin-right: 10px;" alt="logo"><?php else: ?><i class="fas fa-flask me-2" style="color:#FF6B35;"></i><?php endif; ?><span><?php echo htmlspecialchars($landSiteName); ?></span></a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navmenudetail">
            <i class="fas fa-bars" style="color:#1a1a2e;"></i>
        </button>
        <div class="collapse navbar-collapse" id="navmenudetail">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/#beranda"><i class="fas fa-house me-1" style="color:#FF6B35;"></i>Beranda</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/#layanan"><i class="fas fa-cubes me-1" style="color:#FF6B35;"></i>Layanan</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/#tentang"><i class="fas fa-info-circle me-1" style="color:#FF6B35;"></i>Tentang</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/#kontak"><i class="fas fa-address-card me-1" style="color:#FF6B35;"></i>Kontak</a></li>
                <li class="nav-item"><a class="nav-link active" href="<?php echo BASE_URL; ?>/daftar_informasi.php"><i class="fas fa-bullhorn me-1" style="color:#FF6B35;"></i>Informasi</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/verifikasi.php"><i class="fas fa-shield-alt me-1" style="color:#FF6B35;"></i>Verifikasi Surat</a></li>
            </ul>
            <div class="d-flex gap-2">
                <a href="<?php echo BASE_URL; ?>/aplikasi.php" class="btn btn-outline-dark rounded-pill px-4 fw-semibold">Masuk</a>
                <a href="<?php echo BASE_URL; ?>/aplikasi.php?page=register" class="btn btn-primary rounded-pill px-4" style="background:#FF6B35;border:none;">Daftar</a>
            </div>
        </div>
    </div>
</nav>

<section class="info-header">
    <div class="container py-4">
        <span class="hero-badge mb-3"><i class="fas fa-bullhorn"></i> Informasi LPPM</span>
        <h1 class="info-title mt-3"><?php echo htmlspecialchars($info['judul']); ?></h1>
        <div class="info-date"><i class="far fa-calendar-alt me-1"></i> Dipublikasikan: <?php echo tanggalIndonesia($info['created_at']) . ' ' . date('H:i', strtotime($info['created_at'])); ?></div>
    </div>
</section>

<section class="py-4">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-8">
                <?php if (!empty($info['thumbnail'])): ?>
                <img src="<?php echo htmlspecialchars(asset($info['thumbnail'])); ?>" alt="<?php echo htmlspecialchars($info['judul']); ?>" class="img-cover mb-4">
                <?php endif; ?>
                <div class="info-body-text"><?php echo $info['konten']; ?></div>
                <?php if (!empty($info['lampiran'])): ?>
                <div class="mt-5 p-4 rounded" style="background:#fff5f0; border:1px solid rgba(255,107,53,0.2);">
                    <div class="d-flex align-items-center gap-3">
                        <i class="fas fa-file-pdf fa-2x text-danger"></i>
                        <div class="flex-grow-1">
                            <div class="fw-bold text-muted">Lampiran</div>
                            <small class="text-muted">Unduh dokumen lampiran untuk informasi ini.</small>
                        </div>
                        <a href="<?php echo htmlspecialchars(asset($info['lampiran'])); ?>" target="_blank" class="btn btn-download"><i class="fas fa-download me-2"></i>Download PDF</a>
                    </div>
                </div>
                <?php endif; ?>
                <div class="mt-5">
                    <a href="<?php echo BASE_URL; ?>/" class="btn btn-outline-secondary rounded-pill px-4"><i class="fas fa-arrow-left me-2"></i>Kembali ke Landing Page</a>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="sidebar-card">
                    <h6 class="sidebar-title mb-3"><i class="fas fa-newspaper me-2" style="color:#FF6B35;"></i>Berita Terbaru</h6>
                    <?php if ($latestNews): ?>
                    <div class="d-flex flex-column gap-1">
                        <?php foreach ($latestNews as $n): ?>
                        <a href="<?php echo BASE_URL; ?>/informasi.php?id=<?php echo $n['id']; ?>" class="sidebar-item">
                            <?php if (!empty($n['thumbnail'])): ?>
                            <img src="<?php echo htmlspecialchars(asset($n['thumbnail'])); ?>" alt="<?php echo htmlspecialchars($n['judul']); ?>">
                            <?php else: ?>
                            <div style="width:70px;height:52px;border-radius:8px;background:rgba(255,107,53,0.1);display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i class="fas fa-bullhorn" style="color:#FF6B35;"></i></div>
                            <?php endif; ?>
                            <div>
                                <div class="sidebar-item-title"><?php echo htmlspecialchars($n['judul']); ?></div>
                                <div class="sidebar-item-date"><i class="far fa-calendar-alt me-1"></i><?php echo tanggalIndonesia($n['created_at']); ?></div>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <p class="text-muted small mb-0">Belum ada berita lain.</p>
                    <?php endif; ?>
                    <a href="<?php echo BASE_URL; ?>/daftar_informasi.php" class="sidebar-more">Lihat Semua Berita <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>
    </div>
</section>

<footer class="footer">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-5">
                <h6 class="text-uppercase mb-3" style="letter-spacing:1px;"><?php if ($landLogo): ?><img src="<?php echo htmlspecialchars(asset($landLogo)); ?>" style="height: 30px; width: auto; object-fit: contain; margin-right: 8px;" alt="logo"><?php else: ?><i class="fas fa-flask me-2" style="color:#FF6B35;"></i><?php endif; ?><?php echo htmlspecialchars($landSiteName); ?></h6>
                <p><?php echo getSetting('site_description', 'Lembaga Penelitian & Pengabdian Masyarakat'); ?>. <?php echo getSetting('footer_desc', 'Mendorong inovasi riset dan pengabdian yang berdampak nyata bagi masyarakat.'); ?></p>
            </div>
            <div class="col-md-3 offset-md-1">
                <h6 class="text-uppercase mb-3" style="letter-spacing:1px;">Navigasi</h6>
                <div class="d-flex flex-column gap-2">
                    <a href="<?php echo BASE_URL; ?>/#beranda"><i class="fas fa-house me-2" style="color:#FF6B35;"></i>Beranda</a>
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
            <p class="mb-0">&copy; 2026 <?php echo htmlspecialchars($landSiteName); ?>. All rights reserved.</p>
            <div class="d-flex gap-3">
                <a href="<?php echo htmlspecialchars(getSetting('social_instagram', '#')); ?>"><i class="fab fa-instagram"></i></a>
                <a href="<?php echo htmlspecialchars(getSetting('social_youtube', '#')); ?>"><i class="fab fa-youtube"></i></a>
                <a href="<?php echo htmlspecialchars(getSetting('social_facebook', '#')); ?>"><i class="fab fa-facebook"></i></a>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>