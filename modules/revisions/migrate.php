<?php
// Migrasi Riwayat Revisi Surat
// 1. Tambah nilai 'revisi' ke ENUM approvals.action
// 2. Perbaiki action revisi yang sebelumnya tersimpan kosong
// 3. Perbaiki tanggal pengajuan pertama (submitted_at) yang NULL atau tertimpa
require_once __DIR__ . '/../../config/db.php';
$pdo = db();

try {
    $pdo->exec("ALTER TABLE approvals MODIFY COLUMN action ENUM('approve', 'reject', 'revisi') NOT NULL COMMENT 'Aksi persetujuan'");
} catch (Exception $e) {
    // Column sudah dalam bentuk yang benar atau sudah pernah dimigrasi
}

$pdo->exec("UPDATE approvals SET action = 'revisi' WHERE action NOT IN ('approve', 'reject')");

// submitted_at wajib menjadi tanggal pengajuan pertama:
// - NULL -> pakai created_at
// - tertimpa (lebih besar dari revisi pertama) -> kembalikan ke created_at
$pdo->exec("
    UPDATE documents d
    LEFT JOIN (
        SELECT document_id, MIN(revisi_at) AS first_revisi_at
        FROM document_revisions
        WHERE revisi_at IS NOT NULL
        GROUP BY document_id
    ) r ON r.document_id = d.id
    SET d.submitted_at = d.created_at
    WHERE d.submitted_at IS NULL
       OR (r.first_revisi_at IS NOT NULL AND d.submitted_at > r.first_revisi_at)
");

$_SESSION['success'] = "Migrasi riwayat revisi berhasil diterapkan!";
header("Location: ?page=list");
exit;
