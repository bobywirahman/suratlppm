<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/helpers.php';
$landSiteName = getSetting('site_name', 'LPPM UFDK');
$landLogo = getSetting('logo', '');

$perPage = 9;
$page = max(1, (int)($_GET['hal'] ?? 1));
$total = (int)$pdo->query("SELECT COUNT(*) FROM informasi_lpm WHERE is_active = 1")->fetchColumn();
$totalPages = max(1, (int)ceil($total / $perPage));
if ($page > $totalPages) $page = $totalPages;
$offset = ($page - 1) * $perPage;
$stmt = $pdo->prepare("SELECT * FROM informasi_lpm WHERE is_active = 1 ORDER BY created_at DESC, id DESC LIMIT ? OFFSET ?");
$stmt->bindValue(1, $perPage, PDO::PARAM_INT);
$stmt->bindValue(2, $offset, PDO::PARAM_INT);
$stmt->execute();
$list = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informasi &amp; Berita - <?php echo htmlspecialchars($landSiteName); ?></title>
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
        .list-header { background: linear-gradient(135deg, #fff 0%, #fff5f0 50%, #fff 100%); padding: 56px 0 30px; }
        .hero-badge { display: inline-flex; align-items: center; gap: 8px; background: rgba(255,107,53,0.1); color: #FF6B35; padding: 6px 18px; border-radius: 50px; font-size: 0.8rem; font-weight: 600; }
        .section-title { font-weight: 800; font-size: 2rem; color: #1a1a2e; }
        .info-card {
            border: none; border-radius: 16px; overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05); transition: .3s; height: 100%;
            background: #fff; display: flex; flex-direction: column;
        }
        .info-card:hover { transform: translateY(-6px); box-shadow: 0 14px 44px rgba(0,0,0,0.12); }
        .info-card img { width: 100%; height: 180px; object-fit: cover; }
        .info-card .info-body { padding: 20px; display: flex; flex-direction: column; flex-grow: 1; }
        .info-card .info-date { font-size: 0.72rem; color: #999; }
        .info-card h5 { font-weight: 700; color: #1a1a2e; }
        .info-card p { font-size: 0.85rem; color: #777; line-height: 1.6; }
        .info-card .btn-info-read { background: #FF6B35; color: #fff; border-radius: 50px; font-size: 0.78rem; font-weight: 600; padding: 6px 18px; border: none; }
        .info-card .btn-info-read:hover { background: #e85d2a; }
        .info-card .btn-info-link { color: #FF6B35; font-size: 0.78rem; font-weight: 600; text-decoration: none; }
        .pagination .page-link { color: #FF6B35; }
        .pagination .page-item.active .page-link { background-color: #FF6B35; border-color: #FF6B35; color: #fff; }
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
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navmenulist">
            <i class="fas fa-bars" style="color:#1a1a2e;"></i>
        </button>
        <div class="collapse navbar-collapse" id="navmenulist">
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

<section class="list-header">
    <div class="container text-center">
        <span class="hero-badge mb-3"><i class="fas fa-bullhorn"></i> Informasi LPPM</span>
        <h1 class="section-title">Semua Informasi &amp; <span style="color:#FF6B35;">Berita</span></h1>
        <p class="text-muted">Kumpulan informasi terbaru seputar kegiatan <?php echo htmlspecialchars($landSiteName); ?></p>
    </div>
</section>

<section class="py-5">
    <div class="container py-3">
        <?php if ($list): ?>
        <div class="row g-4">
            <?php foreach ($list as $info): ?>
            <div class="col-md-6 col-lg-4">
                <div class="info-card">
                    <?php if (!empty($info['thumbnail'])): ?>
                    <img src="<?php echo htmlspecialchars(asset($info['thumbnail'])); ?>" alt="<?php echo htmlspecialchars($info['judul']); ?>">
                    <?php endif; ?>
                    <div class="info-body">
                        <div class="info-date mb-1"><i class="far fa-calendar-alt me-1"></i><?php echo tanggalIndonesia($info['created_at']); ?></div>
                        <h5><?php echo htmlspecialchars($info['judul']); ?></h5>
                        <p><?php echo htmlspecialchars(mb_strimwidth(strip_tags($info['konten']), 0, 160, '...')); ?></p>
                        <div class="d-flex align-items-center justify-content-between mt-auto pt-3">
                            <a href="<?php echo BASE_URL; ?>/informasi.php?id=<?php echo $info['id']; ?>" class="btn btn-info-read"><i class="fas fa-book-open me-1"></i> Baca Selengkapnya</a>
                            <?php if (!empty($info['lampiran'])): ?>
                            <a href="<?php echo htmlspecialchars(asset($info['lampiran'])); ?>" target="_blank" class="btn-info-link" title="Download Lampiran"><i class="fas fa-file-pdf me-1 text-danger"></i> Lampiran</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if ($total > 0): ?>
        <nav class="mt-5 d-flex justify-content-center">
            <ul class="pagination">
                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?hal=<?php echo $page - 1; ?>"><i class="fas fa-chevron-left"></i></a>
                </li>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                    <a class="page-link" href="?hal=<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
                <?php endfor; ?>
                <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?hal=<?php echo $page + 1; ?>"><i class="fas fa-chevron-right"></i></a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>

        <?php else: ?>
        <p class="text-center text-muted py-5">Belum ada informasi untuk ditampilkan.</p>
        <?php endif; ?>

        <div class="text-center mt-4">
            <a href="<?php echo BASE_URL; ?>/" class="btn btn-outline-secondary rounded-pill px-4"><i class="fas fa-arrow-left me-2"></i>Kembali ke Landing Page</a>
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