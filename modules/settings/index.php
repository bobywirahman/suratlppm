<?php
$page_title = 'Setting Aplikasi';
$pdo = db();

$settings = [];
$stmt = $pdo->query("SELECT setting_key, setting_value FROM app_settings");
foreach ($stmt->fetchAll() as $s) {
    $settings[$s['setting_key']] = $s['setting_value'];
}

$siteName = $settings['site_name'] ?? APP_NAME;
$siteDesc = $settings['site_description'] ?? 'LPPM Universitas Fort de Kock';
$adminPhone = $settings['admin_phone'] ?? '';
$favicon = asset($settings['favicon'] ?? '');
$logo = asset($settings['logo'] ?? '');
ob_start();
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header" style="background: linear-gradient(135deg, #FF6B35 0%, #e85d2a 100%); color: white; border-radius: 10px 10px 0 0 !important;">
                <h5 class="mb-0"><i class="fas fa-cog me-2"></i> Pengaturan Aplikasi</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="?page=settings-save" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Nama Website / Aplikasi *</label>
                        <input type="text" name="site_name" class="form-control" required value="<?php echo htmlspecialchars($siteName); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Deskripsi / Subtitle</label>
                        <input type="text" name="site_description" class="form-control" value="<?php echo htmlspecialchars($siteDesc); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">No. HP Kontak Admin</label>
                        <input type="text" name="admin_phone" class="form-control" value="<?php echo htmlspecialchars($adminPhone); ?>" placeholder="08xxxxxxxxxx">
                    </div>
                    <hr class="my-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Favicon (ICO/PNG, 32x32 px)</label>
                        <input type="file" name="favicon" class="form-control" accept=".ico,.png">
                        <?php if ($favicon): ?>
                        <div class="mt-2 d-flex align-items-center gap-2">
                            <img src="<?php echo htmlspecialchars($favicon); ?>" style="height: 32px; width: 32px; object-fit: contain;" alt="favicon">
                            <small class="text-muted">Favicon saat ini</small>
                            <label class="text-danger small mb-0">
                                <input type="checkbox" name="delete_favicon" value="1"> Hapus
                            </label>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Logo Aplikasi</label>
                        <input type="file" name="logo" class="form-control" accept=".png,.jpg,.jpeg,.svg">
                        <?php if ($logo): ?>
                        <div class="mt-2 d-flex align-items-center gap-2">
                            <img src="<?php echo htmlspecialchars($logo); ?>" style="height: 50px; width: auto; object-fit: contain;" alt="logo">
                            <small class="text-muted">Logo saat ini</small>
                            <label class="text-danger small mb-0">
                                <input type="checkbox" name="delete_logo" value="1"> Hapus
                            </label>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary rounded-pill px-4"><i class="fas fa-save me-2"></i> Simpan</button>
                        <a href="<?php echo SITE_URL; ?>" class="btn btn-outline-secondary rounded-pill px-4">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean(); require __DIR__ . '/../layouts/master.php'; ?>
