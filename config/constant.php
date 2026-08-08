<?php
date_default_timezone_set('Asia/Jakarta');
// Application Constants for LPPM Universitas Fort de Kock
define('APP_NAME', 'SisFo Pengajuan Surat LPPM');
$scriptBasePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
if (!defined('BASE_URL')) define('BASE_URL', $scriptBasePath);
define('SITE_URL', $protocol . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . BASE_URL . '/aplikasi.php');
define('APP_ROOT_URL', $protocol . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . BASE_URL);

// User Roles (match database ENUM values)
define('ROLE_ADMIN', 'admin');
define('ROLE_STAFF', 'staff');
define('ROLE_RESEARCHER', 'researcher');

// Document Types
define('DOC_TYPE_PENELITIAN', 'penelitian');
define('DOC_TYPE_PENGABDIAN', 'pengabdian');
define('DOC_TYPE_PUBLIKASI', 'publikasi');
define('DOC_TYPE_LAINNYA', 'lainnya');

// Document Categories
define('CAT_PROPOSAL', 'proposal');
define('CAT_REPORT', 'laporan');
define('CAT_OTHER', 'other');

// Document Status
define('STATUS_DRAFT', 'draft');
define('STATUS_SUBMITTED', 'submitted');
define('STATUS_IN_PROGRESS', 'in_progress');
define('STATUS_APPROVED', 'approved');
define('STATUS_REJECTED', 'rejected');
define('STATUS_REVISI', 'revisi');
define('STATUS_COMPLETED', 'completed');

// Approval Stages
define('APPROVAL_STAGE_0', 0); // Draft
define('APPROVAL_STAGE_1', 1); // Dekanat
define('APPROVAL_STAGE_2', 2); // LPPM
