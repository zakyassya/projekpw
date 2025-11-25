<?php
include "config.php";
require_login(); // wajib login

$pesan = '';
$errors = [];

$dir_ktp           = "uploads/ktp/";
$dir_kk            = "uploads/kk/";
$dir_foto_usaha    = "uploads/foto_usaha/";
$dir_surat_pernyataan = "uploads/surat_pernyataan/";

// pastikan folder upload ada
foreach([$dir_ktp, $dir_kk, $dir_foto_usaha, $dir_surat_pernyataan] as $d){
    if(!is_dir($d)) mkdir($d, 0755, true);
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    // ambil data dari form
    $nik           = clean_input($_POST['nik']);
    $nama          = clean_input($_POST['nama']);
    $nama_usaha    = clean_input($_POST['nama_usaha']);
    $jenis_usaha   = clean_input($_POST['jenis_usaha']);
    $alamat_usaha  = clean_input($_POST['alamat_usaha']);
    $rt            = clean_input($_POST['rt']);
    $rw            = clean_input($_POST['rw']);
    $modal_usaha   = clean_input($_POST['modal_usaha']);
    $jumlah_karyawan = clean_input($_POST['jumlah_karyawan']);
    $telepon       = clean_input($_POST['telepon']);

    // validasi wajib
    if(!$nik || !$nama || !$nama_usaha || !$jenis_usaha || !$alamat_usaha || !$rt || !$rw || !$telepon){
        $errors[] = "Semua field wajib diisi kecuali modal usaha dan jumlah karyawan.";
    }

    // validasi file
    $required_files = [
        'file_ktp'             => $dir_ktp,
        'file_kk'              => $dir_kk,
        'file_foto_usaha'      => $dir_foto_usaha,
        'file_surat_pernyataan'=> $dir_surat_pernyataan
    ];

    foreach($required_files as $f=>$dir){
        if(!isset($_FILES[$f]) || $_FILES[$f]['error'] !== UPLOAD_ERR_OK){
            $errors[] = "File ".str_replace("_"," ",$f)." belum dipilih atau terjadi error.";
        }
    }

    if(empty($errors)){
        $resFiles = [];
        foreach($required_files as $f=>$dir){
            $resFiles[$f] = upload_file($_FILES[$f], $dir);
            if(!$resFiles[$f]['success']) $errors[] = $resFiles[$f]['message'];
        }

        if(empty($errors)){
            $stmt = mysqli_prepare($conn, "
                INSERT INTO pengajuan_usaha
                (user_id, nik, nama, nama_usaha, jenis_usaha, alamat_usaha, rt, rw, modal_usaha, jumlah_karyawan, telepon, file_ktp, file_kk, file_foto_usaha, file_surat_pernyataan)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            mysqli_stmt_bind_param($stmt, "issssssssisssss",
                $_SESSION['user_id'],
                $nik,
                $nama,
                $nama_usaha,
                $jenis_usaha,
                $alamat_usaha,
                $rt,
                $rw,
                $modal_usaha ? $modal_usaha : null,
                $jumlah_karyawan ? $jumlah_karyawan : null,
                $telepon,
                $resFiles['file_ktp']['filename'],
                $resFiles['file_kk']['filename'],
                $resFiles['file_foto_usaha']['filename'],
                $resFiles['file_surat_pernyataan']['filename']
            );

            if(mysqli_stmt_execute($stmt)){
                $pesan = "Pengajuan Surat Usaha berhasil dikirim.";
            } else {
                $errors[] = "Gagal menyimpan ke database: ".mysqli_error($conn);
            }
            mysqli_stmt_close($stmt);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pengajuan Surat Usaha - Kecamatan Digital</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    font-family: Arial, sans-serif;
}
.card-container {
    max-width: 800px;
    width: 100%;
    background: white;
    border-radius: 20px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
    padding: 30px;
}
input, textarea, select {
    width: 100%;
    padding: 10px;
    margin: 8px 0;
    border-radius: 5px;
    border:1px solid #ccc;
}
button {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: #fff;
    border: none;
    padding: 12px;
    border-radius: 8px;
    cursor: pointer;
}
button:hover {opacity:0.9;}
.alert-success {background:#e6ffed;border:1px solid #46a069;}
.alert-error {background:#ffe6e6;border:1px solid #d34747;}
</style>
</head>
<body>
<div class="card-container">
    <h2 class="mb-4 text-center">Form Pengajuan Surat Usaha</h2>

    <?php if($pesan): ?>
        <div class="alert alert-success"><?= htmlspecialchars($pesan) ?></div>
    <?php endif; ?>

    <?php if($errors): ?>
        <div class="alert alert-error">
            <ul class="mb-0">
                <?php foreach($errors as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" novalidate>
        <label>NIK</label>
        <input type="text" name="nik" value="<?= isset($_POST['nik'])?htmlspecialchars($_POST['nik']):'' ?>">

        <label>Nama</label>
        <input type="text" name="nama" value="<?= isset($_POST['nama'])?htmlspecialchars($_POST['nama']):'' ?>">

        <label>Nama Usaha</label>
        <input type="text" name="nama_usaha" value="<?= isset($_POST['nama_usaha'])?htmlspecialchars($_POST['nama_usaha']):'' ?>">

        <label>Jenis Usaha</label>
        <input type="text" name="jenis_usaha" value="<?= isset($_POST['jenis_usaha'])?htmlspecialchars($_POST['jenis_usaha']):'' ?>">

        <label>Alamat Usaha</label>
        <textarea name="alamat_usaha"><?= isset($_POST['alamat_usaha'])?htmlspecialchars($_POST['alamat_usaha']):'' ?></textarea>

        <label>RT</label>
        <input type="text" name="rt" value="<?= isset($_POST['rt'])?htmlspecialchars($_POST['rt']):'' ?>">

        <label>RW</label>
        <input type="text" name="rw" value="<?= isset($_POST['rw'])?htmlspecialchars($_POST['rw']):'' ?>">

        <label>Modal Usaha (opsional)</label>
        <input type="number" name="modal_usaha" value="<?= isset($_POST['modal_usaha'])?htmlspecialchars($_POST['modal_usaha']):'' ?>">

        <label>Jumlah Karyawan (opsional)</label>
        <input type="number" name="jumlah_karyawan" value="<?= isset($_POST['jumlah_karyawan'])?htmlspecialchars($_POST['jumlah_karyawan']):'' ?>">

        <label>Telepon</label>
        <input type="text" name="telepon" value="<?= isset($_POST['telepon'])?htmlspecialchars($_POST['telepon']):'' ?>">

        <label>Upload KTP (jpg,jpeg,png,pdf | max 2MB)</label>
        <input type="file" name="file_ktp" accept=".jpg,.jpeg,.png,.pdf">

        <label>Upload KK (jpg,jpeg,png,pdf | max 2MB)</label>
        <input type="file" name="file_kk" accept=".jpg,.jpeg,.png,.pdf">

        <label>Upload Foto Usaha (jpg,jpeg,png,pdf | max 2MB)</label>
        <input type="file" name="file_foto_usaha" accept=".jpg,.jpeg,.png,.pdf">

        <label>Upload Surat Pernyataan (jpg,jpeg,png,pdf | max 2MB)</label>
        <input type="file" name="file_surat_pernyataan" accept=".jpg,.jpeg,.png,.pdf">

        <button type="submit" class="mt-3 w-100">Ajukan Surat Usaha</button>
    </form>

    <p class="mt-3 text-center"><a href="index.php">← Kembali ke Dashboard</a></p>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
