<?php
$page_title = 'Syarat Pengurusan Surat';
$pdo = db();
$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM document_types WHERE id = ?");
$stmt->execute([$id]);
$type = $stmt->fetch();
if (!$type) { $_SESSION['error'] = "Jenis surat tidak ditemukan"; header("Location: ?page=types"); exit; }

$hasSort = false;
try { $pdo->query("SELECT sort_order FROM type_requirements LIMIT 1"); $hasSort = true; } catch (Exception $e) {}
$orderBy = $hasSort ? "ORDER BY sort_order ASC, id ASC" : "ORDER BY id ASC";
$reqs = $pdo->prepare("SELECT * FROM type_requirements WHERE type_id = ? $orderBy");
$reqs->execute([$id]);
$requirements = $reqs->fetchAll();
ob_start();
?>
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow border-0 rounded-4">
            <div class="card-header d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #FF6B35 0%, #e85d2a 100%); color: white; border-radius: 10px 10px 0 0 !important;">
                <h5 class="mb-0"><i class="fas fa-list-check me-2"></i> Syarat: <?php echo htmlspecialchars($type['name']); ?></h5>
                <a href="?page=types" class="btn btn-light btn-sm rounded-pill px-3">Kembali</a>
            </div>
            <div class="card-body p-4">
                <p class="text-muted small mb-4">Kode: <code><?php echo htmlspecialchars($type['code']); ?></code></p>

                <form method="POST" action="?page=types-requirements-save">
                    <input type="hidden" name="type_id" value="<?php echo $type['id']; ?>">
                    <div class="mb-3">
                        <div class="input-group">
