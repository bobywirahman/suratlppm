<?php
$page_title = $editDoc ? 'Edit Surat' : 'Form Pengajuan Surat';
$editTextResponses = [];
if ($editDoc) {
    $txtStmt = $pdo->prepare("SELECT requirement_id, text_value FROM document_attachments WHERE document_id = ? AND text_value IS NOT NULL");
    $txtStmt->execute([$editDoc['id']]);
    foreach ($txtStmt->fetchAll() as $tr) {
        $editTextResponses[$tr['requirement_id']] = $tr['text_value'];
    }
}

// Data pemohon (hanya view) — ambil dari data surat (applicant), fallback ke user saat ini
$person = [];
if ($editDoc) {
    $personStmt = $pdo->prepare("SELECT u.full_name, u.nim, u.no_hp, de.name AS dept_name, f.name AS faculty_name
        FROM documents d
        JOIN users u ON d.applicant_id = u.id
        JOIN departments de ON d.department_id = de.id
        LEFT JOIN faculties f ON de.faculty_id = f.id
        WHERE d.id = ?");
    $personStmt->execute([$editDoc['id']]);
    $person = $personStmt->fetch() ?: [];
}
if (empty($person)) {
    $deptStmt = $pdo->prepare("SELECT d.name AS dept_name, f.name AS faculty_name FROM departments d LEFT JOIN faculties f ON d.faculty_id = f.id WHERE d.id = ?");
    $deptStmt->execute([$user['department_id']]);
    $deptData = $deptStmt->fetch() ?: [];
    $person = [
        'full_name' => $user['full_name'] ?? '-',
        'nim'       => $user['nim'] ?? '-',
        'no_hp'     => $user['no_hp'] ?? '-',
        'dept_name' => $deptData['dept_name'] ?? '-',
        'faculty_name' => $deptData['faculty_name'] ?? '-',
    ];
}
$personDepartmentId = $editDoc['department_id'] ?? $user['department_id'];

$revisiComment = '';
if ($editDoc && $editDoc['status'] === STATUS_REVISI) {
    $rcStmt = $pdo->prepare("SELECT revisi_comment FROM document_revisions WHERE document_id = ? AND revisi_comment IS NOT NULL AND revisi_comment <> '' ORDER BY id DESC LIMIT 1");
    $rcStmt->execute([$editDoc['id']]);
    $revisiComment = (string)($rcStmt->fetchColumn() ?: '');
}
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i> <?php echo $editDoc ? 'Edit Surat' : 'Pengajuan Surat Baru'; ?></h5>
        <a href="?page=list" class="btn btn-sm btn-light">Kembali</a>
    </div>

    <form method="POST" action="?page=save" enctype="multipart/form-data" id="suratForm">
        <div class="card-body">
            <?php if ($editDoc && ($editDoc['status'] === STATUS_REJECTED || $editDoc['status'] === STATUS_REVISI)): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i> Surat ini perlu diperbaiki. Silakan lengkapi perubahan yang diperlukan dan ajukan kembali.
                    <?php if ($revisiComment !== ''): ?>
                        <div class="mt-2 small fst-italic">
                            <i class="fas fa-comment me-1"></i>Catatan revisi: <?php echo htmlspecialchars($revisiComment); ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <?php if ($editDoc): ?>
                <input type="hidden" name="edit_id" value="<?php echo $editDoc['id']; ?>">
            <?php endif; ?>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Nama Lengkap</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($person['full_name']); ?>" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label">NIM</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($person['nim'] ?? '-'); ?>" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label">No. HP</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($person['no_hp'] ?? '-'); ?>" readonly>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Fakultas</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($person['faculty_name'] ?? '-'); ?>" readonly>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Program Studi *</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($person['dept_name'] ?? '-'); ?>" readonly>
                    <input type="hidden" name="department_id" value="<?php echo $personDepartmentId; ?>">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Tahun Ajaran</label>
                <select name="academic_year_id" class="form-select">
                    <option value="">Pilih Tahun Ajaran</option>
                    <?php
                    $ayList = $pdo->query("SELECT * FROM academic_years ORDER BY name DESC")->fetchAll();
                    $selectedAy = $editDoc['academic_year_id'] ?? '';
                    foreach ($ayList as $ay):
                    ?>
                    <option value="<?php echo $ay['id']; ?>" <?php echo $selectedAy == $ay['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($ay['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Judul Surat *</label>
                <input type="text" name="title" class="form-control" placeholder="Masukkan judul surat" value="<?php echo htmlspecialchars($editDoc['title'] ?? ''); ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Tipe Surat *</label>
                <select name="type" class="form-select" id="typeSelect" required>
                    <option value="">Pilih Tipe</option>
                    <?php
                    $editType = $editDoc['type'] ?? '';
                    $typeList = $pdo->query("SELECT * FROM document_types WHERE is_active = 1 ORDER BY name")->fetchAll();
                    $typeFound = false;
                    foreach ($typeList as $t) {
                        if ($t['code'] === $editType) { $typeFound = true; break; }
                    }
                    if ($editType !== '' && !$typeFound) {
                        $ghostStmt = $pdo->prepare("SELECT * FROM document_types WHERE code = ?");
                        $ghostStmt->execute([$editType]);
                        if ($ghost = $ghostStmt->fetch()) {
                            $typeList[] = $ghost;
                        } else {
                            $typeList[] = ['id' => 0, 'code' => $editType, 'name' => ucfirst(str_replace('_', ' ', $editType)) . ' (tidak tersedia)', 'is_active' => 0];
                        }
                    }
                    $requirementsByType = [];
                    foreach ($typeList as $t):
                        $rStmt = $pdo->prepare("SELECT * FROM type_requirements WHERE type_id = ? ORDER BY sort_order ASC, id ASC");
                        $rStmt->execute([$t['id']]);
                        $requirementsByType[$t['code']] = $rStmt->fetchAll();
                    ?>
                        <option value="<?php echo $t['code']; ?>" <?php echo $editType === $t['code'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($t['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3" id="requirementsSection" style="display:none;">
                <label class="form-label">Syarat Pengurusan</label>
                <div id="requirementsList" class="list-group list-group-flush border rounded"></div>
            </div>

            <div class="mb-3">
                <label class="form-label">Deskripsi</label>
                <textarea name="description" class="form-control" rows="5" placeholder="Deskripsi atau isi surat..."><?php echo htmlspecialchars($editDoc['description'] ?? ''); ?></textarea>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" name="save_draft" class="btn btn-outline-secondary flex-fill">
                    <i class="fas fa-save me-2"></i> <?php echo $editDoc ? 'Simpan Perubahan' : 'Simpan sebagai Draft'; ?>
                </button>
                <button type="submit" name="submit" class="btn btn-primary flex-fill">
                    <i class="fas fa-paper-plane me-2"></i> <?php echo $editDoc ? 'Ajukan Surat' : 'Ajukan Surat'; ?>
                </button>
            </div>
        </div>
    </form>
</div>

<script>
// Skip client validation when saving as draft
document.querySelector('button[name="save_draft"]')?.addEventListener('click', function() {
    document.getElementById('suratForm').noValidate = true;
});

var requirementsData = <?php echo json_encode($requirementsByType); ?>;
var editAttachments = <?php echo json_encode($editAttachments); ?>;
var editTextResponses = <?php echo json_encode($editTextResponses); ?>;
var typeSelect = document.getElementById('typeSelect');
var reqSection = document.getElementById('requirementsSection');
var reqList = document.getElementById('requirementsList');

function decodeListConfig(r) {
    var cfg = r && r.config ? JSON.parse(r.config) : null;
    if (cfg && Array.isArray(cfg) && cfg.length) return cfg;
    return [{ key: 'nama', label: 'Nama Anggota' }];
}

function listRowHtml(cols, rowVals, idx) {
    var h = '<tr class="list-row">';
    h += '<td class="text-center align-middle list-no">' + (idx + 1) + '</td>';
    cols.forEach(function (c) {
        var v = rowVals ? (rowVals[c.key] !== undefined ? rowVals[c.key] : '') : '';
        v = String(v).replace(/"/g, '&quot;');
        h += '<td><input type="text" data-col="' + c.key + '" name="syarat_list[0][0][' + c.key + ']" class="form-control form-control-sm" value="' + v + '"></td>';
    });
    h += '<td class="text-center align-middle" style="width:44px;"><button type="button" class="btn btn-sm btn-outline-danger list-delrow"><i class="fas fa-times"></i></button></td>';
    h += '</tr>';
    return h;
}

function buildListBlock(r, existing) {
    var cols = decodeListConfig(r);
    var rows = [];
    if (existing) { try { var p = JSON.parse(existing); if (Array.isArray(p)) rows = p; } catch (e) {} }
    if (!rows.length) rows = [{}];
    var h = '<div class="list-block mt-1" data-req="' + r.id + '" data-cols="' + encodeURIComponent(JSON.stringify(cols)) + '">';
    h += '<table class="table table-sm table-bordered mb-1 list-table"><thead><tr>';
    h += '<th class="text-center" style="width:44px;">No</th>';
    cols.forEach(function (c) { h += '<th>' + c.label + '</th>'; });
    h += '<th style="width:44px;"></th></tr></thead><tbody>';
    rows.forEach(function (rv, i) { h += listRowHtml(cols, rv, i); });
    h += '</tbody></table>';
    h += '<button type="button" class="btn btn-sm btn-outline-primary list-add"><i class="fas fa-plus me-1"></i> Tambah Anggota</button>';
    h += '</div>';
    return h;
}

function renumberTbody(tbody, reqId) {
    Array.prototype.forEach.call(tbody.querySelectorAll('.list-row'), function (tr, i) {
        var noCell = tr.querySelector('.list-no');
        if (noCell) noCell.textContent = i + 1;
        tr.querySelectorAll('input').forEach(function (inp) {
            var col = inp.getAttribute('data-col');
            inp.name = 'syarat_list[' + reqId + '][' + i + '][' + col + ']';
        });
    });
}

function listBlockByElement(el) {
    var block = el.closest('.list-block');
    if (!block) return null;
    var reqId = parseInt(block.getAttribute('data-req'), 10);
    var cols = JSON.parse(decodeURIComponent(block.getAttribute('data-cols') || '[]'));
    var tbody = block.querySelector('.list-table tbody');
    return { block: block, reqId: reqId, cols: cols, tbody: tbody };
}

document.addEventListener('click', function (e) {
    var addBtn = e.target.closest('.list-add');
    if (addBtn) {
        var ctx = listBlockByElement(addBtn);
        if (!ctx) return;
        var idx = ctx.tbody.querySelectorAll('.list-row').length;
        ctx.tbody.insertAdjacentHTML('beforeend', listRowHtml(ctx.cols, {}, idx));
        renumberTbody(ctx.tbody, ctx.reqId);
        return;
    }
    var delBtn = e.target.closest('.list-delrow');
    if (delBtn) {
        var tr = delBtn.closest('tr');
        var ctx2 = listBlockByElement(delBtn);
        if (!ctx2) return;
        if (ctx2.tbody.querySelectorAll('.list-row').length <= 1) return;
        tr.remove();
        renumberTbody(ctx2.tbody, ctx2.reqId);
    }
});

function renderRequirements() {
    var reqs = requirementsData[typeSelect.value] || [];
    reqList.innerHTML = '';
    if (reqs.length) {
        reqs.forEach(function(r) {
            var icon = r.is_required ? 'fa-circle-check text-danger' : 'fa-circle text-secondary';
            var badge = r.is_required ? '<span class="badge bg-danger ms-2">Wajib</span>' : '<span class="badge bg-secondary ms-2">Opsional</span>';
            var typeLabel;
            if ((r.input_type || 'file') === 'text') typeLabel = '<span class="badge bg-primary ms-1 small"><i class="fas fa-font me-1"></i>Teks</span>';
            else if ((r.input_type || 'file') === 'list') typeLabel = '<span class="badge bg-info ms-1 small"><i class="fas fa-list-ol me-1"></i>List</span>';
            else typeLabel = '<span class="badge bg-secondary ms-1 small"><i class="fas fa-upload me-1"></i>File</span>';
            var inputType = r.input_type || 'file';
            var maxSizeKb = parseInt(r.max_size) || 0;
            var maxSizeHtml = '';
            if (inputType === 'file' && maxSizeKb > 0) {
                var sizeLabel = maxSizeKb >= 1024 ? (maxSizeKb / 1024).toFixed(1) + ' MB' : maxSizeKb + ' KB';
                maxSizeHtml = '<span class="badge bg-info ms-1">Maks ' + sizeLabel + '</span>';
            }
            var inputHtml = '';
            if (inputType === 'list') {
                var existingList = editTextResponses[r.id] || '';
                inputHtml = buildListBlock(r, existingList);
            } else if (inputType === 'text') {
                var existingText = editTextResponses[r.id] || '';
                inputHtml = '<div class="mt-1">' +
                    '<input type="text" name="syarat_text[' + r.id + ']" class="form-control form-control-sm" placeholder="Masukkan ' + r.description.toLowerCase() + '" value="' + existingText.replace(/"/g, '&quot;') + '"' + (r.is_required && !existingText ? ' required' : '') + '>' +
                    '</div>';
            } else if (maxSizeKb > 0) {
                var accept = '.pdf,.doc,.docx,.jpg,.jpeg,.png,.zip,.rar';
                var existingFile = editAttachments[r.id];
                var existingHtml = '';
                if (existingFile) {
                    existingHtml = '<div class="mt-1 text-success small"><i class="fas fa-check-circle me-1"></i> File terupload: <strong>' + existingFile.file_name + '</strong></div>';
                }
                inputHtml = '<div class="mt-1">' +
                    '<input type="file" name="syarat_file[' + r.id + ']" class="form-control form-control-sm file-syarat" accept="' + accept + '" data-maxkb="' + maxSizeKb + '" data-desc="' + r.description + '"' + (r.is_required && !existingFile ? ' required' : '') + '>' +
                    existingHtml +
                    '<small class="text-muted">Ukuran maksimal ' + sizeLabel + '</small>' +
                    '<div class="file-syarat-alert small text-danger" style="display:none;"></div>' +
                    '</div>';
            }
            reqList.innerHTML += '<div class="list-group-item py-2 px-3 border-start-0 border-end-0">' +
                '<div class="d-flex align-items-center"><i class="fas ' + icon + ' me-2"></i> ' + r.description + badge + typeLabel + maxSizeHtml + '</div>' +
                inputHtml + '</div>';
        });
        reqSection.style.display = 'block';
        reqList.querySelectorAll('.list-block').forEach(function (block) {
            renumberTbody(block.querySelector('.list-table tbody'), parseInt(block.getAttribute('data-req'), 10));
        });
        bindFileValidation();
    } else {
        reqSection.style.display = 'none';
    }
}

typeSelect.addEventListener('change', renderRequirements);

// Auto-render if editing
if (typeSelect.value) renderRequirements();

function bindFileValidation() {
    document.querySelectorAll('.file-syarat').forEach(function(input) {
        input.addEventListener('change', function() {
            var alertDiv = this.parentElement.querySelector('.file-syarat-alert');
            var maxKb = parseInt(this.dataset.maxkb) || 0;
            var desc = this.dataset.desc || 'File';
            if (this.files.length && maxKb > 0) {
                var file = this.files[0];
                var fileKb = file.size / 1024;
                if (fileKb > maxKb) {
                    var maxLabel = maxKb >= 1024 ? (maxKb / 1024).toFixed(1) + ' MB' : maxKb + ' KB';
                    alertDiv.textContent = 'File "' + desc + '" melebihi batas ' + maxLabel + ' (' + (fileKb / 1024).toFixed(2) + ' MB)';
                    alertDiv.style.display = 'block';
                    this.value = '';
                } else {
                    alertDiv.style.display = 'none';
                }
            }
        });
    });
}

function validateRequiredLists() {
    var reqs = requirementsData[typeSelect.value] || [];
    var problem = null;
    reqs.forEach(function (r) {
        if ((r.input_type || 'file') !== 'list' || !r.is_required) return;
        var cols = decodeListConfig(r);
        var block = reqList.querySelector('.list-block[data-req="' + r.id + '"]');
        if (!block) return;
        var filled = false;
        block.querySelectorAll('.list-row input').forEach(function (inp) {
            if (inp.value.trim() !== '') filled = true;
        });
        if (!filled) problem = r.description;
    });
    return problem;
}

document.getElementById('suratForm')?.addEventListener('submit', function (e) {
    if (this.noValidate) return;
    var problem = validateRequiredLists();
    if (problem) {
        e.preventDefault();
        alert('Syarat daftar wajib belum diisi: "' + problem + '". Silakan isi minimal satu anggota.');
        return false;
    }
});
</script>