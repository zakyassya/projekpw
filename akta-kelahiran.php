<?php
include "config.php";
require_login(); // wajib login

$pesan = '';
$errors = [];

$dir_surat_lahir = "uploads/surat_lahir/";
$dir_kk          = "uploads/kk/";
$dir_ktp_ortu    = "uploads/ktp_ortu/";
$dir_surat_nikah = "uploads/surat_nikah/";

// pastikan folder upload ada
foreach([$dir_surat_lahir, $dir_kk, $dir_ktp_ortu, $dir_surat_nikah] as $d){
    if(!is_dir($d)) mkdir($d, 0755, true);
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    // ambil data dari form
    $nama_anak    = clean_input($_POST['nama_anak']);
    $tempat_lahir = clean_input($_POST['tempat_lahir']);
    $tanggal_lahir= clean_input($_POST['tanggal_lahir']);
    $jenis_kelamin= clean_input($_POST['jenis_kelamin']);
    $anak_ke      = clean_input($_POST['anak_ke']);
    $nama_ayah    = clean_input($_POST['nama_ayah']);
    $nik_ayah     = clean_input($_POST['nik_ayah']);
    $nama_ibu     = clean_input($_POST['nama_ibu']);
    $nik_ibu      = clean_input($_POST['nik_ibu']);
    $alamat       = clean_input($_POST['alamat']);
    $telepon      = clean_input($_POST['telepon']);

    // validasi
    if(!$nama_anak || !$tempat_lahir || !$tanggal_lahir || !$jenis_kelamin || !$anak_ke || !$nama_ayah || !$nik_ayah || !$nama_ibu || !$nik_ibu || !$alamat || !$telepon){
        $errors[] = "Semua field wajib diisi.";
    }

    // validasi file
    $required_files = [
        'file_surat_lahir' => $dir_surat_lahir,
        'file_kk'          => $dir_kk,
        'file_ktp_ortu'    => $dir_ktp_ortu,
        'file_surat_nikah' => $dir_surat_nikah
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
                INSERT INTO pengajuan_akta
                (user_id, nama_anak, tempat_lahir, tanggal_lahir, jenis_kelamin, anak_ke, nama_ayah, nik_ayah, nama_ibu, nik_ibu, alamat, telepon, file_surat_lahir, file_kk, file_ktp_ortu, file_surat_nikah)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            mysqli_stmt_bind_param($stmt, "issssissssssssss",
                $_SESSION['user_id'],
                $nama_anak,
                $tempat_lahir,
                $tanggal_lahir,
                $jenis_kelamin,
                $anak_ke,
                $nama_ayah,
                $nik_ayah,
                $nama_ibu,
                $nik_ibu,
                $alamat,
                $telepon,
                $resFiles['file_surat_lahir']['filename'],
                $resFiles['file_kk']['filename'],
                $resFiles['file_ktp_ortu']['filename'],
                $resFiles['file_surat_nikah']['filename']
            );

            if(mysqli_stmt_execute($stmt)){
                $pesan = "Pengajuan Akta Kelahiran berhasil dikirim.";
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
<title>Pengajuan Akta Kelahiran - Kecamatan Digital</title>
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
    <h2 class="mb-4 text-center">Form Pengajuan Akta Kelahiran</h2>

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
        <label>Nama Anak</label>
        <input type="text" name="nama_anak" value="<?= isset($_POST['nama_anak'])?htmlspecialchars($_POST['nama_anak']):'' ?>">

        <label>Tempat Lahir</label>
        <input type="text" name="tempat_lahir" value="<?= isset($_POST['tempat_lahir'])?htmlspecialchars($_POST['tempat_lahir']):'' ?>">

        <label>Tanggal Lahir</label>
        <input type="date" name="tanggal_lahir" value="<?= isset($_POST['tanggal_lahir'])?htmlspecialchars($_POST['tanggal_lahir']):'' ?>">

        <label>Jenis Kelamin</label>
        <select name="jenis_kelamin">
            <option value="">--Pilih--</option>
            <option value="L" <?= (isset($_POST['jenis_kelamin']) && $_POST['jenis_kelamin']=='L')?'selected':'' ?>>Laki-laki</option>
            <option value="P" <?= (isset($_POST['jenis_kelamin']) && $_POST['jenis_kelamin']=='P')?'selected':'' ?>>Perempuan</option>
        </select>

        <label>Anak Ke</label>
        <input type="number" name="anak_ke" value="<?= isset($_POST['anak_ke'])?htmlspecialchars($_POST['anak_ke']):'' ?>">

        <label>Nama Ayah</label>
        <input type="text" name="nama_ayah" value="<?= isset($_POST['nama_ayah'])?htmlspecialchars($_POST['nama_ayah']):'' ?>">

        <label>NIK Ayah</label>
        <input type="text" name="nik_ayah" value="<?= isset($_POST['nik_ayah'])?htmlspecialchars($_POST['nik_ayah']):'' ?>">

        <label>Nama Ibu</label>
        <input type="text" name="nama_ibu" value="<?= isset($_POST['nama_ibu'])?htmlspecialchars($_POST['nama_ibu']):'' ?>">

        <label>NIK Ibu</label>
        <input type="text" name="nik_ibu" value="<?= isset($_POST['nik_ibu'])?htmlspecialchars($_POST['nik_ibu']):'' ?>">

        <label>Alamat</label>
        <textarea name="alamat"><?= isset($_POST['alamat'])?htmlspecialchars($_POST['alamat']):'' ?></textarea>

        <label>Telepon</label>
        <input type="text" name="telepon" value="<?= isset($_POST['telepon'])?htmlspecialchars($_POST['telepon']):'' ?>">

        <label>Upload Surat Kelahiran (jpg,jpeg,png,pdf | max 2MB)</label>
        <input type="file" name="file_surat_lahir" accept=".jpg,.jpeg,.png,.pdf">

        <label>Upload KK (jpg,jpeg,png,pdf | max 2MB)</label>
        <input type="file" name="file_kk" accept=".jpg,.jpeg,.png,.pdf">

        <label>Upload KTP Orang Tua (jpg,jpeg,png,pdf | max 2MB)</label>
        <input type="file" name="file_ktp_ortu" accept=".jpg,.jpeg,.png,.pdf">

        <label>Upload Surat Nikah (jpg,jpeg,png,pdf | max 2MB)</label>
        <input type="file" name="file_surat_nikah" accept=".jpg,.jpeg,.png,.pdf">

        <button type="submit" class="mt-3 w-100">Ajukan Akta Kelahiran</button>
    </form>

    <p class="mt-3 text-center"><a href="index.php">← Kembali ke Dashboard</a></p>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
