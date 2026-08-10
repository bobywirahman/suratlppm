<?php
$page_title = 'Set Template Surat';
$pdo = db();
$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM document_types WHERE id = ?");
$stmt->execute([$id]);
$type = $stmt->fetch();
if (!$type) { $_SESSION['error'] = "Jenis surat tidak ditemukan"; header("Location: ?page=types"); exit; }

$siteUrl = rtrim((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]", '/');
ob_start();
?>
<script src="<?php echo APP_ROOT_URL; ?>/assets/lib/tinymce/tinymce.min.js?v=5.10.9"></script>
<script>
function initTemplateEditor() {
    if (typeof tinymce !== 'undefined') {
        tinymce.init({
            selector: '#templateEditor',
            height: 600,
            menubar: 'file edit view insert format tools table help',
            plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table code help wordcount',
            toolbar: [
                'undo redo | formatselect fontselect fontsizeselect | bold italic underline strikethrough subscript superscript | forecolor backcolor',
                'alignleft aligncenter alignright alignjustify | lineheight indent outdent | bullist numlist',
                'table image link | code fullscreen'
            ],
            font_formats: 'Arial=arial,helvetica,sans-serif; Courier New=courier new,courier,monospace; Georgia=georgia,times new roman,serif; Tahoma=tahoma,geneva,sans-serif; Times New Roman=times new roman,times,serif; Verdana=verdana,geneva,sans-serif; Calibri=calibri,sans-serif; Cambria=cambria,serif; Garamond=garamond,serif; Comic Sans MS=comic sans ms,sans-serif; Trebuchet MS=trebuchet ms,geneva,sans-serif',
            fontsize_formats: '8pt 9pt 10pt 11pt 12pt 13pt 14pt 16pt 18pt 20pt 22pt 24pt 26pt 28pt 36pt 42pt 48pt 60pt 72pt',
            image_advtab: true,
            image_caption: true,
            object_resizing: true,
            image_dimensions: true,
            table_toolbar: 'tableprops tabledelete | tableinsertrowbefore tableinsertrowafter tabledeleterow | tableinsertcolbefore tableinsertcolafter tabledeletecol',
            content_style: [
                'img { max-width: 100%; height: auto; }',
                'body { font-family: "Times New Roman", serif; font-size: 12pt; }',
                'table { border-collapse: collapse; width: 100%; }',
                'table td, table th { border: 1px solid #999; padding: 6px 10px; }'
            ].join('\n'),
            images_upload_url: '<?php echo APP_ROOT_URL; ?>/uploads/tiny_upload.php',
            automatic_uploads: true,
            file_picker_types: 'image',
            images_upload_handler: function (blobInfo, success, failure) {
                var xhr, formData;
                xhr = new XMLHttpRequest();
                xhr.withCredentials = false;
                xhr.open('POST', '<?php echo APP_ROOT_URL; ?>/uploads/tiny_upload.php');
                xhr.onload = function () {
                    var json;
                    if (xhr.status !== 200) { failure('HTTP Error: ' + xhr.status); return; }
                    json = JSON.parse(xhr.responseText);
                    if (!json || typeof json.location != 'string') { failure('Invalid JSON: ' + xhr.responseText); return; }
                    success(json.location);
                };
                formData = new FormData();
                formData.append('file', blobInfo.blob(), blobInfo.filename());
                xhr.send(formData);
            },
            setup: function (editor) {
                editor.on('change', function () {
                    sessionStorage.setItem('tinymce_template_' + <?php echo $id; ?>, editor.getContent());
                });
                editor.on('submit', function () {
                    sessionStorage.removeItem('tinymce_template_' + <?php echo $id; ?>);
                });
            }
        });
    }
}
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTemplateEditor);
} else {
    initTemplateEditor();
}
</script>
<div class="row justify-content-center">
    <div class="col-md-12">
        <div class="card shadow border-0 rounded-4">
            <div class="card-header d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #FF6B35 0%, #e85d2a 100%); color: white; border-radius: 10px 10px 0 0 !important;">
                <h5 class="mb-0"><i class="fas fa-file-code me-2"></i> Template: <?php echo htmlspecialchars($type['name']); ?></h5>
                <a href="?page=types" class="btn btn-light btn-sm rounded-pill px-3">Kembali</a>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="?page=types-template-save">
                    <input type="hidden" name="id" value="<?php echo $type['id']; ?>">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Kode / Nama</label>
                        <p class="mb-0"><code><?php echo htmlspecialchars($type['code']); ?></code> - <?php echo htmlspecialchars($type['name']); ?></p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Template Surat <small class="text-danger">*</small></label>
                        <?php
                        $reqStmt = $pdo->prepare("SELECT description, input_type FROM type_requirements WHERE type_id = ? AND input_type IN ('text','list') ORDER BY sort_order ASC, id ASC");
                        $reqStmt->execute([$id]);
                        $textReqs = $reqStmt->fetchAll();
                        ?>
                        <div class="alert alert-info p-3 mb-2">
                            <strong class="small"><i class="fas fa-info-circle me-1"></i> Placeholder yang tersedia:</strong>
                            <div class="row small mt-2">
                                <div class="col-md-6">
                                    <code>{nama}</code> Nama pemohon<br>
                                    <code>{nim}</code> NIM pemohon<br>
                                    <code>{prodi}</code> Program studi<br>
                                    <code>{fakultas}</code> Fakultas<br>
                                    <code>{alamat}</code> Alamat pemohon<br>
                                    <code>{no_hp}</code> No. HP pemohon<br>
                                    <code>{email}</code> Email pemohon
                                </div>
                                <div class="col-md-6">
                                    <code>{tahun_ajaran}</code> Tahun ajaran<br>
                                    <code>{judul}</code> Judul surat<br>
                                    <code>{tipe_surat}</code> Jenis surat<br>
                                    <code>{deskripsi}</code> Deskripsi surat<br>
                                    <code>{tanggal}</code> Tanggal hari ini<br>
                                    <code>{tanggal_approval}</code> Tanggal persetujuan surat<br>
                                    <code>{no_surat}</code> Nomor surat
                                </div>
                            </div>
                            <?php if ($textReqs): ?>
                            <hr class="my-2">
                            <strong class="small">Placeholder dari syarat input teks / list:</strong>
                            <div class="small mt-1">
                                <?php foreach ($textReqs as $r): ?>
                                <code>{<?php echo strtolower(str_replace(' ', '_', preg_replace('/[^a-zA-Z0-9 ]/', '', $r['description']))); ?>}</code>
                                <?php echo htmlspecialchars($r['description']); ?>
                                <?php if (($r['input_type'] ?? '') === 'list'): ?>
                                    <span class="badge bg-info ms-1">List (cetak tabel)</span>
                                <?php else: ?>
                                    <span class="badge bg-primary ms-1">Teks</span>
                                <?php endif; ?>
                                <br>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <textarea name="template" id="templateEditor"><?php echo htmlspecialchars($type['template'] ?? ''); ?></textarea>
                        <div class="small text-muted mt-2">
                            <i class="fas fa-arrows-alt me-1"></i> Gambar dapat diubah ukurannya dengan cara <strong>drag sudut gambar</strong> di editor, atau atur Lebar/Tinggi di dialog gambar (klik 2x gambar). Ukuran yang diatur akan dicetak sesuai di surat.
                            <br><i class="fas fa-file-image me-1"></i> Untuk gambar kop surat yang ingin memenuhi lebar halaman, pilih class <code>Penuh lebar (kop surat)</code> saat insert/klik gambar.
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-info rounded-pill px-4" onclick="reviewTemplate()"><i class="fas fa-eye me-2"></i> Review Surat</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-5"><i class="fas fa-save me-2"></i> Simpan Template</button>
                        <a href="?page=types" class="btn btn-outline-secondary rounded-pill px-4">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