<input type="text" name="description" class="form-control form-control-lg" placeholder="Tulis syarat baru..." required>
                            <select name="input_type" id="addInputType" class="form-select form-select-lg" style="max-width:150px;">
                                <option value="file">Upload File</option>
                                <option value="text">Input Teks</option>
                                <option value="list">List</option>
                            </select>
                            <div class="input-group-text p-0 border-0 bg-transparent">
                                <label class="btn btn-outline-secondary rounded-0 h-100 d-flex align-items-center mb-0">
                                    <input type="checkbox" name="is_required" value="1" checked> Wajib
                                </label>
                            </div>
                            <button type="submit" class="btn btn-primary rounded-end-3 px-4"><i class="fas fa-plus me-1"></i> Tambah</button>
                        </div>
                            <div class="row mt-2">
                            <div class="col-md-3" id="addMaxSizeCol">
                                <input type="number" name="max_size" class="form-control" placeholder="Ukuran maks KB" value="2048" min="0">
                            </div>
                            <div class="col-md-3 d-flex align-items-center">
                                <small class="text-muted">Ukuran file (0 = tanpa file)</small>
                            </div>
                            <div class="col-md-6" id="addConfigRow" style="display:none;">
                                <textarea name="config" class="form-control" rows="2" placeholder="Nama Anggota&#10;NIM&#10;Jabatan"></textarea>
                                <small class="text-muted">Kolom daftar (satu kolom per baris). Kolom pertama = item utama.</small>
                            </div>
                        </div>
                    </div>
                </form>

                <?php if (count($requirements)): ?>
                <div class="list-group list-group-flush mt-3">
                    <?php foreach ($requirements as $r): ?>
                    <div class="list-group-item d-flex justify-content-between align-items-center py-3 px-0 border-start-0 border-end-0">
                        <div>
                            <i class="fas fa-circle-check text-<?php echo $r['is_required'] ? 'danger' : 'secondary'; ?> me-2"></i>
                            <span><?php echo htmlspecialchars($r['description']); ?></span>
                            <span class="badge bg-<?php echo ($r['input_type'] ?? 'file') === 'text' ? 'primary' : (($r['input_type'] ?? 'file') === 'list' ? 'info' : 'secondary'); ?> ms-1 small">
                                <?php
                                    $it = $r['input_type'] ?? 'file';
                                    if ($it === 'text') echo '<i class="fas fa-font me-1"></i>Teks';
                                    elseif ($it === 'list') echo '<i class="fas fa-list-ol me-1"></i>List';
                                    else echo '<i class="fas fa-upload me-1"></i>File';
                                ?>
                            </span>
                            <?php if ($r['is_required']): ?>
                                <span class="badge bg-danger ms-1 small">Wajib</span>
                            <?php else: ?>
                                <span class="badge bg-secondary ms-1 small">Opsional</span>
                            <?php endif; ?>
                            <?php if (($r['input_type'] ?? 'file') === 'file' && $r['max_size'] > 0): ?>
                                <span class="badge bg-info ms-1 small">Maks <?php echo $r['max_size'] >= 1024 ? round($r['max_size'] / 1024, 1) . ' MB' : $r['max_size'] . ' KB'; ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="d-flex gap-1">
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-circle" 
                                    title="Edit" 
                                    data-bs-toggle="modal" data-bs-target="#editModal"
                                    data-id="<?php echo $r['id']; ?>"
                                    data-desc="<?php echo htmlspecialchars($r['description'], ENT_QUOTES); ?>"
                                    data-required="<?php echo $r['is_required']; ?>"
                                    data-max="<?php echo $r['max_size']; ?>"
                                    data-input_type="<?php echo $r['input_type'] ?? 'file'; ?>"
                                    data-config="<?php echo htmlspecialchars($r['config'] ?? '', ENT_QUOTES); ?>">
                                <i class="fas fa-pen"></i>
                            </button>
                            <a href="?page=types-requirements-save&delete=<?php echo $r['id']; ?>&type_id=<?php echo $type['id']; ?>" 
                               class="btn btn-sm btn-outline-danger rounded-circle" 
                               onclick="return confirm('Hapus syarat ini?')" title="Hapus">
                                <i class="fas fa-times"></i>
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="text-center text-muted py-5">
                    <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                    Belum ada syarat untuk jenis surat ini. Silakan tambah syarat di atas.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="editModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="?page=types-requirements-save">
        <div class="modal-header" style="background: linear-gradient(135deg, #FF6B35 0%, #e85d2a 100%); color: white;">
          <h5 class="modal-title"><i class="fas fa-pen me-2"></i> Edit Syarat</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="edit_id" id="editId">
          <input type="hidden" name="type_id" value="<?php echo $type['id']; ?>">
          <div class="mb-3">
            <label class="form-label fw-bold small text-muted">Deskripsi Syarat</label>
            <input type="text" name="description" id="editDesc" class="form-control form-control-lg" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold small text-muted">Tipe Input</label>
            <select name="input_type" id="editInputType" class="form-select">
                <option value="file">Upload File</option>
                <option value="text">Input Teks</option>
                <option value="list">List</option>
            </select>
          </div>

          <div class="mb-3" id="editMaxSizeRow">
            <label class="form-label fw-bold small text-muted">Ukuran Maksimal File (KB)</label>
            <input type="number" name="max_size" id="editMaxSize" class="form-control" min="0" value="2048">
            <small class="text-muted">Isi 0 jika tidak perlu upload file.</small>
          </div>
          <div class="mb-3" id="editConfigRow" style="display:none;">
            <label class="form-label fw-bold small text-muted">Kolom Daftar (satu kolom per baris)</label>
            <textarea name="config" id="editConfig" class="form-control" rows="3" placeholder="Nama Anggota&#10;NIM&#10;Jabatan"></textarea>
            <small class="text-muted">Kolom pertama dijadikan item utama.</small>
          </div>
          <div class="form-check">
            <input type="checkbox" name="is_required" value="1" id="editRequired" class="form-check-input">
            <label class="form-check-label" for="editRequired">Wajib</label>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary rounded-pill px-4"><i class="fas fa-save me-1"></i> Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function toggleEditFields(type) {
    document.getElementById('editMaxSizeRow').style.display = type === 'file' ? '' : 'none';
    document.getElementById('editConfigRow').style.display = type === 'list' ? '' : 'none';
}

function configToLines(json) {
    if (!json) return '';
    try {
        var arr = JSON.parse(json);
        if (!Array.isArray(arr)) return '';
        return arr.map(function(c) { return c.label; }).join('\n');
    } catch (e) { return ''; }
}

function toggleAddFields(type) {
    document.getElementById('addMaxSizeCol').style.display = type === 'file' ? '' : 'none';
    document.getElementById('addConfigRow').style.display = type === 'list' ? '' : 'none';
}

document.getElementById('addInputType').addEventListener('change', function() {
    toggleAddFields(this.value);
});

document.getElementById('editModal').addEventListener('show.bs.modal', function (e) {
    var btn = e.relatedTarget;
    document.getElementById('editId').value = btn.dataset.id;
    document.getElementById('editDesc').value = btn.dataset.desc;
    document.getElementById('editRequired').checked = btn.dataset.required === '1';
    document.getElementById('editMaxSize').value = btn.dataset.max || '0';
    document.getElementById('editInputType').value = btn.dataset.input_type || 'file';
    document.getElementById('editConfig').value = configToLines(btn.dataset.config || '');
    toggleEditFields(document.getElementById('editInputType').value);
});

document.getElementById('editInputType').addEventListener('change', function() {
    toggleEditFields(this.value);
});
</script>
<?php
$content = ob_get_clean(); require __DIR__ . '/../layouts/master.php';
