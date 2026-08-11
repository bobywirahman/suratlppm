<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/constant.php';
require_once __DIR__ . '/models/User.php';
require_once __DIR__ . '/models/Document.php';
require_once __DIR__ . '/includes/helpers.php';

session_start();

$messages = '';
$messageType = 'success';
if (isset($_SESSION['error'])) {
    $messages = $_SESSION['error'];
    $messageType = 'danger';
    unset($_SESSION['error']);
} elseif (isset($_SESSION['success'])) {
    $messages = $_SESSION['success'];
    $messageType = 'success';
    unset($_SESSION['success']);
}

// Handle logout
if (isset($_GET['logout'])) {
    logActivity('Logout', 'Pengguna keluar dari sistem');
    session_destroy();
    header("Location: " . SITE_URL);
    exit;
}

$userModel = new User();

// Handle login (kecuali form non-login yang juga memakai field email/password)
$isSaveProfile = isset($_POST['profile-save']);
$isSaveUser    = ($_GET['page'] ?? '') === 'users-save';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email']) && isset($_POST['password']) && !isset($_POST['register']) && !$isSaveProfile && !$isSaveUser) {
    if ($userModel->login($_POST['email'], $_POST['password'])) {
        logActivity('Login', 'Pengguna masuk ke sistem');
        header("Location: " . SITE_URL);
        exit;
    } else {
        $_SESSION['error'] = "Email/Username atau password salah!";
    }
}

// Handle approve document
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve-document'])) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: " . SITE_URL);
        exit;
    }
    $user = $userModel->checkAuth();
    if (!hasPermission('approve_documents')) {
        $_SESSION['error'] = "Akses ditolak";
        header("Location: " . SITE_URL);
        exit;
    }
    include __DIR__ . '/modules/approval/approve_document.php';
    exit;
}

// Handle revisi document
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['revisi-document'])) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: " . SITE_URL);
        exit;
    }
    $user = $userModel->checkAuth();
    if (!hasPermission('approve_documents')) {
        $_SESSION['error'] = "Akses ditolak";
        header("Location: " . SITE_URL);
        exit;
    }
    include __DIR__ . '/modules/approval/revisi_document.php';
    exit;
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['profile-save'])) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: " . SITE_URL);
        exit;
    }
    include __DIR__ . '/modules/auth/profile_save.php';
    exit;
}

