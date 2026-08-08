<?php $page_title = 'Daftar Surat'; ?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="mb-0"><i class="fas fa-list me-2"></i> Daftar Surat Saya</h5>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <form method="GET" action="?page=list" class="d-flex align-items-center gap-2 mb-0">
                <input type="hidden" name="page" value="list">
                <span class="small">Tampilkan</span>
                <select name="per_page" class="form-select form-select-sm" style="width:auto;" onchange="this.form.submit()">
                    <?php foreach ($perPageOptions as $opt): ?>
                    <option value="<?php echo $opt; ?>" <?php echo (string)$perPageReq === (string)$opt ? 'selected' : ''; ?>><?php echo $opt === 'all' ? 'Semua' : $opt; ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
            <a href="?page=form" class="btn btn-sm btn-primary rounded-pill px-3"><i class="fas fa-plus me-1"></i> Buat Surat</a>
        </div>
    </div>

    <div class="card-body p-0">
        <!-- Search & Filter -->
        <form method="GET" class="row g-2 mb-3 align-items-end px-3 pt-3">
            <input type="hidden" name="page" value="list">
            <input type="hidden" name="per_page" value="<?php echo htmlspecialchars($perPageReq); ?>">
            <div class="col-md-5">
                <input type="text" name="search" class="form-control p-2 border-primary"
                       placeholder="Cari berdasarkan judul, nomor, jenis, prodi, pengaju..." value="<?php echo htmlspecialchars($filters['search'] ?? ''); ?>">
            </div>
            <div class="col-auto">
                <select name="department_id" class="form-select p-2 border-primary">
                    <option value="">Semua Prodi</option>
                    <?php foreach ($userDepartments as $dept): ?>
                        <option value="<?php echo htmlspecialchars($dept['id']); ?>" <?php echo (!empty($filters['department_id']) && $filters['department_id'] == $dept['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($dept['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary p-2 px-3"><i class="fas fa-search me-1"></i> Cari</button>
            </div>
            <?php if (!empty($filters['search']) || !empty($filters['department_id'])): ?>
            <div class="col-auto">
                <a href="?page=list&per_page=<?php echo htmlspecialchars($perPageReq); ?>" class="btn btn-outline-secondary p-2 px-3"><i class="fas fa-times me-1"></i> Hapus</a>
            </div>
            <?php endif; ?>
        </form>

        <?php if (empty($documents)): ?>
            <?php if (!empty($filters['search']) || !empty($filters['department_id'])): ?>
                <div class="text-center py-5">
                    <i class="fas fa-search fa-3x text-muted mb-2"></i>
                    <p class="text-muted mb-0">Tidak ada surat yang cocok dengan pencarian Anda</p>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-2"></i>
                    <p class="text-muted mb-0">Belum ada surat yang diajukan</p>
                    <a href="?page=form" class="btn btn-primary mt-2"><i class="fas fa-plus me-1"></i> Buat Surat Baru</a>
                </div>
            <?php endif; ?>
        <?php else: ?>
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-primary text-white">
                        <tr>
                            <th class="ps-3">No</th>
                            <?php if (hasPermission('view_all_documents')): ?>
                                <th>Pengaju</th>
                                <th>Program Studi</th>
                            <?php endif; ?>
                            <th>No. HP Pengaju</th>
                            <th>Judul Surat</th>
                            <th>Tipe</th>
                            <th>Status</th>
                            <th style="min-width:140px;">Tanggal / Riwayat</th>
                            <th class="pe-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($documents as $i => $doc):
                            $s = $doc['status'];
                            $statusMap = [
                                STATUS_DRAFT => ['label' => 'Draft', 'class' => 'bg-secondary'],
                                STATUS_SUBMITTED => ['label' => 'Diajukan', 'class' => 'bg-warning text-dark'],
                                STATUS_IN_PROGRESS => ['label' => 'Diproses', 'class' => 'bg-info'],
                                STATUS_APPROVED => ['label' => 'Disetujui', 'class' => 'bg-success'],
                                STATUS_REVISI => ['label' => 'Revisi', 'class' => 'bg-warning text-dark'],
                                STATUS_COMPLETED => ['label' => 'Selesai', 'class' => 'bg-success'],
                            ];
                            $st = $statusMap[$s] ?? ['label' => ucfirst($s), 'class' => 'bg-secondary'];

                            // Build timeline text
                            $created = $doc['created_at'] ?? $doc['updated_at'] ?? '';
                            $timeline = [];
                            if ($s === STATUS_DRAFT) {
                                $timeline[] = '<small class="text-muted d-block">Dibuat: ' . date('d/m/Y H:i', strtotime($created)) . '</small>';
                            } else {
                                $timeline[] = '<small class="text-muted d-block">Diajukan: ' . date('d/m/Y H:i', strtotime($doc['submitted_at'] ?? $created)) . '</small>';
                            }
                            if (!empty($doc['in_progress_at'])) {
                                $timeline[] = '<small class="text-muted d-block">Diproses: ' . date('d/m/Y H:i', strtotime($doc['in_progress_at'])) . '</small>';
                            }
                            if ($s === STATUS_APPROVED && !empty($doc['approved_at'])) {
                                $timeline[] = '<small class="text-success d-block">Disetujui: ' . date('d/m/Y H:i', strtotime($doc['approved_at'])) . '</small>';
                            }
                            if (!empty($doc['completed_at'])) {
                                $timeline[] = '<small class="text-success d-block">Selesai: ' . date('d/m/Y H:i', strtotime($doc['completed_at'])) . '</small>';
                            }
                            if ($s === STATUS_REVISI && !empty($doc['in_progress_at'])) {
                                $timeline[] = '<small class="text-warning d-block">Direvisi: ' . date('d/m/Y H:i', strtotime($doc['in_progress_at'])) . '</small>';
                            }
                        ?>
                        <tr>
                            <td class="ps-3"><?php echo $offset + $i + 1; ?></td>
                            <?php if (hasPermission('view_all_documents')): ?>
                                <td><?php echo htmlspecialchars($doc['applicant_name'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($doc['department_name'] ?? '-'); ?></td>
                            <?php endif; ?>
                            <?php $wa = waNumber($doc['applicant_phone'] ?? ''); ?>
                            <td>
                                <?php if ($wa !== ''): ?>
                                    <a href="https://wa.me/<?php echo $wa; ?>" target="_blank" rel="noopener" class="text-decoration-none text-success" title="Chat via WhatsApp">
                                        <i class="fab fa-whatsapp me-1"></i><?php echo htmlspecialchars($doc['applicant_phone']); ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="fw-semibold"><?php echo htmlspecialchars($doc['title']); ?></td>
                            <td><span class="badge bg-secondary"><?php echo ucfirst(str_replace('_', ' ', $doc['type'])); ?></span></td>
                            <td><span class="badge <?php echo $st['class']; ?>"><?php echo $st['label']; ?></span></td>
                            <td><?php echo implode('', $timeline); ?></td>
                            <td class="pe-3">
                                <a href="?page=detail&id=<?php echo $doc['id']; ?>" class="btn btn-sm btn-outline-primary py-0 px-2" title="Detail"><i class="fas fa-eye"></i></a>
                                <?php if ($s === STATUS_DRAFT): ?>
                                    <a href="?page=form&edit=<?php echo $doc['id']; ?>" class="btn btn-sm btn-outline-secondary py-0 px-2" title="Edit Draft"><i class="fas fa-edit"></i></a>
                                    <a href="?page=submit-draft&id=<?php echo $doc['id']; ?>" class="btn btn-sm btn-outline-success py-0 px-2" title="Ajukan" onclick="return confirm('Yakin ingin mengajukan draft ini?')"><i class="fas fa-paper-plane"></i></a>
                                    <a href="?page=delete-draft&id=<?php echo $doc['id']; ?>" class="btn btn-sm btn-outline-danger py-0 px-2" title="Hapus Draft" onclick="return confirm('Yakin ingin menghapus draft ini?')"><i class="fas fa-trash"></i></a>
                                <?php endif; ?>
                                <?php if ($s === STATUS_REVISI && hasPermission('submit_document')): ?>
                                    <a href="?page=form&edit=<?php echo $doc['id']; ?>" class="btn btn-sm btn-outline-warning py-0 px-2" title="Perbaiki & Ajukan Ulang"><i class="fas fa-rotate-left"></i></a>
                                <?php endif; ?>
                                <?php if (in_array($s, [STATUS_APPROVED, STATUS_COMPLETED])): ?>
                                    <a href="?page=print&id=<?php echo $doc['id']; ?>" class="btn btn-sm btn-outline-success py-0 px-2" title="Cetak PDF" target="_blank"><i class="fas fa-print"></i></a>
                                <?php endif; ?>
                                <?php if ($s !== STATUS_DRAFT && hasPermission('delete_approved_documents')): ?>
                                    <a href="?page=delete-verified-document&id=<?php echo $doc['id']; ?>&from=list" class="btn btn-sm btn-outline-danger py-0 px-2" title="Hapus Surat" onclick="return confirm('Yakin ingin menghapus surat \'<?php echo htmlspecialchars(addslashes($doc['title'])); ?>\'? Tindakan ini tidak dapat dibatalkan.')"><i class="fas fa-trash"></i></a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php
            $qs = [];
            if (!empty($filters['status'])) $qs[] = 'status=' . urlencode($filters['status']);
            if (!empty($filters['department_id'])) $qs[] = 'department_id=' . urlencode($filters['department_id']);
            if (!empty($filters['search'])) $qs[] = 'search=' . urlencode($filters['search']);
            $qs[] = 'per_page=' . urlencode($perPageReq);
            $q = implode('&', $qs);
            ?>
            <div class="d-flex align-items-center justify-content-between px-3 py-2 border-top flex-wrap gap-2">
                <?php if ($perPageReq === 'all'): ?>
                <small class="text-muted">Menampilkan 1 - <?php echo $total; ?> dari <?php echo $total; ?> data</small>
                <?php else: ?>
                <small class="text-muted">Menampilkan <?php echo $offset + 1; ?> - <?php echo min($offset + $perPage, $total); ?> dari <?php echo $total; ?> data</small>
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=list&hal=<?php echo $page - 1; ?>&<?php echo $q; ?>"><i class="fas fa-chevron-left"></i></a>
                        </li>
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=list&hal=<?php echo $i; ?>&<?php echo $q; ?>"><?php echo $i; ?></a>
                        </li>
                        <?php endfor; ?>
                        <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=list&hal=<?php echo $page + 1; ?>&<?php echo $q; ?>"><i class="fas fa-chevron-right"></i></a>
                        </li>
                    </ul>
                </nav>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>