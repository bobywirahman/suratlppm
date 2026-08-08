<?php $loginSiteName = getSetting('site_name', APP_NAME); $loginSiteDesc = getSetting('site_description', ''); $loginLogo = asset(getSetting('logo', '')); ?>
<div class="container d-flex align-items-center min-vh-100 py-4">
    <div class="col-md-6 col-lg-4 mx-auto">
        <div class="card shadow border-0 rounded-4">
            <div class="card-header text-center py-3">
                <?php if ($loginLogo): ?>
                <img src="<?php echo htmlspecialchars($loginLogo); ?>" style="height: 80px; width: auto; object-fit: contain; margin-bottom: 12px; filter: drop-shadow(0 4px 8px rgba(0,0,0,0.15));" alt="logo">
                <?php else: ?>
                <i class="fas fa-university fa-2x mb-1"></i>
                <?php endif; ?>
                <h5 class="fw-bold mb-0"><?php echo htmlspecialchars($loginSiteName); ?></h5>
                <small class="opacity-75"><?php echo htmlspecialchars($loginSiteDesc); ?></small>
            </div>
            <div class="card-body p-4">

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger py-2 small" role="alert">
                        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?php echo SITE_URL; ?>">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email / Username</label>
                        <input type="text" class="form-control" id="email" name="email" 
                               placeholder="Masukkan email atau username" required autofocus>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" 
                               placeholder="Masukkan password Anda" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-sign-in-alt me-2"></i> Login
                    </button>
                </form>

                <hr class="my-3" style="opacity: 0.1;">

                <div class="text-center">
                    <p class="mb-2 small text-muted">Belum punya akun? Daftar sekarang</p>
                    <div class="d-flex justify-content-center gap-2">
                        <a href="<?php echo BASE_URL; ?>/" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                            <i class="fas fa-home me-2"></i> Home
                        </a>
                        <a href="<?php echo SITE_URL; ?>?page=register" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                            <i class="fas fa-user-plus me-2"></i> Daftar
                        </a>
                    </div>
                </div>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success mt-3 py-2 small" role="alert">
                        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>


