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
                    <?php if (!empty($registrasi)): ?>
                    <div class="bg-light rounded-3 p-3 mb-3 text-start" style="text-align:left;">
                        <small class="text-muted d-block mb-2 fw-bold"><i class="fas fa-save me-1"></i> Data Login Anda &mdash; simpan baik-baik:</small>
                        <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
                            <span class="small text-muted flex-shrink-0">Email</span>
                            <span class="d-flex align-items-center gap-2">
                                <code class="text-dark text-break"><?php echo htmlspecialchars($registrasi['email']); ?></code>
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-2 copy-btn" data-value="<?php echo htmlspecialchars($registrasi['email'], ENT_QUOTES); ?>" title="Salin"><i class="fas fa-copy"></i></button>
                            </span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
                            <span class="small text-muted flex-shrink-0">Username</span>
                            <span class="d-flex align-items-center gap-2">
                                <code class="text-dark text-break"><?php echo htmlspecialchars($registrasi['username']); ?></code>
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-2 copy-btn" data-value="<?php echo htmlspecialchars($registrasi['username'], ENT_QUOTES); ?>" title="Salin"><i class="fas fa-copy"></i></button>
                            </span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center gap-2">
                            <span class="small text-muted flex-shrink-0">Password</span>
                            <span class="d-flex align-items-center gap-2">
                                <code class="text-dark text-break"><?php echo htmlspecialchars($registrasi['password']); ?></code>
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-2 copy-btn" data-value="<?php echo htmlspecialchars($registrasi['password'], ENT_QUOTES); ?>" title="Salin"><i class="fas fa-copy"></i></button>
                            </span>
                        </div>
                    </div>
                    <script>
                    (function() {
                        var copyBtns = document.querySelectorAll('.copy-btn');
                        function fallback(text, btn) {
                            var ta = document.createElement('textarea');
                            ta.value = text;
                            ta.style.position = 'fixed';
                            ta.style.opacity = '0';
                            document.body.appendChild(ta);
                            ta.select();
                            try { document.execCommand('copy'); flash(btn); } catch (e) {}
                            document.body.removeChild(ta);
                        }
                        function flash(btn) {
                            btn.innerHTML = '<i class="fas fa-check"></i>';
                            setTimeout(function() { btn.innerHTML = '<i class="fas fa-copy"></i>'; }, 1500);
                        }
                        copyBtns.forEach(function(btn) {
                            btn.addEventListener('click', function() {
                                var text = btn.getAttribute('data-value');
                                if (navigator.clipboard && window.isSecureContext) {
                                    navigator.clipboard.writeText(text).then(function() { flash(btn); }).catch(function() { fallback(text, btn); });
                                } else {
                                    fallback(text, btn);
                                }
                            });
                        });
                    })();
                    </script>
                    <?php endif; ?>
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
