<?php
include "../config.php";

// Pastikan user adalah admin
if (!is_admin()) {
    exit("Akses ditolak");
}

// Ambil parameter
$file = isset($_GET['file']) ? $_GET['file'] : '';

if (empty($file)) {
    exit("File tidak ditemukan");
}

// Sanitasi path untuk keamanan - cegah directory traversal
$file = basename($file);

// Daftar folder uploads yang diizinkan
$allowed_dirs = [
    'ktp',
    'kk',
    'surat_lahir',
    'ktp_kepala',
    'kk_lama',
    'surat_nikah',
    'ktp_ortu',
    'foto',
    'foto_usaha',
    'surat_pengantar',
    'surat_pernyataan'
];

// Cari file di folder-folder uploads
$file_path = null;
foreach ($allowed_dirs as $dir) {
    $test_path = "../uploads/" . $dir . "/" . $file;
    if (file_exists($test_path) && is_file($test_path)) {
        $file_path = $test_path;
        break;
    }
}

// Jika file tidak ditemukan
if (!$file_path || !file_exists($file_path)) {
    exit("File tidak ditemukan: " . htmlspecialchars($file));
}

// Download file
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($file_path));

readfile($file_path);
exit();
