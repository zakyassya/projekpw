<?php
include "../config.php";

// Pastikan user adalah admin
if (!is_admin()) {
    header("Location: ../login.php");
    exit("Akses ditolak");
}

// Ambil parameter
$id  = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$tbl = isset($_GET['tbl']) ? $_GET['tbl'] : '';

// Sanitasi nama tabel
$tbl = preg_replace('/[^a-z_]/', '', $tbl);

// Daftar tabel yang diizinkan
$allowed_tables = [
    'pengajuan_ktp',
    'pengajuan_kk',
    'pengajuan_pindah',
    'pengajuan_domisili',
    'pengajuan_akta',
    'pengajuan_usaha'
];

// Validasi
if (!$id || empty($tbl) || !in_array($tbl, $allowed_tables)) {
    header("Location: index.php?error=Invalid request");
    exit();
}

// Ambil data pengajuan untuk mendapatkan file yang perlu dihapus
$sql = "SELECT * FROM $tbl WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);

if (!$row) {
    header("Location: index.php?error=Data tidak ditemukan");
    exit();
}

// Hapus file-file yang terkait jika ada
$upload_fields = [
    'pengajuan_ktp' => ['ktp_file', 'ktp_kepala_file'],
    'pengajuan_kk' => ['kk_file', 'kk_lama_file'],
    'pengajuan_pindah' => ['surat_pengantar_file'],
    'pengajuan_domisili' => ['surat_domisili_file'],
    'pengajuan_akta' => ['surat_lahir_file', 'surat_nikah_file'],
    'pengajuan_usaha' => ['surat_usaha_file', 'foto_usaha_file', 'foto_file']
];

if (isset($upload_fields[$tbl])) {
    foreach ($upload_fields[$tbl] as $field) {
        if (!empty($row[$field])) {
            $file_path = "../uploads/" . $row[$field];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
    }
}

// Hapus data dari database
$delete_sql = "DELETE FROM $tbl WHERE id = ?";
$delete_stmt = mysqli_prepare($conn, $delete_sql);
mysqli_stmt_bind_param($delete_stmt, "i", $id);

if (mysqli_stmt_execute($delete_stmt)) {
    header("Location: index.php?success=Data berhasil dihapus");
} else {
    header("Location: index.php?error=Gagal menghapus data");
}
exit();
