<?php
$page_title = 'Persetujuan Surat';
$lastNomor = (string)($pdo->query("SELECT document_number FROM documents WHERE document_number IS NOT NULL AND document_number <> '' ORDER BY id DESC LIMIT 1")->fetchColumn() ?: '');
?>

<div class="row">
    <div class="col-12">
        <?php if (isset($departments) && count($departments)): ?>
            <div class="card mb-4 shadow border-primary">
                <div class="card-header bg-white">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-check-double me-2 text-primary"></i> Persetujuan Surat</h5>
                </div>
            </div>
        <?php endif; ?>

        <!-- Documents Table -->
        <div class="card shadow mb-4 border-primary">
            <div class="card-body p-4">
                <?php if (empty($documents)): ?>
                    <?php if (hasPermission('submit_document')): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted">Tidak ada surat yang menunggu persetujuan</h5>
                        <a href="<?php echo SITE_URL; ?>?page=form" class="btn btn-primary mt-2 rounded-pill px-4 py-2">
                            <i class="fas fa-plus me-2"></i> Buat Surat Baru
                        </a>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted">Tidak ada surat yang menunggu persetujuan</h5>
                    </div>
                    <?php endif; ?>
                <?php else: ?>
                    <!-- Search & Filter -->
                    <form method="GET" class="row g-2 mb-3 align-items-end">
                        <input type="hidden" name="page" value="pending">
                        <div class="col-md-5">
                            <input type="text" name="search" class="form-control p-2 border-primary" 
                                   placeholder="Cari berdasarkan judul atau nomor..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                        </div>
                        <div class="col-auto">
                            <select name="departmentId" class="form-select p-2 border-primary">
                                <option value="">Semua Prodi</option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?php echo htmlspecialchars($dept['id']); ?>" <?php echo (isset($_GET['departmentId']) && $_GET['departmentId'] == $dept['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($dept['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary p-2 px-3"><i class="fas fa-search me-1"></i> Cari</button>
                        </div>
                        <?php if (!empty($_GET['search']) || !empty($_GET['departmentId'])): ?>
                        <div class="col-auto">
                            <a href="<?php echo SITE_URL; ?>?page=pending" class="btn btn-outline-secondary p-2 px-3"><i class="fas fa-times me-1"></i> Hapus</a>
                        </div>
                        <?php endif; ?>
                    </form>

                    <!-- Documents Table -->
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="bg-primary text-white">
                                <tr>
<th width="2%" class="text-center">No</th>
                                 <th width="8%">Pengaju</th>
                                 <th width="9%">No. HP</th>
                                 <th width="20%">Judul Surat</th>
                                 <th width="10%" class="text-center">Tipe</th>
                                 <th width="10%" class="text-center">Prodi</th>
                                 <th width="10%" class="text-center">Status</th>
                                 <th width="8%" class="text-center">Lampiran</th>
                                 <th width="8%" class="text-center">Tanggal</th>
                                 <th width="10%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $total = count($documents);
                                for ($i = 0; $i < $total; $i++): 
                                    $doc = $documents[$i];
                                    $statusBadgeClass = 'bg-warning';
                                    if ($doc['status'] === STATUS_APPROVED) {
                                        $statusBadgeClass = 'bg-success';
                                    } elseif ($doc['status'] === STATUS_REJECTED) {
                                        $statusBadgeClass = 'bg-danger';
                                    }
                                ?>
                                <tr>
<td><?php echo $i + 1; ?></td>
                                     <td><?php echo htmlspecialchars($doc['applicant_name'] ?? '-'); ?></td>
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
                                     <td class="fw-bold">
                                         <?php echo htmlspecialchars($doc['title']); ?>
                                         <?php if (!empty($doc['department_name'])): ?>
                                             <div class="text-muted small mt-1"><i class="fas fa-building me-1"></i><?php echo htmlspecialchars($doc['department_name']); ?></div>
                                         <?php endif; ?>
                                     </td>
                                    <td><span class="badge bg-secondary"><?php echo ucfirst(str_replace('_', ' ', $doc['type'])); ?></span></td>
                                    <td><?php echo $doc['department_id'] ? htmlspecialchars($doc['department_name']) : '-'; ?></td>
<td><span class="badge <?php echo $statusBadgeClass; ?>"><?php echo ucfirst($doc['status']); ?></span></td>
                                     
                                      <?php 
                                      $attStmt = $pdo->prepare("SELECT file_name, file_path FROM document_attachments WHERE document_id = ?");
                                      $attStmt->execute([$doc['id']]);
                                      $attachments = $attStmt->fetchAll();
                                      ?>
                                      <td>
                                          <?php if (!empty($attachments)): ?>
                                              <div class="d-flex gap-1 justify-content-center">
                                                  <?php foreach ($attachments as $att): 
                                                      $filePath2 = $att['file_path'] ?? '';
                                                      $fileUrl2 = upload_url($filePath2);
                                                  ?>
                                                      <a href="<?php echo htmlspecialchars($fileUrl2); ?>" 
                                                         class="btn btn-sm btn-outline-info py-0 px-2" 
                                                         title="<?php echo htmlspecialchars($att['file_name']); ?>"
                                                         target="_blank">
                                                          <i class="fas fa-file"></i>
                                                      </a>
                                                  <?php endforeach; ?>
                                              </div>
                                          <?php else: ?>
                                              <span class="text-muted">-</span>
                                          <?php endif; ?>
                                      </td>
                                     