// Handle registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $pdo = db();
    $errors = [];
    $full_name = trim($_POST['full_name'] ?? '');
    $nim = trim($_POST['nim'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $no_hp = trim($_POST['no_hp'] ?? '');
    $faculty_id = trim($_POST['faculty_id'] ?? '');
    $department_id = trim($_POST['department_id'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $konfirmasi = $_POST['konfirmasi_password'] ?? '';

    if ($full_name === '') $errors[] = 'Nama Lengkap';
    if ($nim === '') $errors[] = 'NIM';
    if ($email === '') $errors[] = 'Email';
    if ($no_hp === '') $errors[] = 'No. HP';
    if ($faculty_id === '') $errors[] = 'Fakultas';
    if ($department_id === '') $errors[] = 'Program Studi';
    if ($alamat === '') $errors[] = 'Alamat';
    if ($username === '') $errors[] = 'Username';
    if ($password === '') $errors[] = 'Password';
    if ($konfirmasi === '') $errors[] = 'Konfirmasi Password';

    if (!empty($errors)) {
        $_SESSION['error'] = 'Semua data harus diisi: ' . implode(', ', $errors);
        $_SESSION['register_old'] = $_POST;
        header("Location: " . SITE_URL . "?page=register");
        exit;
    } elseif (strlen($password) < 8) {
        $_SESSION['error'] = 'Password minimal 8 karakter';
        $_SESSION['register_old'] = $_POST;
        header("Location: " . SITE_URL . "?page=register");
        exit;
    } elseif ($password !== $konfirmasi) {
        $_SESSION['error'] = 'Konfirmasi password tidak cocok';
        $_SESSION['register_old'] = $_POST;
        header("Location: " . SITE_URL . "?page=register");
        exit;
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'Format email tidak valid';
        $_SESSION['register_old'] = $_POST;
        header("Location: " . SITE_URL . "?page=register");
        exit;
    } else {
        $emailCheck = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $emailCheck->execute([$email]);
        if ($emailCheck->fetch()) {
            $_SESSION['error'] = 'Email anda sudah terdaftar';
            $_SESSION['register_old'] = $_POST;
            header("Location: " . SITE_URL . "?page=register");
            exit;
        }
        $usernameCheck = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $usernameCheck->execute([$username]);
        if ($usernameCheck->fetch()) {
            $_SESSION['error'] = 'Username sudah terdaftar';
            $_SESSION['register_old'] = $_POST;
            header("Location: " . SITE_URL . "?page=register");
            exit;
        }
        $stmt = $pdo->prepare("INSERT INTO users (full_name, username, nim, email, no_hp, password, department_id, alamat, is_active)
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)");
        $stmt->execute([
            htmlspecialchars($full_name, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($username, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($nim, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($email, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($no_hp, ENT_QUOTES, 'UTF-8'),
            password_hash($password, PASSWORD_DEFAULT),
            $department_id,
            htmlspecialchars($alamat, ENT_QUOTES, 'UTF-8'),
        ]);
        $newUserId = $pdo->lastInsertId();
        $pdo->prepare("INSERT INTO user_roles (user_id, role_id) SELECT ?, id FROM roles WHERE name = 'mahasiswa'")->execute([$newUserId]);
        logActivity('Registrasi Akun', 'Pendaftaran akun baru: ' . $username . ' (' . $full_name . ')');
        $_SESSION['pesan'] = 'Akun berhasil didaftarkan! Silakan tunggu aktivasi oleh admin.';
        header("Location: " . SITE_URL . "?page=pesan");
        exit;
    }
}

$page = $_GET['page'] ?? '';

// Protected routes - require login (except register and pesan pages)
if (!isset($_SESSION['user_id'])) {
    if ($page === 'register') {
        unset($_SESSION['error'], $_SESSION['success']);
        $pdo = db();
        $faculties = $pdo->query("SELECT * FROM faculties ORDER BY name")->fetchAll();
        $departments = $pdo->query("SELECT * FROM departments ORDER BY name")->fetchAll();
        $old = $_SESSION['register_old'] ?? [];
        unset($_SESSION['register_old']);
        includeView('auth/register.php', [
            'faculties' => $faculties,
            'departments' => $departments,
            'old' => $old,
            'messages' => '',
            'messageType' => 'success'
        ]);
        exit;
    }
    if ($page === 'pesan') {
        $pesan = $_SESSION['pesan'] ?? 'Akun Anda sedang menunggu aktivasi oleh admin.';
        unset($_SESSION['pesan']);
        includeView('auth/pesan.php', ['pesan' => $pesan]);
        exit;
    }
    includeView('auth/login.php');
    exit;
}

$userModel = new User();
$user = $userModel->checkAuth();
if (!$user) {
    session_destroy();
    includeView('auth/login.php');
    exit;
}

// Route handling
switch ($page) {
    case 'form':
        if (!hasPermission('submit_document')) {
            $_SESSION['error'] = "Akses ditolak";
            header("Location: " . SITE_URL);
            exit;
        }
        $pdo = db();
        $editDoc = null;
        $editAttachments = [];
        if (!empty($_GET['edit'])) {
            $stmt = $pdo->prepare("SELECT * FROM documents WHERE id = ? AND applicant_id = ? AND (status = ? OR status = ? OR status = ?)");
            $stmt->execute([$_GET['edit'], $user['id'], STATUS_DRAFT, STATUS_REJECTED, STATUS_REVISI]);
            $editDoc = $stmt->fetch();
            if ($editDoc) {
                $attStmt = $pdo->prepare("SELECT * FROM document_attachments WHERE document_id = ?");
                $attStmt->execute([$editDoc['id']]);
                foreach ($attStmt->fetchAll() as $a) {
                    $editAttachments[$a['requirement_id']] = $a;
                }
            }
        }
        includeView('documents/form.php', ['user' => $user, 'pdo' => $pdo, 'editDoc' => $editDoc, 'editAttachments' => $editAttachments]);
        break;

    case 'save':
        if (!hasPermission('submit_document')) {
            $_SESSION['error'] = "Akses ditolak";
            header("Location: " . SITE_URL);
            exit;
        }
        include __DIR__ . '/modules/documents/save.php';
        break;

    case 'list':
        if (!hasPermission('view_documents')) {
            $_SESSION['error'] = "Akses ditolak";
            header("Location: " . SITE_URL);
            exit;
        }
        $docModel = new Document();
        $filters = array_intersect_key($_GET, array_flip(['status', 'department_id', 'search']));
        if (!hasPermission('view_all_documents')) {
            $filters['applicant_id'] = $user['id'];
        }
        $perPageOptions = [10, 15, 25, 50, 100, 'all'];
        $perPageReq = $_GET['per_page'] ?? 15;
        if (!in_array($perPageReq, $perPageOptions)) $perPageReq = 15;
        $perPage = $perPageReq === 'all' ? PHP_INT_MAX : (int)$perPageReq;
        $page = max(1, (int)($_GET['hal'] ?? 1));
        $total = $docModel->countAllDocuments($filters);
        $totalPages = max(1, (int)ceil($total / $perPage));
        if ($page > $totalPages) $page = $totalPages;
        $offset = ($page - 1) * $perPage;
        $documents = $docModel->getAllDocuments($filters, $perPage, $offset);
        $pdo = db();
        $userDepartments = $pdo->query("SELECT * FROM departments ORDER BY name")->fetchAll();
        includeView('documents/list.php', ['user' => $user, 'documents' => $documents, 'filters' => $filters, 'userDepartments' => $userDepartments, 'total' => $total, 'page' => $page, 'totalPages' => $totalPages, 'offset' => $offset, 'perPage' => $perPage, 'perPageReq' => $perPageReq, 'perPageOptions' => $perPageOptions]);
        break;

    case 'detail':
        if (!hasPermission('view_documents') && !hasPermission('approve_documents')) {
            $_SESSION['error'] = "Akses ditolak";
            header("Location: " . SITE_URL);
            exit;
        }
        include __DIR__ . '/modules/documents/detail.php';
        break;

    case 'pending':
        if (!hasPermission('approve_documents')) {
            $_SESSION['error'] = "Akses ditolak";
            header("Location: " . SITE_URL);
            exit;
        }
        $pdo = db();
        $docModel = new Document();
        $filters = array_intersect_key($_GET, array_flip(['departmentId', 'search']));
        $documents = $docModel->getPendingApprovals($filters);
        $departments = $pdo->query("SELECT * FROM departments ORDER BY name")->fetchAll();
        includeView('approval/pending.php', ['user' => $user, 'documents' => $documents, 'departments' => $departments, 'pdo' => $pdo, 'filters' => $filters]);
        break;

    case 'laporan-rekap':
        if (!hasPermission('view_all_documents')) {
            $_SESSION['error'] = "Akses ditolak";
            header("Location: " . SITE_URL);
            exit;
        }
        include __DIR__ . '/modules/laporan/rekap_status.php';
        break;

    case 'laporan-rekap-export':
        if (!hasPermission('view_all_documents')) {
            $_SESSION['error'] = "Akses ditolak";
            header("Location: " . SITE_URL);
            exit;
        }
        include __DIR__ . '/modules/laporan/export_rekap.php';
        break;

    case 'laporan-activity-log':
        if (!hasPermission('view_all_documents')) {
            $_SESSION['error'] = "Akses ditolak";
            header("Location: " . SITE_URL);
            exit;
        }
        include __DIR__ . '/modules/laporan/activity_log.php';
        break;

    case 'profile':
        $pdo = db();
        $faculties = $pdo->query("SELECT * FROM faculties ORDER BY name")->fetchAll();
        $departments = $pdo->query("SELECT * FROM departments ORDER BY name")->fetchAll();
        includeView('auth/profile.php', ['user' => $user, 'pdo' => $pdo, 'faculties' => $faculties, 'departments' => $departments]);
        break;

    case 'delete-draft':
        $pdo = db();
        $delId = $_GET['id'] ?? 0;
        $delStmt = $pdo->prepare("SELECT id FROM documents WHERE id = ? AND applicant_id = ? AND status = ?");
        $delStmt->execute([$delId, $user['id'], STATUS_DRAFT]);
        if ($delStmt->fetch()) {
            // Delete attachments first
            $attDel = $pdo->prepare("SELECT file_path FROM document_attachments WHERE document_id = ?");
            $attDel->execute([$delId]);
            foreach ($attDel->fetchAll() as $a) {
                if (!empty($a['file_path'])) {
                    $abs = upload_path($a['file_path']);
                    if ($abs && file_exists($abs)) unlink($abs);
                }
            }
            $pdo->prepare("DELETE FROM document_attachments WHERE document_id = ?")->execute([$delId]);
            $pdo->prepare("DELETE FROM documents WHERE id = ?")->execute([$delId]);
            logActivity('Hapus Draft', 'Menghapus draft surat id ' . $delId);
            $_SESSION['success'] = "Draft berhasil dihapus.";
        } else {
            $_SESSION['error'] = "Draft tidak ditemukan!";
        }
        header("Location: " . SITE_URL . "?page=list");
        exit;

    case 'submit-draft':
        if (!hasPermission('submit_document')) {
            $_SESSION['error'] = "Akses ditolak"; header("Location: " . SITE_URL); exit;
        }
        $pdo = db();
        $subId = $_GET['id'] ?? 0;
        $subStmt = $pdo->prepare("SELECT id FROM documents WHERE id = ? AND applicant_id = ? AND status = ?");
        $subStmt->execute([$subId, $user['id'], STATUS_DRAFT]);
        if ($subStmt->fetch()) {
            $now = date('Y-m-d H:i:s');
            // Pertahankan tanggal pengajuan pertama, isi hanya jika belum pernah diajukan
            $pdo->prepare("UPDATE documents SET status = ?, submitted_at = IFNULL(submitted_at, ?) WHERE id = ?")
                ->execute([STATUS_SUBMITTED, $now, $subId]);
            // Catat pengajuan ulang bila draft ini berasal dari revisi yang belum diajukan ulang
            $pdo->prepare("UPDATE document_revisions SET resubmitted_at = ? WHERE document_id = ? AND resubmitted_at IS NULL ORDER BY version DESC LIMIT 1")
                ->execute([$now, $subId]);
            logActivity('Ajukan Surat', 'Mengajukan draft surat id ' . $subId);
            $_SESSION['success'] = "Draft berhasil diajukan!";
        } else {
            $_SESSION['error'] = "Draft tidak ditemukan!";
        }
        header("Location: " . SITE_URL . "?page=list");
        exit;

    case 'delete-verified-document':
        if (!hasPermission('delete_approved_documents')) {
            $_SESSION['error'] = "Akses ditolak";
            header("Location: " . SITE_URL);
            exit;
        }
        $pdo = db();
        $delId = $_GET['id'] ?? 0;
        $delStmt = $pdo->prepare("SELECT id, status FROM documents WHERE id = ?");
        $delStmt->execute([$delId]);
        $delDoc = $delStmt->fetch();
        if ($delDoc && $delDoc['status'] !== STATUS_DRAFT) {
            // Hapus file lampiran dari disk
            $attDel = $pdo->prepare("SELECT file_path FROM document_attachments WHERE document_id = ?");
            $attDel->execute([$delId]);
            foreach ($attDel->fetchAll() as $a) {
                if (!empty($a['file_path'])) {
                    $abs = upload_path($a['file_path']);
                    if ($abs && file_exists($abs)) unlink($abs);
                }
            }
            // Hapus dependensi (approvals TIDAK cascade ke documents, hapus manual)
            $pdo->prepare("DELETE FROM approvals WHERE document_id = ?")->execute([$delId]);
            $pdo->prepare("DELETE FROM document_attachments WHERE document_id = ?")->execute([$delId]);
            $pdo->prepare("DELETE FROM document_revisions WHERE document_id = ?")->execute([$delId]);
            $pdo->prepare("DELETE FROM research_projects WHERE document_id = ?")->execute([$delId]);
            $pdo->prepare("DELETE FROM documents WHERE id = ?")->execute([$delId]);
            logActivity('Hapus Surat', 'Menghapus surat (non-draft) id ' . $delId);
            $_SESSION['success'] = "Surat berhasil dihapus.";
        } else {
            $_SESSION['error'] = "Surat tidak ditemukan atau berstatus draft.";
        }
        header("Location: " . SITE_URL . "?page=" . ($_GET['from'] ?? 'list'));
        exit;

    case 'print':
        include __DIR__ . '/modules/documents/print.php';
        break;

    case 'users':
        if (!hasPermission('manage_users')) { $_SESSION['error'] = "Akses ditolak"; header("Location: " . SITE_URL); exit; }
        include __DIR__ . '/modules/users/index.php';
        break;

    case 'users-save':
        if (!hasPermission('manage_users')) { $_SESSION['error'] = "Akses ditolak"; header("Location: " . SITE_URL); exit; }
        include __DIR__ . '/modules/users/save.php';
        break;

    case 'types':
        if (!hasPermission('manage_document_types')) { $_SESSION['error'] = "Akses ditolak"; header("Location: " . SITE_URL); exit; }
        include __DIR__ . '/modules/jenis_surat/index.php';
        break;

    case 'types-save':
        if (!hasPermission('manage_document_types')) { $_SESSION['error'] = "Akses ditolak"; header("Location: " . SITE_URL); exit; }
        include __DIR__ . '/modules/jenis_surat/save.php';
        break;

    case 'prodi':
        if (!hasPermission('manage_departments')) { $_SESSION['error'] = "Akses ditolak"; header("Location: " . SITE_URL); exit; }
        include __DIR__ . '/modules/prodi/index.php';
        break;

    case 'prodi-save':
        if (!hasPermission('manage_departments')) { $_SESSION['error'] = "Akses ditolak"; header("Location: " . SITE_URL); exit; }
        include __DIR__ . '/modules/prodi/save.php';
        break;

    case 'faculties':
        if (!hasPermission('manage_faculties')) { $_SESSION['error'] = "Akses ditolak"; header("Location: " . SITE_URL); exit; }
        include __DIR__ . '/modules/faculties/index.php';
        break;

    case 'faculties-save':
        if (!hasPermission('manage_faculties')) { $_SESSION['error'] = "Akses ditolak"; header("Location: " . SITE_URL); exit; }
        include __DIR__ . '/modules/faculties/save.php';
        break;

    case 'roles':
        if (!hasPermission('manage_roles')) { $_SESSION['error'] = "Akses ditolak"; header("Location: " . SITE_URL); exit; }
        include __DIR__ . '/modules/roles/index.php';
        break;

    case 'roles-save':
        if (!hasPermission('manage_roles')) { $_SESSION['error'] = "Akses ditolak"; header("Location: " . SITE_URL); exit; }
        include __DIR__ . '/modules/roles/save.php';
        break;

    case 'permissions':
        if (!hasPermission('manage_permissions')) { $_SESSION['error'] = "Akses ditolak"; header("Location: " . SITE_URL); exit; }
        include __DIR__ . '/modules/roles/permissions.php';
        break;

    case 'permissions-save':
        if (!hasPermission('manage_permissions')) { $_SESSION['error'] = "Akses ditolak"; header("Location: " . SITE_URL); exit; }
        include __DIR__ . '/modules/roles/permissions_save.php';
        break;

    case 'install-permissions':
        if (!hasPermission('manage_permissions')) { $_SESSION['error'] = "Akses ditolak"; header("Location: " . SITE_URL); exit; }
        include __DIR__ . '/modules/roles/install_permissions.php';
        break;

    case 'migrate-delete-permission':
        if (!hasPermission('manage_permissions')) { $_SESSION['error'] = "Akses ditolak"; header("Location: " . SITE_URL); exit; }
        include __DIR__ . '/modules/documents/migrate_delete_permission.php';
        break;

    case 'migrate-text-input':
        if (!hasPermission('manage_document_types')) { $_SESSION['error'] = "Akses ditolak"; header("Location: " . SITE_URL); exit; }
        include __DIR__ . '/modules/jenis_surat/migrate_text_input.php';
        break;

    case 'types-migrate-list':
        if (!hasPermission('manage_document_types')) { $_SESSION['error'] = "Akses ditolak"; header("Location: " . SITE_URL); exit; }
        include __DIR__ . '/modules/jenis_surat/migrate_list.php';
        break;

    case 'types-template':
        if (!hasPermission('manage_document_types')) { $_SESSION['error'] = "Akses ditolak"; header("Location: " . SITE_URL); exit; }
        include __DIR__ . '/modules/jenis_surat/template.php';
        break;

    case 'types-template-save':
        if (!hasPermission('manage_document_types')) { $_SESSION['error'] = "Akses ditolak"; header("Location: " . SITE_URL); exit; }
        include __DIR__ . '/modules/jenis_surat/template_save.php';
        break;

    case 'types-requirements':
        if (!hasPermission('manage_document_types')) { $_SESSION['error'] = "Akses ditolak"; header("Location: " . SITE_URL); exit; }
        include __DIR__ . '/modules/jenis_surat/requirements.php';
        break;

    case 'types-requirements-save':
        if (!hasPermission('manage_document_types')) { $_SESSION['error'] = "Akses ditolak"; header("Location: " . SITE_URL); exit; }
        include __DIR__ . '/modules/jenis_surat/requirements_save.php';
        break;

    case 'academic-years':
        if (!hasPermission('manage_academic_years')) { $_SESSION['error'] = "Akses ditolak"; header("Location: " . SITE_URL); exit; }
        include __DIR__ . '/modules/academic_years/index.php';
        break;

    case 'academic-years-save':
        if (!hasPermission('manage_academic_years')) { $_SESSION['error'] = "Akses ditolak"; header("Location: " . SITE_URL); exit; }
        include __DIR__ . '/modules/academic_years/save.php';
        break;

    case 'academic-years-migrate':
        if (!hasPermission('manage_academic_years')) { $_SESSION['error'] = "Akses ditolak"; header("Location: " . SITE_URL); exit; }
        include __DIR__ . '/modules/academic_years/migrate.php';
        break;

    case 'revisi-migrate':
        if (!hasPermission('manage_settings')) { $_SESSION['error'] = "Akses ditolak"; header("Location: " . SITE_URL); exit; }
        include __DIR__ . '/modules/revisions/migrate.php';
        break;

    case 'settings':
        if (!hasPermission('manage_settings')) { $_SESSION['error'] = "Akses ditolak"; header("Location: " . SITE_URL); exit; }
        include __DIR__ . '/modules/settings/index.php';
        break;

    case 'settings-save':
        if (!hasPermission('manage_settings')) { $_SESSION['error'] = "Akses ditolak"; header("Location: " . SITE_URL); exit; }
        include __DIR__ . '/modules/settings/save.php';
        break;

    case 'settings-migrate':
        if (!hasPermission('manage_settings')) { $_SESSION['error'] = "Akses ditolak"; header("Location: " . SITE_URL); exit; }
        include __DIR__ . '/modules/settings/migrate.php';
        break;

    case 'website':
        if (!hasPermission('manage_settings')) { $_SESSION['error'] = "Akses ditolak"; header("Location: " . SITE_URL); exit; }
        include __DIR__ . '/modules/settings/website.php';
        break;

    case 'website-save':
        if (!hasPermission('manage_settings')) { $_SESSION['error'] = "Akses ditolak"; header("Location: " . SITE_URL); exit; }
        include __DIR__ . '/modules/settings/website_save.php';
        break;

    case 'informasi':
        if (!hasPermission('manage_informasi')) { $_SESSION['error'] = "Akses ditolak"; header("Location: " . SITE_URL); exit; }
        include __DIR__ . '/modules/informasi/index.php';
        break;

    case 'informasi-save':
        if (!hasPermission('manage_informasi')) { $_SESSION['error'] = "Akses ditolak"; header("Location: " . SITE_URL); exit; }
        include __DIR__ . '/modules/informasi/save.php';
        break;

    case 'informasi-delete':
        if (!hasPermission('manage_informasi')) { $_SESSION['error'] = "Akses ditolak"; header("Location: " . SITE_URL); exit; }
        include __DIR__ . '/modules/informasi/delete.php';
        break;

    case 'informasi-toggle':
        if (!hasPermission('manage_informasi')) { $_SESSION['error'] = "Akses ditolak"; header("Location: " . SITE_URL); exit; }
        include __DIR__ . '/modules/informasi/publish.php';
        break;

    case 'informasi-migrate':
        if (!hasPermission('manage_informasi')) { $_SESSION['error'] = "Akses ditolak"; header("Location: " . SITE_URL); exit; }
        include __DIR__ . '/modules/informasi/migrate.php';
        break;

    case 'upload-image':
        if (!hasPermission('manage_settings')) { $_SESSION['error'] = "Akses ditolak"; header("Location: " . SITE_URL); exit; }
        $uploadDir = __DIR__ . '/uploads/settings/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $file = $_FILES['file'] ?? null;
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) { http_response_code(400); echo json_encode(['error' => 'Upload gagal']); exit; }
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'])) { http_response_code(400); echo json_encode(['error' => 'Format tidak diizinkan']); exit; }
        $field = preg_replace('/[^a-z_]/', '', $_POST['field'] ?? 'about_image');
        $prefix = preg_replace('/_image$/', '', $field) . '_';
        // Hapus file lama yang terkait field ini (bersihkan juga nilai yang ber-prefix rusak)
        $stmt = $pdo->prepare("SELECT setting_value FROM app_settings WHERE setting_key = ?");
        $stmt->execute([$field]);
        $old = $stmt->fetchColumn();
        if ($old) {
            $oldRel = upload_rel($old);
            if (strpos($oldRel, '/uploads/') === 0) {
                $oldAbs = app_root() . '/' . ltrim($oldRel, '/');
                if (file_exists($oldAbs)) unlink($oldAbs);
            }
        }
        $name = $prefix . time() . '.' . $ext;
        if (!move_uploaded_file($file['tmp_name'], $uploadDir . $name)) { http_response_code(500); echo json_encode(['error' => 'Gagal menyimpan file']); exit; }
        $url = '/uploads/settings/' . $name;
        $stmt = $pdo->prepare("INSERT INTO app_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->execute([$field, $url, $url]);
        // Bersihkan sampah: hapus file di uploads/settings/ yang tidak dirujuk setting mana pun
        $used = [];
        $all = $pdo->query("SELECT setting_value FROM app_settings")->fetchAll(PDO::FETCH_COLUMN);
        foreach ($all as $v) {
            $vRel = upload_rel($v);
            if (strpos($vRel, '/uploads/settings/') === 0) $used[] = basename($vRel);
        }
        foreach (scandir($uploadDir) ?: [] as $fn) {
            if ($fn === '.' || $fn === '..' || in_array($fn, $used, true)) continue;
            @unlink($uploadDir . $fn);
        }
        echo json_encode(['url' => $url]);
        exit;

    default:
        // Dashboard
        $docModel = new Document();
        $stats = $docModel->getStatistics($user['id'], hasPermission('view_all_documents'));
        $recentDocs = $docModel->getAll(hasPermission('view_all_documents') ? [] : ['applicant_id' => $user['id']]);
        $recentDocs = array_slice($recentDocs, 0, 5);
        $pdo = db();
        $totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $activeUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE is_active = 1")->fetchColumn();
        $pendingUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE is_active = 0")->fetchColumn();
        $userStats = ['total' => $totalUsers, 'active' => $activeUsers, 'pending' => $pendingUsers];
        includeView('dashboard/index.php', [
            'statistics' => $stats,
            'recentDocuments' => $recentDocs,
            'user' => $user,
            'userStats' => $userStats
        ]);
        break;
}