#reviewContent img { max-width: 100%; height: auto; }
</style>
<!-- Modal Review Surat -->
<div class="modal fade" id="reviewModal" tabindex="-1" aria-labelledby="reviewModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg, #FF6B35 0%, #e85d2a 100%); color: white;">
        <h5 class="modal-title" id="reviewModalLabel"><i class="fas fa-file-alt me-2"></i> Review Template Surat</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4" id="reviewContent" style="font-family: 'Times New Roman', Times, serif; font-size: 14pt; line-height: 1.6;">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal"><i class="fas fa-times me-2"></i> Tutup</button>
      </div>
    </div>
  </div>
</div>

<script>
function reviewTemplate() {
    var editor = tinymce.get('templateEditor');
    if (!editor) return;
    var content = editor.getContent();
    if (!content.trim()) {
        alert('Template masih kosong. Silakan isi template terlebih dahulu.');
        return;
    }
    var sample = {
        '{nama}': '<strong>Muhammad Al-Fatih</strong>',
        '{nim}': '2024081001',
        '{prodi}': '<strong>S1 Teknik Informatika</strong>',
        '{fakultas}': '<strong>Fakultas Teknik dan Ilmu Komputer</strong>',
        '{alamat}': 'Jl. Merdeka No. 123, Padang',
        '{no_hp}': '0812-3456-7890',
        '{email}': 'alfatih@student.ufdk.ac.id',
        '{tahun_ajaran}': '<strong>2025/2026</strong>',
        '{judul}': '<strong>Pengajuan Proposal Penelitian AI untuk Deteksi Dini Bencana</strong>',
        '{tipe_surat}': '<strong>Surat Permohonan Penelitian</strong>',
        '{deskripsi}': 'Dengan ini mengajukan permohonan penelitian sebagaimana judul di atas.',
        '{tanggal}': '29 Juli 2026',
        '{tanggal_approval}': '30 Juli 2026',
        '{no_surat}': 'LPPM/2026/VII/001'
    };
    <?php foreach ($textReqs as $r):
        $key = strtolower(str_replace(' ', '_', preg_replace('/[^a-zA-Z0-9 ]/', '', $r['description'])));
    ?>
    sample['{<?php echo $key; ?>}'] = '<em>[Isian: <?php echo htmlspecialchars($r['description'], ENT_QUOTES); ?>]</em>';
    <?php endforeach; ?>
    for (var key in sample) {
        content = content.split(key).join(sample[key]);
    }
    document.getElementById('reviewContent').innerHTML = content;
    var modal = new bootstrap.Modal(document.getElementById('reviewModal'));
    modal.show();
}
</script>
<?php
$content = ob_get_clean(); require __DIR__ . '/../layouts/master.php';
