<?php
$siteName = getSetting('site_name', APP_NAME);
$siteDesc = getSetting('site_description', '');
$favicon = asset(getSetting('favicon', ''));
$logo = asset(getSetting('logo', ''));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?><?php echo htmlspecialchars($siteName); ?></title>
    <?php if ($favicon): ?>
    <link rel="icon" href="<?php echo htmlspecialchars($favicon); ?>">
    <?php endif; ?>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background-color: #f5f7fa; font-size: 0.82rem; }
        
        .navbar-brand { font-weight: 800; color: #1a1a2e !important; font-size: 1.15rem; }
        .navbar-brand span { color: #FF6B35; }
        .sidebar { min-height: 100vh; background: #1a1a2e; color: white; }
        .sidebar a { padding: 10px 18px; text-decoration: none; color: rgba(255,255,255,0.7); transition: all 0.3s; border-left: 3px solid transparent; font-weight: 400; font-size: 0.74rem; }
        .sidebar a:hover, .sidebar a.active { background: rgba(255,107,53,0.12); color: #FF6B35; border-left-color: #FF6B35; }
        .sidebar .nav-header { padding: 16px 18px 8px; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 1.5px; opacity: 0.6; font-weight: 600; }
        
        .card { border: none; border-radius: 10px; box-shadow: 0 2px 12px rgba(0,0,0,0.05); margin-bottom: 20px; }
        .card-header { background: linear-gradient(135deg, #FF6B35 0%, #e85d2a 100%); color: white; border-radius: 10px 10px 0 0 !important; padding: 14px 20px; border: none; }
        .card-header h5, .card-header .h5 { font-weight: 700; font-size: 0.95rem; }
        .card-body { padding: 20px; }
        
        .stat-card { text-align: center; padding: 18px; background: #fff; border-radius: 10px; }
        .stat-icon { width: 44px; height: 44px; background: rgba(255,107,53,0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px; color: #FF6B35; font-size: 1.1rem; }
        .stat-card h3 { font-size: 1.4rem; }
        .stat-card p { font-size: 0.78rem; }
        
        .btn { font-size: 0.82rem; }
        .btn-sm { font-size: 0.75rem; }
        .btn-lg { font-size: 0.9rem; padding: 10px 24px; }
        .btn-primary { background: #FF6B35; border: none; padding: 8px 22px; border-radius: 50px; font-weight: 600; transition: .3s; }
        .btn-primary:hover { background: #e85d2a; transform: translateY(-2px); box-shadow: 0 5px 20px rgba(255,107,53,0.35); }
        .btn-outline-primary { border-color: #FF6B35; color: #FF6B35; }
        .btn-outline-primary:hover { background: #FF6B35; border-color: #FF6B35; color: #fff; }
        
        .form-control, .form-select { border-radius: 8px; border: 1.5px solid #e0e0e0; padding: 8px 14px; font-size: 0.82rem; }
        .form-control-lg, .form-select-lg { padding: 10px 16px; font-size: 0.88rem; }
        .form-control:focus, .form-select:focus { border-color: #FF6B35; box-shadow: 0 0 0 0.2rem rgba(255,107,53,0.15); }
        .form-label { font-weight: 600; font-size: 0.78rem; color: #444; margin-bottom: 4px; }
        
        .sidebar .collapse .nav-link { font-size: 0.7rem; padding: 6px 14px 6px 28px; border-left: none; }
        .sidebar .collapse .nav-link:hover { background: rgba(255,107,53,0.08); color: #FF6B35; }
        .menu-toggle { transition: transform 0.3s; }
        .menu-toggle.collapsed { transform: rotate(-90deg); }
        a { color: #FF6B35; }
        a:hover { color: #e85d2a; }
        .text-primary { color: #FF6B35 !important; }
        .badge { font-size: 0.72rem; font-weight: 500; padding: 4px 10px; }
        .badge.bg-primary { background-color: #FF6B35 !important; }
        .page-link { color: #FF6B35; font-size: 0.8rem; padding: 6px 12px; }
        .page-link:focus, .page-link:hover { color: #e85d2a; }
        .page-item.active .page-link { background-color: #FF6B35; border-color: #FF6B35; }
        .form-check-input:checked { background-color: #FF6B35; border-color: #FF6B35; }
        .table { font-size: 0.8rem; margin-bottom: 0; }
        .table th { font-weight: 600; font-size: 0.76rem; text-transform: uppercase; letter-spacing: 0.5px; padding: 10px 12px !important; }
        .table td { padding: 10px 12px !important; vertical-align: middle; }
        .list-group-item { padding: 10px 16px; font-size: 0.82rem; }
        h1.h4, .h4 { font-size: 1.1rem; }
        h5, .h5 { font-size: 0.95rem; }
        h6, .h6 { font-size: 0.85rem; }
        small, .small { font-size: 0.75rem; }
        .text-muted { font-size: 0.78rem; }
        code { font-size: 0.78rem; }
        .alert { padding: 10px 16px; font-size: 0.82rem; margin-bottom: 0; }
        hr { margin: 16px 0; }
        .avatar-circle { width: 40px; height: 40px; }
        .border-bottom { padding-bottom: 12px !important; margin-bottom: 16px !important; }
        .action-btn { width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%; font-size: 15px; transition: all 0.25s ease; border: none; padding: 0; }
        .action-btn:hover { transform: scale(1.1); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
        .action-btn.approve { background: #22c55e; color: #fff; }
        .action-btn.approve:hover { background: #16a34a; }
        .action-btn.reject { background: #ef4444; color: #fff; }
        .action-btn.reject:hover { background: #dc2626; }
        .action-btn.detail { background: #3b82f6; color: #fff; }
        .action-btn.detail:hover { background: #2563eb; }
        .action-btn.view { background: #f59e0b; color: #fff; }
        .action-btn.view:hover { background: #d97706; }
        th.text-center, td.text-center { vertical-align: middle; text-align: center !important; }

        .app-topbar { display: flex; align-items: center; gap: 12px; padding: 10px 16px; background: #1a1a2e; color: #fff; }
        .app-topbar .app-nav-toggle { width: 38px; height: 38px; border: none; border-radius: 8px; background: rgba(255,255,255,0.08); color: #fff; font-size: 1.15rem; display: inline-flex; align-items: center; justify-content: center; transition: background .2s; }
        .app-topbar .app-nav-toggle:hover { background: rgba(255,107,53,0.35); }
        .app-topbar .app-topbar-title { font-weight: 700; font-size: 0.95rem; }

        .backdrop-sidebar { display: none; }

        .sidebar-open { overflow: hidden; }

        .profile-panel { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 10px 12px; border-top: 1px solid rgba(255,255,255,0.12); }
        .profile-info { display: flex; align-items: center; gap: 10px; flex: 1 1 auto; min-width: 0; text-decoration: none; }
        .profile-avatar { flex: 0 0 36px; width: 36px; height: 36px; border-radius: 50%; overflow: hidden; background: #fff; display: flex; align-items: center; justify-content: center; }
        .profile-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .profile-meta { flex: 1 1 auto; min-width: 0; display: flex; flex-direction: column; }
        .profile-name { font-size: 0.8rem; font-weight: 600; color: #fff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .profile-nim { font-size: 0.68rem; color: rgba(255,255,255,0.55); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .profile-logout { flex: 0 0 auto; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 8px; color: #ef4444; text-decoration: none; transition: background .2s; }
        .profile-logout:hover { background: rgba(255,255,255,0.1); color: #ef4444; }
        @media (max-width: 767.98px) { .profile-panel { padding: 9px 10px; gap: 8px; } }

        @media (max-width: 767.98px) {
            .app-sidebar {
                position: fixed !important;
                top: 0; left: 0; bottom: 0;
                width: 280px;
                max-width: 85vw;
                z-index: 1050;
                background: #1a1a2e;
                display: block !important;
                transform: translateX(-100%);
                transition: transform .3s ease;
                overflow-y: auto;
                box-shadow: 0 0 30px rgba(0,0,0,0.45);
            }
            .app-sidebar.open { transform: translateX(0); }
            .backdrop-sidebar {
                display: block;
                position: fixed;
                top: 0; left: 0; right: 0; bottom: 0;
                background: rgba(0,0,0,0.45);
                z-index: 1040;
                opacity: 0;
                pointer-events: none;
                transition: opacity .3s ease;
            }
            .backdrop-sidebar.open { opacity: 1; pointer-events: auto; }
        }
        @media (min-width: 768px) {
            .app-sidebar { transform: none !important; }
        }
        .app-layout { display: flex; flex-wrap: nowrap; align-items: stretch; }
        .app-sidebar { flex: 0 0 14.17%; max-width: 14.17%; }
        .app-main { flex: 1 1 auto; min-width: 0; }
        @media (min-width: 768px) and (max-width: 1199.98px) { .app-sidebar { flex: 0 0 21.25%; max-width: 21.25%; } }
    </style>
    
    <?php if (isset($messages) && !empty($messages)): ?>
        <div class="alert alert-<?php echo $messageType ?? 'success'; ?> alert-dismissible fade show" role="alert">
            <?php echo $messages; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
</head>
<body>

<div class="container-fluid">
    <?php if (isset($user)): ?>
    <!-- Top bar (mobile only) -->
    <div class="app-topbar d-md-none">
        <button type="button" class="app-nav-toggle" id="sidebarToggle" aria-label="Buka menu" aria-expanded="false" aria-controls="app-sidebar">
            <i class="fas fa-bars"></i>
        </button>
        <span class="app-topbar-title"><?php echo htmlspecialchars($siteName); ?></span>
    </div>
    <div class="backdrop-sidebar" id="sidebar-backdrop"></div>
    <?php endif; ?>
    <div class="row app-layout">
        <?php if (isset($user)): ?>
            <!-- Sidebar -->
            <nav class="d-md-block sidebar collapse d-flex flex-column app-sidebar" id="app-sidebar" aria-expanded="false">
                <div class="position-sticky pt-3 d-flex flex-column h-100">
                    <div class="d-flex align-items-center px-4 mb-4 text-white text-center nav-header">
                        <?php if ($logo): ?>
                        <img src="<?php echo htmlspecialchars($logo); ?>" style="height: 50px; width: auto; object-fit: contain; margin-right: 10px; padding: 4px; border-radius: 10px; background: #ffffff; box-shadow: 0 4px 12px rgba(255,255,255,1);" alt="logo">
                        <?php else: ?>
                        <i class="fas fa-university me-3"></i>
                        <?php endif; ?>
                        <span><?php echo htmlspecialchars($siteName); ?></span>
                    </div>
                    
                    <ul class="nav flex-column flex-grow-1">
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo SITE_URL; ?>">
                                <i class="fas fa-home me-3"></i> Dashboard
                            </a>
                        </li>
                        
                        <?php if (hasPermission('submit_document')): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo SITE_URL; ?>?page=form">
                                <i class="fas fa-file-alt me-3"></i> Pengajuan Surat
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php if (hasPermission('view_documents')): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo SITE_URL; ?>?page=list">
                                    <i class="fas fa-list me-3"></i> Daftar Surat
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php if (hasPermission('approve_documents')): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo SITE_URL; ?>?page=pending">
                                    <i class="fas fa-check-double me-3"></i> Persetujuan Surat
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php
$hasAdmin = hasPermission('manage_users') || hasPermission('manage_roles') || hasPermission('manage_permissions') || hasPermission('manage_settings');
$hasMaster = hasPermission('manage_document_types') || hasPermission('manage_departments') || hasPermission('manage_faculties') || hasPermission('manage_academic_years');
                        ?>
                        <?php if ($hasAdmin || $hasMaster): ?>
                            <?php if ($hasAdmin || $hasMaster): ?>
                            <li class="nav-item mt-3">
                                <div class="px-4 nav-header"><small>Pengaturan</small></div>
                            </li>
                            <?php endif; ?>
                            <?php if ($hasAdmin): ?>
                            <li class="nav-item">
                                <a class="nav-link d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#userMenu" role="button" aria-expanded="false" aria-controls="userMenu">
                                    <span><i class="fas fa-users-cog me-3"></i> Manajemen User</span>
                                    <i class="fas fa-chevron-down menu-toggle flex-shrink-0"></i>
                                </a>
                                <div class="collapse" id="userMenu">
                                    <ul class="nav flex-column ms-3 border-start border-secondary">
                                        <?php if (hasPermission('manage_users')): ?>
                                        <li class="nav-item">
                                            <a class="nav-link py-2 small" href="<?php echo SITE_URL; ?>?page=users">
                                                <i class="fas fa-list me-2"></i> Daftar User
                                            </a>
                                        </li>
                                        <?php endif; ?>
                                        <?php if (hasPermission('manage_roles')): ?>
                                        <li class="nav-item">
                                            <a class="nav-link py-2 small" href="<?php echo SITE_URL; ?>?page=roles">
                                                <i class="fas fa-user-tag me-2"></i> Role User
                                            </a>
                                        </li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </li>
                            <?php endif; ?>
                            <?php if ($hasMaster): ?>
                            <li class="nav-item">
                                <a class="nav-link d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#masterMenu" role="button" aria-expanded="false" aria-controls="masterMenu">
                                    <span><i class="fas fa-database me-3"></i> Master</span>
                                    <i class="fas fa-chevron-down menu-toggle flex-shrink-0"></i>
                                </a>
                                <div class="collapse" id="masterMenu">
                                    <ul class="nav flex-column ms-3 border-start border-secondary">
                                        <?php if (hasPermission('manage_document_types')): ?>
                                        <li class="nav-item">
                                            <a class="nav-link py-2 small" href="<?php echo SITE_URL; ?>?page=types">
                                                <i class="fas fa-tags me-2"></i> Jenis Surat
                                            </a>
                                        </li>
                                        <?php endif; ?>
                                        <?php if (hasPermission('manage_departments')): ?>
                                        <li class="nav-item">
                                            <a class="nav-link py-2 small" href="<?php echo SITE_URL; ?>?page=prodi">
                                                <i class="fas fa-building me-2"></i> Program Studi
                                            </a>
                                        </li>
                                        <?php endif; ?>
                                        <?php if (hasPermission('manage_faculties')): ?>
                                        <li class="nav-item">
                                            <a class="nav-link py-2 small" href="<?php echo SITE_URL; ?>?page=faculties">
                                                <i class="fas fa-graduation-cap me-2"></i> Fakultas
                                            </a>
                                        </li>
                                        <?php endif; ?>
                                        <?php if (hasPermission('manage_academic_years')): ?>
                                        <li class="nav-item">
                                            <a class="nav-link py-2 small" href="<?php echo SITE_URL; ?>?page=academic-years">
                                                <i class="fas fa-calendar-alt me-2"></i> Tahun Ajaran
                                            </a>
                                        </li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </li>
                            <?php endif; ?>
                            <?php if (hasPermission('manage_settings')): ?>
                            <li class="nav-item">
                                <a class="nav-link d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#settingMenu" role="button" aria-expanded="false" aria-controls="settingMenu">
                                    <span><i class="fas fa-cog me-3"></i> Setting Aplikasi</span>
                                    <i class="fas fa-chevron-down menu-toggle flex-shrink-0"></i>
                                </a>
                                <div class="collapse" id="settingMenu">
                                    <ul class="nav flex-column ms-3 border-start border-secondary">
                                        <li class="nav-item">
                                            <a class="nav-link py-2 small" href="<?php echo SITE_URL; ?>?page=settings">
                                                <i class="fas fa-sliders-h me-2"></i> General Setup
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link py-2 small" href="<?php echo SITE_URL; ?>?page=website">
                                                <i class="fas fa-globe me-2"></i> Web Setup
                                            </a>
                                        </li>
                                        <?php if (hasPermission('manage_informasi')): ?>
                                        <li class="nav-item">
                                            <a class="nav-link py-2 small" href="<?php echo SITE_URL; ?>?page=informasi">
                                                <i class="fas fa-bullhorn me-2"></i> Informasi LPPM
                                            </a>
                                        </li>
                                        <?php endif; ?>
                                        <?php if (!empty($user['role']) && $user['role'] === ROLE_ADMIN): ?>
                                        <li class="nav-item">
                                            <a class="nav-link py-2 small" href="<?php echo APP_ROOT_URL; ?>/audit_security.php" target="_blank">
                                                <i class="fas fa-shield-halved me-2"></i> Audit Keamanan
                                            </a>
                                        </li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </li>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php if (hasPermission('view_all_documents')): ?>
                            <li class="nav-item mt-3">
                                <div class="px-4 nav-header"><small>Laporan</small></div>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#laporanMenu" role="button" aria-expanded="false" aria-controls="laporanMenu">
                                    <span><i class="fas fa-chart-bar me-3"></i> Laporan</span>
                                    <i class="fas fa-chevron-down menu-toggle flex-shrink-0"></i>
                                </a>
                                <div class="collapse" id="laporanMenu">
                                    <ul class="nav flex-column ms-3 border-start border-secondary">
                                        <li class="nav-item">
                                            <a class="nav-link py-2 small" href="<?php echo SITE_URL; ?>?page=laporan-rekap">
                                                <i class="fas fa-list me-2"></i> Rekap Status
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link py-2 small" href="<?php echo SITE_URL; ?>?page=laporan-activity-log">
                                                <i class="fas fa-history me-2"></i> User Activity Log
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                        <?php endif; ?>
                    </ul>
                    
                    <div class="mt-auto">
                        <div class="profile-panel">
                            <a href="<?php echo SITE_URL; ?>?page=profile" class="profile-info">
                                <?php
                                $avatarInitials = '';
                                foreach (preg_split('/\s+/', trim($user['full_name'] ?? '')) as $i => $part) {
                                    if ($i >= 2 || $part === '') break;
                                    $avatarInitials .= mb_substr($part, 0, 1);
                                }
                                $avatarInitials = strtoupper($avatarInitials ?: '?');
                                ?>
                                <div class="profile-avatar" style="background:#FF6B35;color:#fff;font-weight:800;font-size:0.9rem;"><?php echo htmlspecialchars($avatarInitials); ?></div>
                                <div class="profile-meta">
                                    <span class="profile-name"><?php echo htmlspecialchars($user['full_name']); ?></span>
                                    <span class="profile-nim"><?php echo htmlspecialchars($user['nip'] ?? ''); ?></span>
                                </div>
                            </a>
                            <a href="<?php echo SITE_URL; ?>?logout=1" class="profile-logout" title="Logout">
                                <i class="fas fa-sign-out-alt" style="font-size: 0.9rem;"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </nav>
        <?php endif; ?>

        <!-- Main Content -->
        <main class="<?php echo isset($user) ? 'app-main' : 'col-12'; ?> px-md-4 py-4">
            <!-- Page Header -->
            <?php if (isset($page_title) && !isset($hide_page_title)): ?>
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h4"><?php echo htmlspecialchars($page_title); ?></h1>
                </div>
            <?php endif; ?>

            <!-- Content -->
            <?php if (isset($content)): ?>
                <?php echo $content; ?>
            <?php else: ?>
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-md-8">
                            <p class="text-muted">Loading content...</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

        </main>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo APP_ROOT_URL . '/assets/js/main.js'; ?>"></script>

<?php if (isset($scripts)): ?>
    <?php foreach ($scripts as $script): ?>
        <script src="<?php echo $script; ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>

</body>
</html>