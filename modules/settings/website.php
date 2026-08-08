<?php
$page_title = 'Web Setup';
$pdo = db();

$settings = [];
$stmt = $pdo->query("SELECT setting_key, setting_value FROM app_settings");
foreach ($stmt->fetchAll() as $s) {
    $settings[$s['setting_key']] = $s['setting_value'];
}

function g($key, $default = '') { global $settings; return $settings[$key] ?? $default; }

$heroBadge = g('hero_badge', 'Lembaga Penelitian & Pengabdian');
$heroTitle = g('hero_title', 'Inovasi & Riset<br><span>Untuk Negeri</span>');
$heroSubtitle = g('hero_subtitle', 'Berkomitmen mendorong penelitian, pengabdian masyarakat, dan publikasi ilmiah yang berdampak nyata.');
$heroImage = g('hero_image', '');
$heroImageSrc = asset($heroImage);
$heroStat1Num = g('hero_stat1_num', '50+');
$heroStat1Label = g('hero_stat1_label', 'Penelitian');
$heroStat2Num = g('hero_stat2_num', '120+');
$heroStat2Label = g('hero_stat2_label', 'Pengabdian');
$heroStat3Num = g('hero_stat3_num', '200+');
$heroStat3Label = g('hero_stat3_label', 'Publikasi');

$sectionLayananBadge = g('section_layanan_badge', 'Layanan Kami');
$sectionLayananTitle = g('section_layanan_title', 'Fokus <span style="color:#FF6B35;">Bidang</span>');
$sectionTentangBadge = g('section_tentang_badge', 'Tentang Kami');
$sectionKontakBadge = g('section_kontak_badge', 'Hubungi Kami');
$sectionKontakTitle = g('section_kontak_title', 'Mari <span style="color:#FF6B35;">Berkolaborasi</span>');

$aboutTitle = g('about_title', 'LPPM <span style="color:#FF6B35;">Universitas Fajar Deklarasi Karya</span>');
$aboutContent = g('about_content', 'Lembaga Penelitian dan Pengabdian kepada Masyarakat (LPPM) merupakan unit pelaksana teknis di lingkungan Universitas Fajar Deklarasi Karya yang bertugas merencanakan, mengkoordinasikan, memantau, dan mengevaluasi kegiatan penelitian serta pengabdian kepada masyarakat yang dilakukan oleh dosen dan mahasiswa.');

$aboutImage = g('about_image', '');
$aboutImageSrc = asset($aboutImage);
$aboutFeatures = json_decode(g('about_features', '[]'), true) ?: [];

$services = json_decode(g('services', '[]'), true) ?: [];

$contactAddress = g('contact_address', 'Kampus Universitas Fajar Deklarasi Karya, Jl. Pendidikan No. 12, Padang');
$contactEmail = g('contact_email', 'lppm@ufdk.ac.id');
$contactPhone = g('contact_phone', '(0751) 1234567');
$footerDesc = g('footer_desc', 'Mendorong inovasi riset dan pengabdian yang berdampak nyata bagi masyarakat.');
$socialIg = g('social_instagram', '#');
$socialYt = g('social_youtube', '#');
$socialFb = g('social_facebook', '#');

