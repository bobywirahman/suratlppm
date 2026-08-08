<?php
$page_title = 'Informasi';
$hide_page_title = true;
$pdo = db();
$admin_hp = getSetting('admin_phone', '');
if ($admin_hp === '') {
    $admin_hp = getSetting('contact_phone', '');
}
if ($admin_hp === '') {
    $admin = $pdo->query("SELECT u.no_hp FROM users u
                        JOIN user_roles ur ON ur.user_id = u.id
                        JOIN roles r ON r.id = ur.role_id
                        WHERE r.name = 'admin' AND u.no_hp IS NOT NULL AND u.no_hp <> ''
                        LIMIT 1")->fetch();
    $admin_hp = $admin ? $admin['no_hp'] : '';
}
?>
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="card shadow border-0 rounded-4 text-center">
                <div class="card-header py-3">
                    <i class="fas fa-check-circle fa-3x mb-1"></i>
                    <h5 class="fw-bold mb-0"><?php echo $page_title; ?></h5>
                </div>
                <div class="card-body p-4">
                    <p class="mb-3"><?php echo htmlspecialchars($pesan ?? ''); ?></p>
                    <div class="bg-light rounded-3 p-3 mb-3">
                        <small class="text-muted d-block mb-2">Hubungi Admin untuk Aktivasi:</small>
                        <a href="https://wa.me/62<?php echo ltrim(preg_replace('/[^0-9]/', '', $admin_hp), '0'); ?>" class="btn btn-success btn-sm rounded-pill px-3" target="_blank">
                            <i class="fab fa-whatsapp me-2"></i> <?php echo htmlspecialchars($admin_hp); ?>
                        </a>
                    </div>
                    <hr class="my-3" style="opacity: 0.15;">
                    <a href="<?php echo SITE_URL; ?>" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt me-2"></i> Login
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
