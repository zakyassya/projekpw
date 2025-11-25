<?php
include "config.php";
require_login(); // wajib login

$pesan = '';
$errors = [];

$dir_ktp_kepala = "uploads/ktp_kepala/";
$dir_kk_lama     = "uploads/kk_lama/";
$dir_surat_nikah = "uploads/surat_nikah/";

// pastikan folder upload ada
foreach([$dir_ktp_kepala, $dir_kk_lama, $dir_surat_nikah] as $d){
    if(!is_dir($d)) mkdir($d, 0755, true);
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    // ambil data dari form
    $no_kk_lama           = clean_input($_POST['no_kk_lama']);
    $nama_kepala_keluarga = clean_input($_POST['nama_kepala_keluarga']);
    $alamat               = clean_input($_POST['alamat']);
    $rt                   = clean_input($_POST['rt']);
    $rw                   = clean_input($_POST['rw']);
    $kelurahan            = clean_input($_POST['kelurahan']);
    $kecamatan            = clean_input($_POST['kecamatan']);
    $telepon              = clean_input($_POST['telepon']);
    $jenis_permohonan     = clean_input($_POST['jenis_permohonan']);
    $alasan               = clean_input($_POST['alasan']);

    // validasi
    if(!$nama_kepala_keluarga || !$alamat || !$rt || !$rw || !$kelurahan || !$kecamatan || !$telepon || !$jenis_permohonan){
        $errors[] = "Semua field wajib diisi.";
    }

    if(!isset($_FILES['file_ktp_kepala']) || $_FILES['file_ktp_kepala']['error'] !== UPLOAD_ERR_OK)
        $errors[] = "File KTP kepala keluarga belum dipilih atau terjadi error.";

    if(!isset($_FILES['file_kk_lama']) || $_FILES['file_kk_lama']['error'] !== UPLOAD_ERR_OK)
        $errors[] = "File KK lama belum dipilih atau terjadi error.";

    if(!isset($_FILES['file_surat_nikah']) || $_FILES['file_surat_nikah']['error'] !== UPLOAD_ERR_OK)
        $errors[] = "File surat nikah belum dipilih atau terjadi error.";

    // jika tidak ada error → upload file
    if(empty($errors)){
        $resKTP   = upload_file($_FILES['file_ktp_kepala'], $dir_ktp_kepala);
        $resKK    = upload_file($_FILES['file_kk_lama'], $dir_kk_lama);
        $resNikah = upload_file($_FILES['file_surat_nikah'], $dir_surat_nikah);

        if(!$resKTP['success']) $errors[] = $resKTP['message'];
        if(!$resKK['success']) $errors[] = $resKK['message'];
        if(!$resNikah['success']) $errors[] = $resNikah['message'];

        if(empty($errors)){
            $stmt = mysqli_prepare($conn, "
                INSERT INTO pengajuan_kk 
                (user_id, no_kk_lama, nama_kepala_keluarga, alamat, rt, rw, kelurahan, kecamatan, telepon, jenis_permohonan, alasan, file_ktp_kepala, file_kk_lama, file_surat_nikah)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            mysqli_stmt_bind_param($stmt, "isssssssssssss",
                $_SESSION['user_id'],
                $no_kk_lama,
                $nama_kepala_keluarga,
                $alamat,
                $rt,
                $rw,
                $kelurahan,
                $kecamatan,
                $telepon,
                $jenis_permohonan,
                $alasan,
                $resKTP['filename'],
                $resKK['filename'],
                $resNikah['filename']
            );

            if(mysqli_stmt_execute($stmt)){
                $pesan = "Pengajuan KK berhasil dikirim.";
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
<title>Pengajuan KK - Kecamatan Digital</title>
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
    <h2 class="mb-4 text-center">Form Pengajuan Kartu Keluarga</h2>

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
        <label>No. KK Lama</label>
        <input type="text" name="no_kk_lama" value="<?= isset($_POST['no_kk_lama'])?htmlspecialchars($_POST['no_kk_lama']):'' ?>">

        <label>Nama Kepala Keluarga</label>
        <input type="text" name="nama_kepala_keluarga" value="<?= isset($_POST['nama_kepala_keluarga'])?htmlspecialchars($_POST['nama_kepala_keluarga']):'' ?>">

        <label>Alamat</label>
        <textarea name="alamat"><?= isset($_POST['alamat'])?htmlspecialchars($_POST['alamat']):'' ?></textarea>

        <label>RT</label>
        <input type="text" name="rt" value="<?= isset($_POST['rt'])?htmlspecialchars($_POST['rt']):'' ?>">
        <label>RW</label>
        <input type="text" name="rw" value="<?= isset($_POST['rw'])?htmlspecialchars($_POST['rw']):'' ?>">

        <label>Kelurahan</label>
        <input type="text" name="kelurahan" value="<?= isset($_POST['kelurahan'])?htmlspecialchars($_POST['kelurahan']):'' ?>">
        <label>Kecamatan</label>
        <input type="text" name="kecamatan" value="<?= isset($_POST['kecamatan'])?htmlspecialchars($_POST['kecamatan']):'' ?>">

        <label>Telepon</label>
        <input type="text" name="telepon" value="<?= isset($_POST['telepon'])?htmlspecialchars($_POST['telepon']):'' ?>">

        <label>Jenis Permohonan</label>
        <select name="jenis_permohonan">
            <option value="">--Pilih--</option>
            <option value="baru" <?= (isset($_POST['jenis_permohonan']) && $_POST['jenis_permohonan']=='baru')?'selected':'' ?>>Baru</option>
            <option value="perubahan" <?= (isset($_POST['jenis_permohonan']) && $_POST['jenis_permohonan']=='perubahan')?'selected':'' ?>>Perubahan</option>
            <option value="hilang" <?= (isset($_POST['jenis_permohonan']) && $_POST['jenis_permohonan']=='hilang')?'selected':'' ?>>Hilang</option>
        </select>

        <label>Alasan</label>
        <textarea name="alasan"><?= isset($_POST['alasan'])?htmlspecialchars($_POST['alasan']):'' ?></textarea>

        <label>Upload KTP Kepala Keluarga (jpg,jpeg,png,pdf | max 2MB)</label>
        <input type="file" name="file_ktp_kepala" accept=".jpg,.jpeg,.png,.pdf">

        <label>Upload KK Lama (jpg,jpeg,png,pdf | max 2MB)</label>
        <input type="file" name="file_kk_lama" accept=".jpg,.jpeg,.png,.pdf">

        <label>Upload Surat Nikah (jpg,jpeg,png,pdf | max 2MB)</label>
        <input type="file" name="file_surat_nikah" accept=".jpg,.jpeg,.png,.pdf">

        <button type="submit" class="mt-3 w-100">Ajukan KK</button>
    </form>

    <p class="mt-3 text-center"><a href="index.php">← Kembali ke Dashboard</a></p>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