$stat1Num = g('stat1_num', '50+');
$stat1Label = g('stat1_label', 'Penelitian Aktif');
$stat2Num = g('stat2_num', '120+');
$stat2Label = g('stat2_label', 'Pengabdian');
$stat3Num = g('stat3_num', '200+');
$stat3Label = g('stat3_label', 'Publikasi');
$stat4Num = g('stat4_num', '500+');
$stat4Label = g('stat4_label', 'Dosen & Mahasiswa');
ob_start();
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/5.10.9/tinymce.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    tinymce.init({
        selector: '#aboutEditor',
        height: 400,
        menubar: 'file edit view insert format tools table',
        plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table code help wordcount',
        toolbar: 'undo redo | formatselect bold italic underline | alignleft aligncenter alignright | bullist numlist | removeformat | image link | code',
        content_style: 'body { font-family: "Times New Roman", Times, serif; font-size: 12pt; line-height: 1.8; }',
        setup: function (editor) {
            editor.on('change', function () { editor.save(); });
        }
    });
    document.querySelectorAll('#webTabs a[data-bs-toggle="tab"]').forEach(function (link) {
        link.addEventListener('shown.bs.tab', function () {
            document.getElementById('activeTab').value = this.getAttribute('href').replace('#', '');
        });
    });
    var saved = new URLSearchParams(window.location.search).get('tab');
    if (saved) {
        var tabLink = document.querySelector('#webTabs a[href="#' + saved + '"]');
        if (tabLink) { new bootstrap.Tab(tabLink).show(); }
    }
});
</script>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header" style="background: linear-gradient(135deg, #FF6B35 0%, #e85d2a 100%); color: white; border-radius: 10px 10px 0 0 !important;">
                <h5 class="mb-0"><i class="fas fa-globe me-2"></i> Pengaturan Landing Page</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="?page=website-save">
                    <input type="hidden" name="active_tab" id="activeTab" value="">
                    <ul class="nav nav-tabs mb-3" id="webTabs">
                        <li class="nav-item"><a class="nav-link active" href="#hero" data-bs-toggle="tab">Hero</a></li>
                        <li class="nav-item"><a class="nav-link" href="#layanan" data-bs-toggle="tab">Layanan</a></li>
                        <li class="nav-item"><a class="nav-link" href="#tentang" data-bs-toggle="tab">Tentang</a></li>
                        <li class="nav-item"><a class="nav-link" href="#statistik" data-bs-toggle="tab">Statistik</a></li>
                        <li class="nav-item"><a class="nav-link" href="#kontak" data-bs-toggle="tab">Kontak & Footer</a></li>
                    </ul>

                    <div class="tab-content">
                        <!-- Hero -->
                        <div class="tab-pane fade show active" id="hero">
                            <div class="mb-3">
                                <label class="form-label fw-bold small text-muted">Badge Text</label>
                                <input type="text" name="hero_badge" class="form-control" value="<?php echo htmlspecialchars($heroBadge); ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold small text-muted">Judul Hero <small>(HTML diizinkan, gunakan <code>&lt;span&gt;</code> untuk warna)</small></label>
                                <textarea name="hero_title" class="form-control" rows="3"><?php echo htmlspecialchars($heroTitle); ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold small text-muted">Subtitle Hero</label>
                                <textarea name="hero_subtitle" class="form-control" rows="2"><?php echo htmlspecialchars($heroSubtitle); ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold small text-muted">Gambar Hero</label>
                                <div class="input-group">
                                    <input type="text" name="hero_image" class="form-control" placeholder="URL gambar" value="<?php echo htmlspecialchars($heroImage); ?>">
                                    <button type="button" class="btn btn-outline-secondary" onclick="document.getElementById('heroImgUpload').click()">Upload</button>
                                    <input type="file" id="heroImgUpload" style="display:none" accept="image/*">
                                </div>
                                <?php if ($heroImageSrc): ?>
                                <div class="mt-2">
                                    <img src="<?php echo htmlspecialchars($heroImageSrc); ?>" style="max-height: 120px; width: auto; border-radius: 8px;" alt="preview">
                                </div>
                                <?php endif; ?>
                            </div>
                            <p class="small text-muted">Statistik Hero (tampil di samping tombol):</p>
                            <div class="row g-2 mb-2">
                                <div class="col-md-2"><input type="text" name="hero_stat1_num" class="form-control form-control-sm" placeholder="Angka" value="<?php echo htmlspecialchars($heroStat1Num); ?>"></div>
                                <div class="col-md-4"><input type="text" name="hero_stat1_label" class="form-control form-control-sm" placeholder="Label 1" value="<?php echo htmlspecialchars($heroStat1Label); ?>"></div>
                                <div class="col-md-2"><input type="text" name="hero_stat2_num" class="form-control form-control-sm" placeholder="Angka" value="<?php echo htmlspecialchars($heroStat2Num); ?>"></div>
                                <div class="col-md-4"><input type="text" name="hero_stat2_label" class="form-control form-control-sm" placeholder="Label 2" value="<?php echo htmlspecialchars($heroStat2Label); ?>"></div>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-2"><input type="text" name="hero_stat3_num" class="form-control form-control-sm" placeholder="Angka" value="<?php echo htmlspecialchars($heroStat3Num); ?>"></div>
                                <div class="col-md-4"><input type="text" name="hero_stat3_label" class="form-control form-control-sm" placeholder="Label 3" value="<?php echo htmlspecialchars($heroStat3Label); ?>"></div>
                                <div class="col-md-6"></div>
                            </div>
                        </div>

                        <!-- Layanan -->
                        <div class="tab-pane fade" id="layanan">
                            <div class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold small text-muted">Badge</label>
                                    <input type="text" name="section_layanan_badge" class="form-control" value="<?php echo htmlspecialchars($sectionLayananBadge); ?>">
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label fw-bold small text-muted">Judul <small>(HTML diizinkan)</small></label>
                                    <input type="text" name="section_layanan_title" class="form-control" value="<?php echo htmlspecialchars($sectionLayananTitle); ?>">
                                </div>
                            </div>
                            <p class="small text-muted mb-3">Atur layanan yang ditampilkan di landing page. Gunakan <a href="https://fontawesome.com/v6/search" target="_blank">Font Awesome class</a> untuk icon (contoh: <code>fa-microscope</code>).</p>
                            <?php for ($i = 0; $i < 8; $i++): $svc = $services[$i] ?? ['icon' => '', 'title' => '', 'desc' => '']; ?>
                            <div class="row g-2 mb-2 border-bottom pb-2">
                                <div class="col-md-2">
                                    <input type="text" name="service_icon[<?php echo $i; ?>]" class="form-control form-control-sm" placeholder="Icon class" value="<?php echo htmlspecialchars($svc['icon']); ?>">
                                </div>
                                <div class="col-md-3">
                                    <input type="text" name="service_title[<?php echo $i; ?>]" class="form-control form-control-sm" placeholder="Nama" value="<?php echo htmlspecialchars($svc['title']); ?>">
                                </div>
                                <div class="col-md-7">
                                    <input type="text" name="service_desc[<?php echo $i; ?>]" class="form-control form-control-sm" placeholder="Deskripsi" value="<?php echo htmlspecialchars($svc['desc']); ?>">
                                </div>
                            </div>
                            <?php endfor; ?>
                        </div>

                        <!-- Tentang -->
                        <div class="tab-pane fade" id="tentang">
                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold small text-muted">Badge</label>
                                    <input type="text" name="section_tentang_badge" class="form-control" value="<?php echo htmlspecialchars($sectionTentangBadge); ?>">
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label fw-bold small text-muted">Judul Tentang <small>(HTML diizinkan)</small></label>
                                    <input type="text" name="about_title" class="form-control" value="<?php echo htmlspecialchars($aboutTitle); ?>">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold small text-muted">Konten Tentang</label>
                                <textarea name="about_content" id="aboutEditor" rows="10"><?php echo htmlspecialchars($aboutContent); ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold small text-muted">Gambar Tentang</label>
                                <div class="input-group">
                                    <input type="text" name="about_image" class="form-control" placeholder="URL gambar" value="<?php echo htmlspecialchars($aboutImage); ?>">
                                    <button type="button" class="btn btn-outline-secondary" onclick="document.getElementById('aboutImgUpload').click()">Upload</button>
                                    <input type="file" id="aboutImgUpload" style="display:none" accept="image/*">
                                </div>
                                <?php if ($aboutImageSrc): ?>
                                <div class="mt-2">
                                    <img src="<?php echo htmlspecialchars($aboutImageSrc); ?>" style="max-height: 120px; width: auto; border-radius: 8px;" alt="preview">
                                </div>
                                <?php endif; ?>
                            </div>
                            <p class="small text-muted">Fitur / Keunggulan:</p>
                            <?php for ($i = 0; $i < 4; $i++): $f = $aboutFeatures[$i] ?? ['icon' => 'fa-check-circle', 'title' => '', 'desc' => '']; ?>
                            <div class="row g-2 mb-2 border-bottom pb-2">
                                <div class="col-md-2">
                                    <input type="text" name="feature_icon[<?php echo $i; ?>]" class="form-control form-control-sm" placeholder="Icon" value="<?php echo htmlspecialchars($f['icon']); ?>">
                                </div>
                                <div class="col-md-3">
                                    <input type="text" name="feature_title[<?php echo $i; ?>]" class="form-control form-control-sm" placeholder="Judul" value="<?php echo htmlspecialchars($f['title']); ?>">
                                </div>
                                <div class="col-md-7">
                                    <input type="text" name="feature_desc[<?php echo $i; ?>]" class="form-control form-control-sm" placeholder="Deskripsi" value="<?php echo htmlspecialchars($f['desc']); ?>">
                                </div>
                            </div>
                            <?php endfor; ?>
                        </div>

                        <!-- Statistik -->
                        <div class="tab-pane fade" id="statistik">
                            <div class="row g-3">
                                <?php for ($i = 1; $i <= 4; $i++): $num = g("stat{$i}_num"); $label = g("stat{$i}_label"); ?>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold small text-muted">Statistik <?php echo $i; ?></label>
                                    <input type="text" name="stat<?php echo $i; ?>_num" class="form-control mb-1" placeholder="Angka" value="<?php echo htmlspecialchars($num); ?>">
                                    <input type="text" name="stat<?php echo $i; ?>_label" class="form-control" placeholder="Label" value="<?php echo htmlspecialchars($label); ?>">
                                </div>
                                <?php endfor; ?>
                            </div>
                        </div>

                        <!-- Kontak & Footer -->
                        <div class="tab-pane fade" id="kontak">
                            <div class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold small text-muted">Badge</label>
                                    <input type="text" name="section_kontak_badge" class="form-control" value="<?php echo htmlspecialchars($sectionKontakBadge); ?>">
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label fw-bold small text-muted">Judul <small>(HTML diizinkan)</small></label>
                                    <input type="text" name="section_kontak_title" class="form-control" value="<?php echo htmlspecialchars($sectionKontakTitle); ?>">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold small text-muted">Alamat</label>
                                <textarea name="contact_address" class="form-control" rows="2"><?php echo htmlspecialchars($contactAddress); ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold small text-muted">Email</label>
                                <input type="text" name="contact_email" class="form-control" value="<?php echo htmlspecialchars($contactEmail); ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold small text-muted">No. Telepon</label>
                                <input type="text" name="contact_phone" class="form-control" value="<?php echo htmlspecialchars($contactPhone); ?>">
                            </div>
                            <hr>
                            <h6 class="fw-bold mb-3">Footer</h6>
                            <div class="mb-3">
                                <label class="form-label fw-bold small text-muted">Deskripsi Footer</label>
                                <textarea name="footer_desc" class="form-control" rows="2"><?php echo htmlspecialchars($footerDesc); ?></textarea>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold small text-muted">Instagram <small>(URL)</small></label>
                                    <input type="text" name="social_instagram" class="form-control" placeholder="https://instagram.com/..." value="<?php echo htmlspecialchars($socialIg); ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold small text-muted">YouTube <small>(URL)</small></label>
                                    <input type="text" name="social_youtube" class="form-control" placeholder="https://youtube.com/..." value="<?php echo htmlspecialchars($socialYt); ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold small text-muted">Facebook <small>(URL)</small></label>
                                    <input type="text" name="social_facebook" class="form-control" placeholder="https://facebook.com/..." value="<?php echo htmlspecialchars($socialFb); ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary rounded-pill px-4"><i class="fas fa-save me-2"></i> Simpan</button>
                        <a href="<?php echo BASE_URL; ?>/" target="_blank" class="btn btn-outline-secondary rounded-pill px-4">Lihat Landing Page</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
function setupUpload(inputId, fieldName) {
    document.getElementById(inputId)?.addEventListener('change', function() {
        var file = this.files[0];
        if (!file) return;
        var formData = new FormData();
        formData.append('file', file);
        formData.append('field', fieldName);
        var xhr = new XMLHttpRequest();
        xhr.open('POST', '<?php echo SITE_URL; ?>?page=upload-image');
        xhr.onload = function() {
            if (xhr.status === 200) {
                var res = JSON.parse(xhr.responseText);
                if (res.url) {
                    var input = document.querySelector('input[name="' + fieldName + '"]');
                    input.value = res.url;
                    var preview = input.closest('.mb-3').querySelector('.mt-2 img');
                    if (preview) {
                        preview.src = res.url;
                    } else {
                        var d = document.createElement('div');
                        d.className = 'mt-2';
                        d.innerHTML = '<img src="' + res.url.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;') + '" style="max-height:120px;width:auto;border-radius:8px;" alt="preview">';
                        input.closest('.mb-3').appendChild(d);
                    }
                } else {
                    alert('Gagal upload: ' + (res.error || 'unknown error'));
                }
            } else {
                alert('Gagal upload');
            }
        };
        xhr.send(formData);
    });
}
setupUpload('heroImgUpload', 'hero_image');
setupUpload('aboutImgUpload', 'about_image');
</script>
<?php
$content = ob_get_clean(); require __DIR__ . '/../layouts/master.php'; ?>
