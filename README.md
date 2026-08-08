<?php
// Quick Setup Guide untuk LPPM Universitas Fort de Kock
// ===========================================

echo "🚀 SETUP DATABASE - LPPM FDK\n";
echo str_repeat("=", 50) . "\n\n";

echo "⚠️  MySQL belum berjalan di XAMPP!\n\n";

echo "LANGKAH 1: START XAMPP\n";
echo "   1. Buka XAMPP Control Panel\n";
echo "   2. Klik 'Start' pada MySQL (port 3306)\n";
echo "   3. Pastikan status MySQL = GREEN ✓\n\n";

echo "LANGKAH 2: IMPORT DATABASE\n";
echo "   Setelah MySQL running:\n";
echo "   - Buka phpMyAdmin di http://localhost/phpmyadmin/\n";
echo "   - Klik 'Import' tab\n";
echo "   - Pilih file: C:/xampp/htdocs/suratlppm/database.sql\n";
echo "   - Klik 'Go' untuk import\n\n";

echo "LANGKAH 3: AAKUN ADMIN\n";
echo "   Email: admin@suratlppm.fdk.ac.id\n";
echo "   Password: admin123\n\n";

echo "LANGKAH 4: ACCESS APLIKASI\n";
echo "   http://localhost/suratlppm/\n\n";

echo "=== DATABASE STRUCTURE ===\n";
echo "\nTabel yang dibuat:\n";
echo "✓ faculties - Data fakultas\n";
echo "✓ departments - Data departemen/fakultas\n";
echo "✓ users - Data pengguna (admin & researcher)\n";
echo "✓ documents - Data pengajuan surat\n";
echo "✓ document_attachments - File lampiran\n";
echo "✓ approvals - Riwayat persetujuan\n";
echo "✓ research_projects - Proyek penelitian\n";
echo "✓ user_activity - Audit trail\n\n";

echo "=== SAMPLE DATA ===\n";
echo "\n4 fakultas:\n  • Fakultas Teknik (FT)\n  • Fakultas Ekonomi & Bisnis (FEK)\n  • Fakultas Ilmu Komputer (FIK)\n  • Fakultas Kedokteran (FKD)\n\n3 surat contoh sudah tersedia untuk testing.\n";
