<?php
$page_title = 'Informasi LPPM';
$pdo = db();
$action = $_GET['action'] ?? 'list';
$editInfo = null;

if ($action === 'edit' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM informasi_lpm WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $editInfo = $stmt->fetch();
}

$perPageOptions = [10, 25, 50, 100, 'all'];
$perPageReq = $_GET['per_page'] ?? 10;
if (!in_array($perPageReq, $perPageOptions)) $perPageReq = 10;
$perPage = $perPageReq === 'all' ? PHP_INT_MAX : (int)$perPageReq;
$page = max(1, (int)($_GET['hal'] ?? 1));
$total = (int)$pdo->query("SELECT COUNT(*) FROM informasi_lpm")->fetchColumn();
$totalPages = max(1, (int)ceil($total / $perPage));
if ($page > $totalPages) $page = $totalPages;
$offset = ($page - 1) * $perPage;
if ($perPageReq === 'all') {
    $list = $pdo->query("SELECT * FROM informasi_lpm ORDER BY created_at DESC, id DESC")->fetchAll();
} else {
    $stmt = $pdo->prepare("SELECT * FROM informasi_lpm ORDER BY created_at DESC, id DESC LIMIT ? OFFSET ?");
    $stmt->bindValue(1, $perPage, PDO::PARAM_INT);
    $stmt->bindValue(2, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $list = $stmt->fetchAll();
}
ob_start();
?>
<script src="<?php echo APP_ROOT_URL; ?>/assets/lib/tinymce/tinymce.min.js?v=5.10.9"></script>
<script>
function initInfoEditor() {
    if (typeof tinymce !== 'undefined') {
        tinymce.init({
            selector: '#infoEditor',
            height: 400,
            menubar: 'file edit view insert format tools table',
            plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table code help wordcount',
            toolbar: 'undo redo | formatselect fontselect fontsizeselect | bold italic underline strikethrough subscript superscript | forecolor backcolor | alignleft aligncenter alignright | bullist numlist | table | image link | removeformat | code fullscreen',
            font_formats: 'Arial=arial,helvetica,sans-serif; Courier New=courier new,courier,monospace; Georgia=georgia,times new roman,serif; Tahoma=tahoma,geneva,sans-serif; Times New Roman=times new roman,times,serif; Verdana=verdana,geneva,sans-serif; Calibri=calibri,sans-serif; Cambria=cambria,serif; Garamond=garamond,serif; Comic Sans MS=comic sans ms,sans-serif; Trebuchet MS=trebuchet ms,geneva,sans-serif',
            fontsize_formats: '8 9 10 11 12 13 14 16 18 20 22 24 26 28 36 42 48 60 72',
            content_style: 'body { font-family: "Inter", sans-serif; font-size: 14px; line-height: 1.8; }',
            setup: function (editor) {
                editor.on('change', function () { editor.save(); });
            }
        });
    }
}
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initInfoEditor);
} else {
    initInfoEditor();
}
</script>

