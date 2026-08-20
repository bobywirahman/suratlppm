<?php
$page_title = 'Daftar Akun Baru';
$hide_page_title = true;
$regSiteName = getSetting('site_name', APP_NAME);
$regLogo = asset(getSetting('logo', ''));
?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-xl-7">
            <div class="card border-0 shadow rounded-4">
                <div class="card-header text-center py-3">
                    <?php if ($regLogo): ?>
                    <img src="<?php echo htmlspecialchars($regLogo); ?>" style="height: 80px; width: auto; object-fit: contain; margin-bottom: 12px; filter: drop-shadow(0 4px 8px rgba(0,0,0,0.15));" alt="logo">
                    <?php else: ?>
                    <i class="fas fa-university fa-2x mb-1"></i>
                    <?php endif; ?>
                    <h4 class="fw-bold mb-0"><?php echo htmlspecialchars($regSiteName); ?></h4>
                    <p class="mb-0 small opacity-75">Daftar Akun Baru</p>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="<?php echo SITE_URL; ?>">
                        <input type="hidden" name="register" value="1">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label"><i class="fas fa-user me-1 text-primary"></i> Nama Lengkap *</label>
                                <input type="text" name="full_name" class="form-control" placeholder="Nama lengkap" required value="<?php echo htmlspecialchars($old['full_name'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><i class="fas fa-id-card me-1 text-primary"></i> NIM/NIP YAYASAN *</label>
                                <input type="text" name="nim" class="form-control" placeholder="Nomor Induk Mahasiswa" required value="<?php echo htmlspecialchars($old['nim'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><i class="fas fa-envelope me-1 text-primary"></i> Email *</label>
                                <input type="email" name="email" class="form-control" placeholder="email@example.com" required value="<?php echo htmlspecialchars($old['email'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><i class="fas fa-phone me-1 text-primary"></i> No. HP *</label>
                                <input type="tel" name="no_hp" class="form-control" placeholder="08xxxxxxxxxx" required value="<?php echo htmlspecialchars($old['no_hp'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><i class="fas fa-university me-1 text-primary"></i> Fakultas *</label>
                                <select name="faculty_id" class="form-select" id="faculty" required>
                                    <option value="">Pilih Fakultas</option>
                                    <?php foreach ($faculties as $f): ?>
                                        <option value="<?php echo $f['id']; ?>" <?php echo ($old['faculty_id'] ?? '') == $f['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($f['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><i class="fas fa-building me-1 text-primary"></i> Program Studi *</label>
                                <select name="department_id" class="form-select" id="department" required>
                                    <option value="">Pilih Program Studi</option>
                                    <?php foreach ($departments as $d): ?>
                                        <option value="<?php echo $d['id']; ?>" data-faculty="<?php echo $d['faculty_id']; ?>" <?php echo ($old['department_id'] ?? '') == $d['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($d['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label"><i class="fas fa-map-marker-alt me-1 text-primary"></i> Alamat *</label>
                                <textarea name="alamat" class="form-control" rows="2" placeholder="Alamat lengkap" required><?php echo htmlspecialchars($old['alamat'] ?? ''); ?></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><i class="fas fa-user-circle me-1 text-primary"></i> Username *</label>
                                <input type="text" name="username" class="form-control" placeholder="Username" required value="<?php echo htmlspecialchars($old['username'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><i class="fas fa-lock me-1 text-primary"></i> Password *</label>
                                <input type="password" name="password" class="form-control" placeholder="Minimal 8 karakter" required minlength="8">
                                <div class="mt-2">
                                    <label class="form-label"><i class="fas fa-lock me-1 text-primary"></i> Konfirmasi Password *</label>
                                    <input type="password" name="konfirmasi_password" class="form-control" placeholder="Ulangi password" required minlength="8">
                                </div>
                            </div>
                        </div>

                        <div class="d-grid mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-user-plus me-2"></i> Daftar Sekarang
                            </button>
                        </div>
                    </form>

                    <hr class="my-3" style="opacity: 0.15;">
                    <div class="text-center">
                        <p class="mb-1 text-muted small">Sudah punya akun?</p>
                        <div class="d-flex gap-2 justify-content-center">
                            <a href="<?php echo BASE_URL; ?>/" class="btn btn-light btn-sm rounded-pill px-3">
                                <i class="fas fa-home me-1"></i> Home
                            </a>
                            <a href="<?php echo SITE_URL; ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                <i class="fas fa-sign-in-alt me-2"></i> Login
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
document.getElementById('faculty')?.addEventListener('change', function() {
    const fid = this.value;
    document.querySelectorAll('#department option').forEach(function(opt) {
        if (opt.value === '') return;
        opt.style.display = opt.dataset.faculty === fid ? '' : 'none';
    });
    document.getElementById('department').value = '';
});
<?php if (!empty($old['faculty_id'])): ?>
document.getElementById('faculty').dispatchEvent(new Event('change'));
<?php endif; ?>
</script>
