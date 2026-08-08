<?php
$page_title = 'Profil Saya';
$pdo = $pdo ?? db();
$faculties = $faculties ?? $pdo->query("SELECT * FROM faculties ORDER BY name")->fetchAll();
$departments = $departments ?? $pdo->query("SELECT * FROM departments ORDER BY name")->fetchAll();
?>
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow rounded-4">
                <div class="card-header text-center py-3">
                    <i class="fas fa-user-circle fa-2x mb-1"></i>
                    <h4 class="fw-bold mb-0">Profil Saya</h4>
                    <p class="mb-0 small opacity-75">Perbarui data diri Anda</p>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="<?php echo SITE_URL; ?>">
                        <input type="hidden" name="profile-save" value="1">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label"><i class="fas fa-user me-1 text-primary"></i> Nama Lengkap *</label>
                                <input type="text" name="full_name" class="form-control" required value="<?php echo htmlspecialchars($user['full_name']); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><i class="fas fa-id-card me-1 text-primary"></i> NIP</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['nip'] ?? ''); ?>" readonly disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><i class="fas fa-envelope me-1 text-primary"></i> Email *</label>
                                <input type="email" name="email" class="form-control" required value="<?php echo htmlspecialchars($user['email']); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><i class="fas fa-phone me-1 text-primary"></i> No. HP</label>
                                <input type="tel" name="no_hp" class="form-control" value="<?php echo htmlspecialchars($user['no_hp'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><i class="fas fa-user-circle me-1 text-primary"></i> Username</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>" readonly disabled>
                            </div>
                            <div class="col-12">
                                <label class="form-label"><i class="fas fa-map-marker-alt me-1 text-primary"></i> Alamat</label>
                                <textarea name="alamat" class="form-control" rows="2"><?php echo htmlspecialchars($user['alamat'] ?? ''); ?></textarea>
                            </div>
                        </div>

                        <hr class="my-3">
                        <h6 class="fw-bold"><i class="fas fa-lock me-1 text-primary"></i> Ubah Password</h6>
                        <p class="text-muted small">Kosongkan jika tidak ingin mengubah password</p>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label">Password Baru</label>
                                <input type="password" name="password" class="form-control" placeholder="Minimal 8 karakter" minlength="8">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Konfirmasi Password Baru</label>
                                <input type="password" name="konfirmasi_password" class="form-control" placeholder="Ulangi password baru">
                            </div>
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