<?php if ($action === 'edit' || $action === 'create'): ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header" style="background: linear-gradient(135deg, #FF6B35 0%, #e85d2a 100%); color: white; border-radius: 10px 10px 0 0 !important;">
                <h5 class="mb-0"><i class="fas fa-bullhorn me-2"></i> <?php echo $action === 'create' ? 'Tambah' : 'Edit'; ?> Informasi</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="?page=informasi-save" enctype="multipart/form-data">
                    <?php if ($editInfo): ?><input type="hidden" name="id" value="<?php echo $editInfo['id']; ?>"><?php endif; ?>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Judul *</label>
                        <input type="text" name="judul" class="form-control" required value="<?php echo htmlspecialchars($editInfo['judul'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Informasi</label>
                        <textarea name="konten" id="infoEditor" class="form-control" rows="8"><?php echo htmlspecialchars($editInfo['konten'] ?? ''); ?></textarea>
                        <small class="text-muted">Isi informasi yang akan ditampilkan di landing page.</small>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Gambar / Thumbnail</label>
                        <input type="file" name="thumbnail" class="form-control" accept=".jpg,.jpeg,.png,.gif,.webp">
                        <?php if (!empty($editInfo['thumbnail'])): ?>
                        <div class="mt-2 d-flex align-items-center gap-3">
                            <img src="<?php echo htmlspecialchars(asset($editInfo['thumbnail'])); ?>" style="height: 60px; width: auto; object-fit: cover; border-radius: 8px;" alt="thumbnail">
                            <div>
                                <small class="text-muted d-block">Thumbnail saat ini</small>
                                <label class="text-danger small mb-0">
                                    <input type="checkbox" name="thumbnail_remove" value="1"> Hapus thumbnail
                                </label>
                            </div>
                        </div>
                        <?php endif; ?>
                        <small class="text-muted">Format: jpg, jpeg, png, gif, webp. Disimpan di uploads/informasi/.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Lampiran (PDF)</label>
                        <input type="file" name="lampiran" class="form-control" accept=".pdf">
                        <?php if (!empty($editInfo['lampiran'])): ?>
                        <div class="mt-2 d-flex align-items-center gap-3">
                            <a href="<?php echo htmlspecialchars(asset($editInfo['lampiran'])); ?>" target="_blank"><i class="fas fa-file-pdf me-1 text-danger"></i> Lampiran saat ini</a>
                            <label class="text-danger small mb-0">
                                <input type="checkbox" name="lampiran_remove" value="1"> Hapus lampiran
                            </label>
                        </div>
                        <?php endif; ?>
                        <small class="text-muted">Hanya file PDF. Disimpan di uploads/informasi/.</small>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary rounded-pill px-4"><i class="fas fa-save me-2"></i> Simpan</button>
                        <a href="?page=informasi" class="btn btn-outline-secondary rounded-pill px-4">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #FF6B35 0%, #e85d2a 100%); color: white; border-radius: 10px 10px 0 0 !important;">
        <h5 class="mb-0"><i class="fas fa-bullhorn me-2"></i> Daftar Informasi LPPM</h5>
        <div class="d-flex align-items-center gap-2">
            <form method="GET" action="?page=informasi" class="d-flex align-items-center gap-2 mb-0">
                <input type="hidden" name="page" value="informasi">
                <span class="small text-white-50">Tampilkan</span>
                <select name="per_page" class="form-select form-select-sm" style="width:auto; color:#333;" onchange="this.form.submit()">
                    <?php foreach ($perPageOptions as $opt): ?>
                    <option value="<?php echo $opt; ?>" <?php echo (string)$perPageReq === (string)$opt ? 'selected' : ''; ?>><?php echo $opt === 'all' ? 'Semua' : $opt; ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
            <a href="?page=informasi&action=create" class="btn btn-light btn-sm rounded-pill px-3"><i class="fas fa-plus me-1"></i> Tambah Informasi</a>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-primary text-white">
                    <tr>
                        <th class="ps-3">#</th>
                        <th>Thumbnail</th>
                        <th>Judul</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th class="pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($list as $i => $info): ?>
                    <tr>
                        <td class="ps-3"><?php echo $offset + $i + 1; ?></td>
                        <td>
                            <?php if (!empty($info['thumbnail'])): ?>
                            <img src="<?php echo htmlspecialchars(asset($info['thumbnail'])); ?>" style="width: 60px; height: 42px; object-fit: cover; border-radius: 6px;" alt="th">
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="fw-bold"><?php echo htmlspecialchars($info['judul']); ?></td>
                        <td><?php echo date('d M Y', strtotime($info['created_at'])); ?></td>
                        <td>
                            <a href="?page=informasi-toggle&id=<?php echo $info['id']; ?>" title="Klik untuk toggle publish">
                            <?php if ($info['is_active']): ?>
                                <span class="badge bg-success">Aktif</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Nonaktif</span>
                            <?php endif; ?>
                            </a>
                        </td>
                        <td class="pe-3">
                            <a href="?page=informasi&action=edit&id=<?php echo $info['id']; ?>" class="btn btn-sm btn-outline-primary rounded-circle" title="Edit"><i class="fas fa-edit"></i></a>
                            <a href="?page=informasi-delete&id=<?php echo $info['id']; ?>" class="btn btn-sm btn-outline-danger rounded-circle" onclick="return confirm('Hapus informasi ini?')" title="Hapus"><i class="fas fa-trash"></i></a>
                            <a href="<?php echo BASE_URL; ?>/informasi.php?id=<?php echo $info['id']; ?>" target="_blank" class="btn btn-sm btn-outline-secondary rounded-circle" title="Lihat"><i class="fas fa-eye"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($list)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">Belum ada informasi.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if ($total > 0): ?>
        <div class="d-flex align-items-center justify-content-between px-3 py-2 border-top">
            <?php if ($perPageReq === 'all'): ?>
            <small class="text-muted">Menampilkan 1 - <?php echo $total; ?> dari <?php echo $total; ?> data</small>
            <span></span>
            <?php else: ?>
            <small class="text-muted">Menampilkan <?php echo $offset + 1; ?> - <?php echo min($offset + $perPage, $total); ?> dari <?php echo $total; ?> data</small>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=informasi&per_page=<?php echo $perPageReq; ?>&hal=<?php echo $page - 1; ?>"><i class="fas fa-chevron-left"></i></a>
                    </li>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=informasi&per_page=<?php echo $perPageReq; ?>&hal=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                    <?php endfor; ?>
                    <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=informasi&per_page=<?php echo $perPageReq; ?>&hal=<?php echo $page + 1; ?>"><i class="fas fa-chevron-right"></i></a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endif;
$content = ob_get_clean(); require __DIR__ . '/../layouts/master.php'; ?>