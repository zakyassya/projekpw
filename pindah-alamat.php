<?php
include "config.php";
require_login(); // wajib login

$pesan = '';
$errors = [];

$dir_ktp           = "uploads/ktp/";
$dir_kk            = "uploads/kk/";
$dir_surat_pengantar= "uploads/surat_pengantar/";

// pastikan folder upload ada
foreach([$dir_ktp, $dir_kk, $dir_surat_pengantar] as $d){
    if(!is_dir($d)) mkdir($d, 0755, true);
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    // ambil data dari form
    $nik              = clean_input($_POST['nik']);
    $no_kk            = clean_input($_POST['no_kk']);
    $nama             = clean_input($_POST['nama']);
    $alamat_asal      = clean_input($_POST['alamat_asal']);
    $alamat_tujuan    = clean_input($_POST['alamat_tujuan']);
    $rt_tujuan        = clean_input($_POST['rt_tujuan']);
    $rw_tujuan        = clean_input($_POST['rw_tujuan']);
    $kelurahan_tujuan = clean_input($_POST['kelurahan_tujuan']);
    $kecamatan_tujuan = clean_input($_POST['kecamatan_tujuan']);
    $alasan_pindah    = clean_input($_POST['alasan_pindah']);
    $telepon          = clean_input($_POST['telepon']);

    // validasi
    if(!$nik || !$no_kk || !$nama || !$alamat_asal || !$alamat_tujuan || !$rt_tujuan || !$rw_tujuan || !$kelurahan_tujuan || !$kecamatan_tujuan || !$alasan_pindah || !$telepon){
        $errors[] = "Semua field wajib diisi.";
    }

    // validasi file
    $required_files = [
        'file_ktp'           => $dir_ktp,
        'file_kk'            => $dir_kk,
        'file_surat_pengantar'=> $dir_surat_pengantar
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
                INSERT INTO pengajuan_pindah
                (user_id, nik, no_kk, nama, alamat_asal, alamat_tujuan, rt_tujuan, rw_tujuan, kelurahan_tujuan, kecamatan_tujuan, alasan_pindah, telepon, file_ktp, file_kk, file_surat_pengantar)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            mysqli_stmt_bind_param($stmt, "issssssssssssss",
                $_SESSION['user_id'],
                $nik,
                $no_kk,
                $nama,
                $alamat_asal,
                $alamat_tujuan,
                $rt_tujuan,
                $rw_tujuan,
                $kelurahan_tujuan,
                $kecamatan_tujuan,
                $alasan_pindah,
                $telepon,
                $resFiles['file_ktp']['filename'],
                $resFiles['file_kk']['filename'],
                $resFiles['file_surat_pengantar']['filename']
            );

            if(mysqli_stmt_execute($stmt)){
                $pesan = "Pengajuan Surat Pindah Alamat berhasil dikirim.";
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
<title>Pengajuan Surat Pindah Alamat - Kecamatan Digital</title>
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
    <h2 class="mb-4 text-center">Form Pengajuan Surat Pindah Alamat</h2>

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

        <label>No. KK</label>
        <input type="text" name="no_kk" value="<?= isset($_POST['no_kk'])?htmlspecialchars($_POST['no_kk']):'' ?>">

        <label>Nama</label>
        <input type="text" name="nama" value="<?= isset($_POST['nama'])?htmlspecialchars($_POST['nama']):'' ?>">

        <label>Alamat Asal</label>
        <textarea name="alamat_asal"><?= isset($_POST['alamat_asal'])?htmlspecialchars($_POST['alamat_asal']):'' ?></textarea>

        <label>Alamat Tujuan</label>
        <textarea name="alamat_tujuan"><?= isset($_POST['alamat_tujuan'])?htmlspecialchars($_POST['alamat_tujuan']):'' ?></textarea>

        <label>RT Tujuan</label>
        <input type="text" name="rt_tujuan" value="<?= isset($_POST['rt_tujuan'])?htmlspecialchars($_POST['rt_tujuan']):'' ?>">

        <label>RW Tujuan</label>
        <input type="text" name="rw_tujuan" value="<?= isset($_POST['rw_tujuan'])?htmlspecialchars($_POST['rw_tujuan']):'' ?>">

        <label>Kelurahan Tujuan</label>
        <input type="text" name="kelurahan_tujuan" value="<?= isset($_POST['kelurahan_tujuan'])?htmlspecialchars($_POST['kelurahan_tujuan']):'' ?>">

        <label>Kecamatan Tujuan</label>
        <input type="text" name="kecamatan_tujuan" value="<?= isset($_POST['kecamatan_tujuan'])?htmlspecialchars($_POST['kecamatan_tujuan']):'' ?>">

        <label>Alasan Pindah</label>
        <textarea name="alasan_pindah"><?= isset($_POST['alasan_pindah'])?htmlspecialchars($_POST['alasan_pindah']):'' ?></textarea>

        <label>Telepon</label>
        <input type="text" name="telepon" value="<?= isset($_POST['telepon'])?htmlspecialchars($_POST['telepon']):'' ?>">

        <label>Upload KTP (jpg,jpeg,png,pdf | max 2MB)</label>
        <input type="file" name="file_ktp" accept=".jpg,.jpeg,.png,.pdf">

        <label>Upload KK (jpg,jpeg,png,pdf | max 2MB)</label>
        <input type="file" name="file_kk" accept=".jpg,.jpeg,.png,.pdf">

        <label>Upload Surat Pengantar (jpg,jpeg,png,pdf | max 2MB)</label>
        <input type="file" name="file_surat_pengantar" accept=".jpg,.jpeg,.png,.pdf">

        <button type="submit" class="mt-3 w-100">Ajukan Surat Pindah Alamat</button>
    </form>

    <p class="mt-3 text-center"><a href="index.php">← Kembali ke Dashboard</a></p>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
