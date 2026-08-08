<?php
$page_title = 'Dashboard';
?>

<div class="row mb-3">
    <div class="col-lg-6 mb-3 mb-lg-0">
        <div class="card border-0 rounded-3 h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i> Statistik Surat</h5>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-6 col-md-3">
                        <div class="border rounded-3 p-2 text-center bg-light">
                            <div class="fw-bold fs-5 mb-0" style="color:#FF6B35;"><?php echo $statistics['total'] ?? 0; ?></div>
                            <small class="text-muted">Total Surat</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="border rounded-3 p-2 text-center bg-light">
                            <div class="fw-bold fs-5 mb-0" style="color:#FFB800;"><?php echo $statistics['submitted'] ?? 0; ?></div>
                            <small class="text-muted">Menunggu</small>
                        </div>
                    </div>
                    <?php if (hasPermission('approve_documents')): ?>
                    <div class="col-6 col-md-3">
                        <div class="border rounded-3 p-2 text-center bg-light">
                            <div class="fw-bold fs-5 mb-0" style="color:#22c55e;"><?php echo $statistics['approved'] ?? 0; ?></div>
                            <small class="text-muted">Disetujui</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="border rounded-3 p-2 text-center bg-light">
                            <div class="fw-bold fs-5 mb-0" style="color:#ef4444;"><?php echo $statistics['rejected'] ?? 0; ?></div>
                            <small class="text-muted">Ditolak</small>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php if (hasPermission('manage_users')): ?>
    <div class="col-lg-6">
        <div class="card border-0 rounded-3 h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-users me-2"></i> Statistik User</h5>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-4">
                        <div class="border rounded-3 p-2 text-center bg-light">
                            <div class="fw-bold fs-5 mb-0" style="color:#FF6B35;"><?php echo $userStats['total']; ?></div>
                            <small class="text-muted">Total User</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded-3 p-2 text-center bg-light">
                            <div class="fw-bold fs-5 mb-0" style="color:#22c55e;"><?php echo $userStats['active']; ?></div>
                            <small class="text-muted">Aktif</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded-3 p-2 text-center bg-light">
                            <div class="fw-bold fs-5 mb-0" style="color:#FFB800;"><?php echo $userStats['pending']; ?></div>
                            <small class="text-muted">Belum Aktif</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php $canMigrate = hasPermission('manage_permissions') || hasPermission('manage_document_types') || hasPermission('manage_academic_years') || hasPermission('manage_settings'); ?>

<div class="row">
    <!-- Recent Documents -->
    <div class="col-lg-8 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i> Surat Terbaru</h5>
                <a href="<?php echo SITE_URL; ?>?page=list" class="btn btn-sm btn-light rounded-pill px-3" style="color:#FF6B35;">Lihat Semua</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($recentDocuments)): ?>
                    <p class="text-muted text-center py-4 mb-0">Tidak ada surat yang terdaftar</p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recentDocuments as $doc): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="fw-semibold mb-1"><?php echo htmlspecialchars($doc['title']); ?></div>
                                    <small class="text-muted"><i class="fas fa-user me-1"></i><?php echo htmlspecialchars($doc['applicant_name']); ?> &middot; <?php echo htmlspecialchars($doc['department_name'] ?? 'N/A'); ?><?php $wa = waNumber($doc['applicant_phone'] ?? ''); if ($wa !== ''): ?> &middot; <a href="https://wa.me/<?php echo $wa; ?>" target="_blank" rel="noopener" class="text-decoration-none text-success" title="Chat via WhatsApp"><i class="fab fa-whatsapp me-1"></i><?php echo htmlspecialchars($doc['applicant_phone']); ?></a><?php endif; ?></small><br>
                                    <?php
                                    $badgeMap = [
                                        STATUS_DRAFT => 'secondary',
                                        STATUS_SUBMITTED => 'warning',
                                        STATUS_IN_PROGRESS => 'info',
                                        STATUS_APPROVED => 'success',
                                        STATUS_REJECTED => 'danger',
                                        STATUS_COMPLETED => 'success',
                                    ];
                                    $labelMap = [
                                        STATUS_DRAFT => 'Draft',
                                        STATUS_SUBMITTED => 'Diajukan',
                                        STATUS_IN_PROGRESS => 'Diproses',
                                        STATUS_APPROVED => 'Disetujui',
                                        STATUS_REJECTED => 'Ditolak',
                                        STATUS_COMPLETED => 'Selesai',
                                    ];
                                    ?>
                                    <span class="badge bg-<?php echo $badgeMap[$doc['status']] ?? 'info'; ?>">
                                        <?php echo $labelMap[$doc['status']] ?? ucfirst($doc['status']); ?>
                                    </span>
                                </div>
                                <div class="text-end">
                                    <small class="text-muted"><?php echo isset($doc['updated_at']) ? date('d/m/Y', strtotime($doc['updated_at'])) : ''; ?></small>
                                    <?php if (hasPermission('view_documents')): ?>
                                        <div class="mt-1"><a href="<?php echo SITE_URL; ?>?page=detail&id=<?php echo $doc['id']; ?>" class="btn btn-sm btn-outline-primary py-0 px-2"><i class="fas fa-eye"></i></a></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if ($canMigrate): ?>
    <!-- Database -->
    <div class="col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-database me-2"></i> Database</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <?php if (hasPermission('manage_permissions')): ?>
                    <a href="?page=install-permissions" class="btn btn-warning btn-sm rounded-pill px-3"><i class="fas fa-sync me-1"></i> Inisialisasi Hak Akses</a>
                    <?php endif; ?>
                    <?php if (hasPermission('manage_document_types')): ?>
                    <a href="?page=migrate-text-input" class="btn btn-warning btn-sm rounded-pill px-3"><i class="fas fa-sync me-1"></i> Migrasi Input Teks</a>
                    <?php endif; ?>
                    <?php if (hasPermission('manage_academic_years')): ?>
                    <a href="?page=academic-years-migrate" class="btn btn-warning btn-sm rounded-pill px-3"><i class="fas fa-sync me-1"></i> Migrasi Tahun Ajaran & Kolom</a>
                    <?php endif; ?>
                    <?php if (hasPermission('manage_settings')): ?>
                    <a href="?page=settings-migrate" class="btn btn-warning btn-sm rounded-pill px-3"><i class="fas fa-sync me-1"></i> Migrasi Setting Aplikasi</a>
                    <a href="?page=revisi-migrate" class="btn btn-warning btn-sm rounded-pill px-3"><i class="fas fa-sync me-1"></i> Migrasi Riwayat Revisi</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
