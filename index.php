<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/helpers.php';
$landSiteName = getSetting('site_name', 'LPPM UFDK');
$landSiteDesc = getSetting('site_description', 'Lembaga Penelitian & Pengabdian Masyarakat');
$landAdminPhone = getSetting('admin_phone', '0852-1234-5678');
$landLogo = getSetting('logo', '');
$heroBadge = getSetting('hero_badge', 'Lembaga Penelitian & Pengabdian');
$heroTitle = getSetting('hero_title', 'Inovasi & Riset<br><span>Untuk Negeri</span>');
$heroSubtitle = getSetting('hero_subtitle', 'Berkomitmen mendorong penelitian, pengabdian masyarakat, dan publikasi ilmiah yang berdampak nyata.');
$heroImage = getSetting('hero_image', 'https://images.unsplash.com/photo-1532094349884-543bc11b234d?w=600&q=80');
$heroStatDefaults = [1=>['50+','Penelitian'],2=>['120+','Pengabdian'],3=>['200+','Publikasi']];
for ($i = 1; $i <= 3; $i++) {
    ${"heroStat{$i}Num"} = getSetting("hero_stat{$i}_num", $heroStatDefaults[$i][0]);
    ${"heroStat{$i}Label"} = getSetting("hero_stat{$i}_label", $heroStatDefaults[$i][1]);
}
$sectionLayananBadge = getSetting('section_layanan_badge', 'Layanan Kami');
$sectionLayananTitle = getSetting('section_layanan_title', 'Fokus <span style="color:#FF6B35;">Bidang</span>');
$sectionTentangBadge = getSetting('section_tentang_badge', 'Tentang Kami');
$sectionKontakBadge = getSetting('section_kontak_badge', 'Hubungi Kami');
$sectionKontakTitle = getSetting('section_kontak_title', 'Mari <span style="color:#FF6B35;">Berkolaborasi</span>');
$aboutTitle = getSetting('about_title', 'LPPM <span style="color:#FF6B35;">Universitas Fajar Deklarasi Karya</span>');
$aboutContent = getSetting('about_content', 'Lembaga Penelitian dan Pengabdian kepada Masyarakat (LPPM) merupakan unit pelaksana teknis di lingkungan Universitas Fajar Deklarasi Karya yang bertugas merencanakan, mengkoordinasikan, memantau, dan mengevaluasi kegiatan penelitian serta pengabdian kepada masyarakat yang dilakukan oleh dosen dan mahasiswa.');
$aboutImage = getSetting('about_image', 'https://images.unsplash.com/photo-1562774053-701939374585?w=600&q=80');
$aboutFeatures = json_decode(getSetting('about_features', '[]'), true) ?: [];
$services = json_decode(getSetting('services', '[]'), true) ?: [];
$contactAddress = getSetting('contact_address', 'Kampus Universitas Fajar Deklarasi Karya, Jl. Pendidikan No. 12, Padang');
$contactEmail = getSetting('contact_email', 'lppm@ufdk.ac.id');
$contactPhone = getSetting('contact_phone', '(0751) 1234567');
$footerDesc = getSetting('footer_desc', 'Mendorong inovasi riset dan pengabdian yang berdampak nyata bagi masyarakat.');
$socialIg = getSetting('social_instagram', '#');
$socialYt = getSetting('social_youtube', '#');
$socialFb = getSetting('social_facebook', '#');
for ($i = 1; $i <= 4; $i++) {
    ${"stat{$i}Num"} = getSetting("stat{$i}_num");
    ${"stat{$i}Label"} = getSetting("stat{$i}_label");
}
$informasiList = [];
try {
    $informasiList = $pdo->query("SELECT * FROM informasi_lpm WHERE is_active = 1 ORDER BY created_at DESC, id DESC LIMIT 6")->fetchAll();
} catch (Exception $e) {}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($landSiteName); ?> - <?php echo htmlspecialchars($landSiteDesc); ?></title>
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
        .hero {
            min-height: 92vh; display: flex; align-items: center;
            background: linear-gradient(135deg, #fff 0%, #fff5f0 50%, #fff 100%);
            position: relative; overflow: hidden;
        }
        .hero::before {
            content: ''; position: absolute; top: -50%; right: -20%;
            width: 700px; height: 700px; border-radius: 50%;
            background: radial-gradient(circle, rgba(255,107,53,0.06) 0%, transparent 70%);
        }
        .hero::after {
            content: ''; position: absolute; bottom: -30%; left: -10%;
            width: 500px; height: 500px; border-radius: 50%;
            background: radial-gradient(circle, rgba(255,107,53,0.04) 0%, transparent 70%);
        }
        .hero-badge {
            display: inline-flex; align-items: center; gap: 8px;
            background: rgba(255,107,53,0.1); color: #FF6B35;
            padding: 6px 18px; border-radius: 50px; font-size: 0.8rem; font-weight: 600;
        }
        .hero h1 { font-size: 3.5rem; font-weight: 900; line-height: 1.15; color: #1a1a2e; }
        .hero h1 span { color: #FF6B35; }
        .hero p { font-size: 1.1rem; color: #666; line-height: 1.7; max-width: 540px; }
        .hero .btn-primary {
            background: #FF6B35; border: none; padding: 14px 40px; border-radius: 50px;
            font-weight: 700; font-size: 1rem; transition: .3s; box-shadow: 0 8px 30px rgba(255,107,53,0.3);
        }
        .hero .btn-primary:hover { background: #e85d2a; transform: translateY(-2px); box-shadow: 0 12px 40px rgba(255,107,53,0.4); }
        .hero .btn-outline-secondary {
            border: 2px solid #ddd; color: #555; padding: 14px 32px; border-radius: 50px;
            font-weight: 600; transition: .3s;
        }
        .hero .btn-outline-secondary:hover { border-color: #FF6B35; color: #FF6B35; background: rgba(255,107,53,0.05); }
        .hero-img {
            width: 100%; border-radius: 24px;
            box-shadow: 0 30px 80px rgba(0,0,0,0.08);
            transform: perspective(1000px) rotateY(-4deg) scale(1);
            transition: all .5s cubic-bezier(.25,.46,.45,.94);
        }
        .hero-img:hover { transform: perspective(1000px) rotateY(0deg) scale(1.02); box-shadow: 0 40px 100px rgba(0,0,0,0.12); }
        .section-title { font-weight: 800; font-size: 2rem; color: #1a1a2e; }
        .section-subtitle { color: #888; font-size: 1.05rem; max-width: 600px; margin: 0 auto; }
        .service-card {
            border: none; border-radius: 16px; padding: 32px 24px;
            transition: .3s; background: #fff;
            box-shadow: 0 4px 20px rgba(0,0,0,0.04);
            height: 100%;
        }
        .service-card:hover { transform: translateY(-6px); box-shadow: 0 12px 40px rgba(0,0,0,0.08); }
        .service-icon {
            width: 56px; height: 56px; border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.4rem; margin-bottom: 16px;
        }
        .service-card h6 { font-weight: 700; font-size: 1.05rem; color: #1a1a2e; }
        .service-card p { font-size: 0.88rem; color: #777; line-height: 1.6; }
        .stats-section { background: #1a1a2e; padding: 80px 0; }
        .stats-section h3 { font-weight: 800; font-size: 2.5rem; color: #FF6B35; }
        .stats-section p { color: rgba(255,255,255,0.7); font-weight: 500; font-size: 0.95rem; }
        .about-section { background: #fafafa; }
        .about-card {
            border: none; border-radius: 20px; overflow: hidden;
            box-shadow: 0 10px 40px rgba(0,0,0,0.05);
        }
        .footer {
            background: #1a1a2e; padding: 40px 0 24px;
        }
        .footer h6 { color: #fff; font-weight: 700; font-size: 0.9rem; }
        .footer p, .footer a { color: rgba(255,255,255,0.6); font-size: 0.85rem; text-decoration: none; }
        .footer a:hover { color: #FF6B35; }
        .footer-divider { border-color: rgba(255,255,255,0.08); }
        .scroll-top {
            position: fixed; bottom: 30px; right: 30px; z-index: 999;
            width: 44px; height: 44px; border-radius: 50%;
            background: #FF6B35; color: #fff; border: none;
            display: none; align-items: center; justify-content: center;
            box-shadow: 0 4px 20px rgba(255,107,53,0.3); cursor: pointer;
            transition: .3s;
        }
        .scroll-top:hover { transform: translateY(-3px); }
        .wa-float {
            position: fixed; bottom: 96px; right: 24px; z-index: 998;
            width: 56px; height: 56px; border-radius: 50%;
            background: #25D366; color: #fff; display: flex;
            align-items: center; justify-content: center; text-decoration: none;
            font-size: 1.7rem; box-shadow: 0 6px 20px rgba(37,211,102,0.4);
            transition: .3s;
        }
        .wa-float:hover { transform: translateY(-3px) scale(1.05); color: #fff; box-shadow: 0 10px 30px rgba(37,211,102,0.5); }
        .wa-float .wa-tooltip {
            position: absolute; right: calc(100% + 12px); top: 50%; transform: translateY(-50%);
            background: #1a1a2e; color: #fff; font-size: 0.75rem; font-weight: 500;
            padding: 6px 12px; border-radius: 8px; white-space: nowrap; opacity: 0;
            pointer-events: none; transition: .3s;
        }
        .wa-float:hover .wa-tooltip { opacity: 1; }
        @media (max-width: 768px) {
            .hero h1 { font-size: 2.2rem; }
            .hero { min-height: auto; padding: 100px 0 60px; }
        }
        .info-card {
            border: none; border-radius: 16px; overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05); transition: .3s; height: 100%;
            background: #fff;
        }
        .info-card:hover { transform: translateY(-6px); box-shadow: 0 14px 44px rgba(0,0,0,0.12); }
        .info-card img { width: 100%; height: 180px; object-fit: cover; }
        .info-card .info-body { padding: 20px; }
        .info-card .info-date { font-size: 0.72rem; color: #999; }
        .info-card h5 { font-weight: 700; color: #1a1a2e; }
        .info-card p { font-size: 0.85rem; color: #777; line-height: 1.6; }
        .info-card .btn-info-read { background: #FF6B35; color: #fff; border-radius: 50px; font-size: 0.78rem; font-weight: 600; padding: 6px 18px; border: none; }
        .info-card .btn-info-read:hover { background: #e85d2a; }
        .info-card .btn-info-link { color: #FF6B35; font-size: 0.78rem; font-weight: 600; text-decoration: none; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg sticky-top py-3">
    <div class="container">
        <a class="navbar-brand" href="<?php echo BASE_URL; ?>/"><?php if ($landLogo): ?><img src="<?php echo htmlspecialchars(asset($landLogo)); ?>" style="height: 40px; width: auto; object-fit: contain; margin-right: 10px;" alt="logo"><?php else: ?><i class="fas fa-flask me-2" style="color:#FF6B35;"></i><?php endif; ?><span><?php echo htmlspecialchars($landSiteName); ?></span></a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navmenu">
            <i class="fas fa-bars" style="color:#1a1a2e;"></i>
        </button>
        <div class="collapse navbar-collapse" id="navmenu">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item"><a class="nav-link" href="#beranda"><i class="fas fa-house me-1" style="color:#FF6B35;"></i>Beranda</a></li>
                <li class="nav-item"><a class="nav-link" href="#layanan"><i class="fas fa-cubes me-1" style="color:#FF6B35;"></i>Layanan</a></li>
                <li class="nav-item"><a class="nav-link" href="#tentang"><i class="fas fa-info-circle me-1" style="color:#FF6B35;"></i>Tentang</a></li>
                <li class="nav-item"><a class="nav-link" href="#kontak"><i class="fas fa-address-card me-1" style="color:#FF6B35;"></i>Kontak</a></li>
                <li class="nav-item"><a class="nav-link" href="#informasi"><i class="fas fa-bullhorn me-1" style="color:#FF6B35;"></i>Informasi</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/verifikasi.php"><i class="fas fa-shield-alt me-1" style="color:#FF6B35;"></i>Verifikasi Surat</a></li>
            </ul>
            <div class="d-flex gap-2">
                <a href="<?php echo BASE_URL; ?>/aplikasi.php" class="btn btn-outline-dark rounded-pill px-4 fw-semibold">Masuk</a>
                <a href="<?php echo BASE_URL; ?>/aplikasi.php?page=register" class="btn btn-primary rounded-pill px-4" style="background:#FF6B35;border:none;">Daftar</a>
            </div>
        </div>
    </div>
</nav>

<section class="hero" id="beranda">
    <div class="container position-relative">
        <div class="row align-items-center g-5">
            <div class="col-lg-5">
                <div class="hero-badge mb-4"><i class="fas fa-certificate"></i> <?php echo htmlspecialchars($heroBadge); ?></div>
                <h1><?php echo $heroTitle; ?></h1>
                <p><?php echo htmlspecialchars($heroSubtitle); ?></p>
                <div class="d-flex flex-wrap gap-3 mt-4">
                    <a href="<?php echo BASE_URL; ?>/aplikasi.php" class="btn btn-primary"><i class="fas fa-file-alt me-2"></i> Pengajuan Surat</a>
                    <a href="<?php echo BASE_URL; ?>/verifikasi.php" class="btn btn-outline-secondary"><i class="fas fa-shield-alt me-2"></i> Verifikasi Surat</a>
                    <a href="#layanan" class="btn btn-outline-secondary">Jelajahi Layanan</a>
                </div>
                <div class="d-flex gap-4 mt-5 pt-2">
                    <div><h5 class="fw-bold mb-0" style="color:#1a1a2e;"><?php echo htmlspecialchars($heroStat1Num); ?></h5><small class="text-muted"><?php echo htmlspecialchars($heroStat1Label); ?></small></div>
                    <div><h5 class="fw-bold mb-0" style="color:#1a1a2e;"><?php echo htmlspecialchars($heroStat2Num); ?></h5><small class="text-muted"><?php echo htmlspecialchars($heroStat2Label); ?></small></div>
                    <div><h5 class="fw-bold mb-0" style="color:#1a1a2e;"><?php echo htmlspecialchars($heroStat3Num); ?></h5><small class="text-muted"><?php echo htmlspecialchars($heroStat3Label); ?></small></div>
                </div>
            </div>
            <div class="col-lg-7 text-center">
                <img src="<?php echo htmlspecialchars(asset($heroImage)); ?>" alt="LPPM Research" class="hero-img">
            </div>
        </div>
    </div>
</section>

<section class="py-5" id="layanan">
    <div class="container py-5">
        <div class="text-center mb-5">
            <span class="hero-badge mb-3"><i class="fas fa-cubes"></i> <?php echo htmlspecialchars($sectionLayananBadge); ?></span>
            <h2 class="section-title"><?php echo $sectionLayananTitle; ?></h2>
            <p class="section-subtitle"><?php echo htmlspecialchars($landSiteName); ?> melayani berbagai bidang penelitian dan pengabdian masyarakat</p>
        </div>
        <div class="row g-4">
            <?php foreach ($services as $svc): ?>
            <div class="col-md-4 col-lg-3">
                <div class="service-card">
                    <div class="service-icon" style="background:rgba(255,107,53,0.1);color:#FF6B35;"><i class="fas <?php echo htmlspecialchars($svc['icon']); ?>"></i></div>
                    <h6><?php echo htmlspecialchars($svc['title']); ?></h6>
                    <p><?php echo htmlspecialchars($svc['desc']); ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="stats-section">
    <div class="container">
        <div class="row text-center g-4">
            <div class="col-6 col-lg-3">
                <h3><?php echo htmlspecialchars($stat1Num); ?></h3>
                <p><?php echo htmlspecialchars($stat1Label); ?></p>
            </div>
            <div class="col-6 col-lg-3">
                <h3><?php echo htmlspecialchars($stat2Num); ?></h3>
                <p><?php echo htmlspecialchars($stat2Label); ?></p>
            </div>
            <div class="col-6 col-lg-3">
                <h3><?php echo htmlspecialchars($stat3Num); ?></h3>
                <p><?php echo htmlspecialchars($stat3Label); ?></p>
            </div>
            <div class="col-6 col-lg-3">
                <h3><?php echo htmlspecialchars($stat4Num); ?></h3>
                <p><?php echo htmlspecialchars($stat4Label); ?></p>
            </div>
        </div>
    </div>
</section>

<section class="py-5 about-section" id="tentang">
    <div class="container py-5">
        <div class="row align-items-center g-5">
            <div class="col-lg-5">
                <div class="about-card">
                    <img src="<?php echo htmlspecialchars(asset($aboutImage)); ?>" alt="Tentang LPPM" class="w-100">
                </div>
            </div>
            <div class="col-lg-7">
                <span class="hero-badge mb-3"><i class="fas fa-info-circle"></i> <?php echo htmlspecialchars($sectionTentangBadge); ?></span>
                <h2 class="section-title"><?php echo $aboutTitle; ?></h2>
                <p class="text-muted" style="line-height:1.8;"><?php echo $aboutContent; ?></p>
                <?php if ($aboutFeatures): ?>
                <div class="row g-3 mt-3">
                    <?php foreach ($aboutFeatures as $f): ?>
                    <div class="col-sm-6">
                        <div class="d-flex gap-3">
                            <i class="fas <?php echo htmlspecialchars($f['icon']); ?>" style="color:#FF6B35;font-size:1.2rem;margin-top:3px;"></i>
                            <div><strong><?php echo htmlspecialchars($f['title']); ?></strong><br><small class="text-muted"><?php echo htmlspecialchars($f['desc']); ?></small></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<section class="py-5 about-section" id="informasi">
    <div class="container py-5">
        <div class="text-center mb-5">
            <span class="hero-badge mb-3"><i class="fas fa-bullhorn"></i> Informasi LPPM</span>
            <h2 class="section-title">Berita &amp; <span style="color:#FF6B35;">Informasi</span></h2>
            <p class="section-subtitle">Kabar terbaru seputar kegiatan penelitian, pengabdian, dan publikasi <?php echo htmlspecialchars($landSiteName); ?></p>
        </div>
        <?php if ($informasiList): ?>
        <div class="row g-4">
            <?php foreach ($informasiList as $info): ?>
            <div class="col-md-6 col-lg-4">
                <div class="info-card">
                    <?php if (!empty($info['thumbnail'])): ?>
                    <img src="<?php echo htmlspecialchars(asset($info['thumbnail'])); ?>" alt="<?php echo htmlspecialchars($info['judul']); ?>">
                    <?php endif; ?>
                    <div class="info-body">
                        <div class="info-date mb-1"><i class="far fa-calendar-alt me-1"></i><?php echo tanggalIndonesia($info['created_at']); ?></div>
                        <h5><?php echo htmlspecialchars($info['judul']); ?></h5>
                        <p><?php echo htmlspecialchars(mb_strimwidth(strip_tags($info['konten']), 0, 160, '...')); ?></p>
                        <div class="d-flex align-items-center justify-content-between mt-3">
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
        <div class="text-center mt-5">
            <a href="<?php echo BASE_URL; ?>/daftar_informasi.php" class="btn btn-primary rounded-pill px-4" style="background:#FF6B35;border:none;color:#fff;padding:8px 22px;font-size:0.85rem;"><i class="fas fa-newspaper me-2"></i> Informasi Lainnya</a>
        </div>
        <?php else: ?>
        <p class="text-center text-muted">Belum ada informasi untuk ditampilkan.</p>
        <?php endif; ?>
    </div>
</section>

<section class="py-5" id="kontak">
    <div class="container py-5">
        <div class="text-center mb-5">
            <span class="hero-badge mb-3"><i class="fas fa-address-card"></i> <?php echo htmlspecialchars($sectionKontakBadge); ?></span>
            <h2 class="section-title"><?php echo $sectionKontakTitle; ?></h2>
            <p class="section-subtitle">Hubungi <?php echo htmlspecialchars($landSiteName); ?> untuk informasi lebih lanjut dan kemitraan riset</p>
        </div>
        <div class="row g-4 justify-content-center">
            <div class="col-md-4">
                <div class="service-card text-center">
                    <div class="service-icon mx-auto" style="background:rgba(255,107,53,0.1);color:#FF6B35;"><i class="fas fa-map-marker-alt"></i></div>
                    <h6>Alamat</h6>
                    <p><?php echo nl2br(htmlspecialchars($contactAddress)); ?></p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="service-card text-center">
                    <div class="service-icon mx-auto" style="background:rgba(255,107,53,0.1);color:#FF6B35;"><i class="fas fa-envelope"></i></div>
                    <h6>Email</h6>
                    <p><?php echo htmlspecialchars($contactEmail); ?></p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="service-card text-center">
                    <div class="service-icon mx-auto" style="background:rgba(255,107,53,0.1);color:#FF6B35;"><i class="fas fa-phone"></i></div>
                    <h6>Telepon</h6>
                    <p><?php echo htmlspecialchars($contactPhone); ?></p>
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
                <p><?php echo htmlspecialchars($landSiteDesc); ?>. <?php echo htmlspecialchars($footerDesc); ?></p>
            </div>
            <div class="col-md-3 offset-md-1">
                <h6 class="text-uppercase mb-3" style="letter-spacing:1px;">Navigasi</h6>
                <div class="d-flex flex-column gap-2">
                    <a href="#beranda"><i class="fas fa-house me-2" style="color:#FF6B35;"></i>Beranda</a>
                    <a href="#layanan"><i class="fas fa-cubes me-2" style="color:#FF6B35;"></i>Layanan</a>
                    <a href="#tentang"><i class="fas fa-info-circle me-2" style="color:#FF6B35;"></i>Tentang</a>
                    <a href="#informasi"><i class="fas fa-bullhorn me-2" style="color:#FF6B35;"></i>Informasi</a>
                    <a href="#kontak"><i class="fas fa-address-card me-2" style="color:#FF6B35;"></i>Kontak</a>
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
                <a href="<?php echo htmlspecialchars($socialIg); ?>"><i class="fab fa-instagram"></i></a>
                <a href="<?php echo htmlspecialchars($socialYt); ?>"><i class="fab fa-youtube"></i></a>
                <a href="<?php echo htmlspecialchars($socialFb); ?>"><i class="fab fa-facebook"></i></a>
            </div>
        </div>
    </div>
</footer>

<button class="scroll-top" id="scrollTop" onclick="window.scrollTo({top:0,behavior:'smooth'})"><i class="fas fa-arrow-up"></i></button>

<?php $waNum = waNumber($contactPhone); ?>
<?php if ($waNum): ?>
<a class="wa-float" href="https://wa.me/<?php echo htmlspecialchars($waNum); ?>" target="_blank" rel="noopener" title="Chat via WhatsApp">
    <span class="wa-tooltip">Chat dengan kami</span>
    <i class="fab fa-whatsapp"></i>
</a>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
window.addEventListener('scroll', function() {
    document.getElementById('scrollTop').style.display = window.scrollY > 400 ? 'flex' : 'none';
});
document.querySelectorAll('a[href^="#"]').forEach(function(a) {
    a.addEventListener('click', function(e) {
        e.preventDefault();
        var target = document.querySelector(this.getAttribute('href'));
        if (target) target.scrollIntoView({behavior:'smooth', block:'start'});
    });
});
</script>
</body>
</html>