<td><?php echo date('d/m/Y', strtotime($doc['created_at'])); ?></td>
                                      <td class="text-center">
                                          <div class="d-flex gap-1 justify-content-center align-items-center">
                                              <?php if (hasPermission('approve_documents')): ?>
                                                  <?php if ($doc['status'] === STATUS_SUBMITTED || $user['role'] === ROLE_ADMIN): ?>
                                                      <button onclick="approve(<?php echo $doc['id']; ?>, '<?php echo htmlspecialchars(addslashes($doc['title'])); ?>')" 
                                                              class="btn btn-sm btn-outline-success py-0 px-2" title="Setujui">
                                                          <i class="fas fa-check"></i>
                                                      </button>
                                                  <?php endif; ?>
                                                  <button onclick="revisi(<?php echo $doc['id']; ?>, '<?php echo htmlspecialchars(addslashes($doc['title'])); ?>')" 
                                                          class="btn btn-sm btn-outline-warning py-0 px-2" title="Revisi">
                                                      <i class="fas fa-rotate-left"></i>
                                                  </button>
                                                  <a href="<?php echo SITE_URL; ?>?page=detail&id=<?php echo $doc['id']; ?>" 
                                                     class="btn btn-sm btn-outline-primary py-0 px-2" title="Detail">
                                                      <i class="fas fa-eye"></i>
                                                  </a>
                                                  <?php if ($doc['status'] !== STATUS_DRAFT && hasPermission('delete_approved_documents')): ?>
                                                      <a href="<?php echo SITE_URL; ?>?page=delete-verified-document&id=<?php echo $doc['id']; ?>&from=pending" 
                                                         class="btn btn-sm btn-outline-danger py-0 px-2" title="Hapus Surat"
                                                         onclick="return confirm('Yakin ingin menghapus surat \'<?php echo htmlspecialchars(addslashes($doc['title'])); ?>\'? Tindakan ini tidak dapat dibatalkan.')">
                                                          <i class="fas fa-trash"></i>
                                                      </a>
                                                  <?php endif; ?>
                                              <?php else: ?>
                                                  <a href="<?php echo SITE_URL; ?>?page=detail&id=<?php echo $doc['id']; ?>" 
                                                     class="btn btn-sm btn-outline-primary py-0 px-2" title="Detail">
                                                      <i class="fas fa-eye"></i>
                                                  </a>
                                              <?php endif; ?>
                                          </div>
                                      </td>
                                </tr>
                             <?php endfor; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>

<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-check-circle text-success me-2"></i>Setujui Surat</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?php echo SITE_URL; ?>">
                <input type="hidden" name="approve-document" value="1">
                <input type="hidden" name="id" id="approveId" value="">
                <div class="modal-body">
                    <p>Yakin ingin menyetujui surat <strong id="approveTitle"></strong>?</p>
                    <div class="mb-3">
                        <label class="form-label">Nomor Surat <span class="text-danger">*</span></label>
                        <input type="text" name="nomor_surat" class="form-control" placeholder="Contoh: LPPM/001/VII/2026" required>
                        <?php if ($lastNomor !== ''): ?>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>Nomor surat terakhir yang digunakan: <strong><?php echo htmlspecialchars($lastNomor); ?></strong>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-check me-1"></i>Setujui</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Revisi Modal -->
<div class="modal fade" id="revisiModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white;">
                <h5 class="modal-title"><i class="fas fa-rotate-left me-2"></i>Revisi Surat</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?php echo SITE_URL; ?>">
                <input type="hidden" name="revisi-document" value="1">
                <input type="hidden" name="id" id="revisiId" value="">
                <div class="modal-body">
                    <p>Kirim pesan revisi untuk surat <strong id="revisiTitle"></strong>:</p>
                    <div class="mb-3">
                        <label class="form-label">Pesan Revisi *</label>
                        <textarea name="comment" class="form-control" rows="4" placeholder="Jelaskan apa yang perlu diperbaiki sesuai SOP dan syarat yang berlaku..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning"><i class="fas fa-rotate-left me-1"></i>Kirim Revisi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function approve(id, title) {
    document.getElementById('approveId').value = id;
    document.getElementById('approveTitle').textContent = title;
    new bootstrap.Modal(document.getElementById('approveModal')).show();
}

function revisi(id, title) {
    document.getElementById('revisiId').value = id;
    document.getElementById('revisiTitle').textContent = title;
    new bootstrap.Modal(document.getElementById('revisiModal')).show();
}
</script>
