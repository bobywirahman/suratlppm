<?php
require_once __DIR__ . '/../../config/db.php';
$pdo = db();

// Create permissions table
$pdo->exec("CREATE TABLE IF NOT EXISTS permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    `key` VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Create role_permissions table
$pdo->exec("CREATE TABLE IF NOT EXISTS role_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL,
    permission_id INT NOT NULL,
    UNIQUE KEY unique_role_perm (role_id, permission_id),
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
)");

// Seed permissions
$permissions = [
    ['dashboard', 'Dashboard', 'Mengakses halaman dashboard'],
    ['submit_document', 'Pengajuan Surat', 'Mengajukan surat baru'],
    ['view_documents', 'Daftar Surat', 'Melihat daftar surat'],
    ['view_all_documents', 'Lihat Semua Surat', 'Melihat semua surat (tidak terbatas milik sendiri)'],
    ['approve_documents', 'Persetujuan Surat', 'Menyetujui atau merevisi surat'],
    ['view_department_docs', 'Lihat Surat Per Prodi', 'Melihat surat berdasarkan program studi'],
    ['manage_users', 'Manajemen User', 'Mengelola daftar pengguna'],
    ['manage_roles', 'Manajemen Role', 'Mengelola role pengguna'],
    ['manage_permissions', 'Hak Akses', 'Mengatur hak akses setiap role'],
    ['manage_faculties', 'Manajemen Fakultas', 'Mengelola data fakultas'],
    ['manage_departments', 'Manajemen Prodi', 'Mengelola data program studi'],
    ['manage_document_types', 'Jenis Surat', 'Mengelola jenis surat'],
    ['delete_documents', 'Hapus Surat', 'Menghapus surat yang ada'],
    ['delete_approved_documents', 'Hapus Surat Terverifikasi', 'Menghapus surat yang statusnya bukan draft (sudah diajukan/disetujui)'],
    ['manage_academic_years', 'Tahun Ajaran', 'Mengelola tahun ajaran'],
    ['manage_settings', 'Setting Aplikasi', 'Mengatur pengaturan aplikasi'],
    ['manage_informasi', 'Informasi LPPM', 'Mengelola informasi LPPM di landing page'],
];

$stmt = $pdo->prepare("INSERT IGNORE INTO permissions (`key`, name, description) VALUES (?, ?, ?)");
foreach ($permissions as $p) {
    $stmt->execute($p);
}

// Map default permissions to roles
$roles = $pdo->query("SELECT id, name FROM roles")->fetchAll();
foreach ($roles as $role) {
    $permKeys = [];
    if ($role['name'] === 'admin') {
        $permKeys = ['dashboard', 'submit_document', 'view_documents', 'view_all_documents', 'approve_documents', 'view_department_docs', 'manage_users', 'manage_roles', 'manage_permissions', 'manage_faculties', 'manage_departments', 'manage_document_types', 'delete_documents', 'delete_approved_documents', 'manage_academic_years', 'manage_settings', 'manage_informasi'];
    } elseif ($role['name'] === 'staff') {
        $permKeys = ['dashboard', 'view_documents', 'view_all_documents', 'approve_documents', 'view_department_docs'];
    } else {
        $permKeys = ['dashboard', 'submit_document', 'view_documents'];
    }

    $delStmt = $pdo->prepare("DELETE FROM role_permissions WHERE role_id = ?");
    $delStmt->execute([$role['id']]);

    $insStmt = $pdo->prepare("INSERT INTO role_permissions (role_id, permission_id) SELECT ?, id FROM permissions WHERE `key` = ?");
    foreach ($permKeys as $key) {
        $insStmt->execute([$role['id'], $key]);
    }
}

$_SESSION['success'] = "Tabel permissions & role_permissions berhasil dibuat!";
header("Location: ?page=roles");
exit;
