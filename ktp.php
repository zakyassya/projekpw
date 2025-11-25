<?php
include "config.php";
require_login(); // wajib login

$pesan = '';
$errors = [];

$dir_kk = "uploads/kk/";
$dir_foto = "uploads/foto/";

// pastikan folder upload ada
foreach([$dir_kk, $dir_foto] as $d){
    if(!is_dir($d)) mkdir($d, 0755, true);
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    // ambil input
    $nama           = clean_input($_POST['nama']);
    $nik            = clean_input($_POST['nik']);
    $no_kk          = clean_input($_POST['no_kk'] ?? '');
    $tempat_lahir   = clean_input($_POST['tempat_lahir'] ?? '');
    $tanggal_lahir  = clean_input($_POST['tanggal_lahir'] ?? '');
    $jenis_kelamin  = clean_input($_POST['jenis_kelamin'] ?? '');
    $gol_darah      = clean_input($_POST['gol_darah'] ?? '');
    $alamat         = clean_input($_POST['alamat']);
    $agama          = clean_input($_POST['agama'] ?? '');
    $status_kawin   = clean_input($_POST['status_kawin'] ?? '');
    $pekerjaan      = clean_input($_POST['pekerjaan'] ?? '');
    $telepon        = clean_input($_POST['telepon'] ?? '');

    // validasi wajib
    if(!$nama || !$nik || !$alamat) $errors[] = "Nama, NIK, dan Alamat wajib diisi.";
    if(!isset($_FILES['berkas_kk']) || $_FILES['berkas_kk']['error'] !== UPLOAD_ERR_OK)
        $errors[] = "File KK belum dipilih atau terjadi error.";
    if(!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK)
        $errors[] = "File Foto belum dipilih atau terjadi error.";

    // jika tidak ada error → upload file
    if(empty($errors)){
        $resKK = upload_file($_FILES['berkas_kk'], $dir_kk);
        $resFoto = upload_file($_FILES['foto'], $dir_foto);

        if(!$resKK['success']) $errors[] = $resKK['message'];
        if(!$resFoto['success']) $errors[] = $resFoto['message'];

        // simpan ke database jika upload berhasil
        if(empty($errors)){
            $tanggal = date("Y-m-d H:i:s");
            $status = 'pending'; // default enum

            $stmt = mysqli_prepare($conn, "
                INSERT INTO pengajuan_ktp 
                (user_id, nik, no_kk, nama, tempat_lahir, tanggal_lahir, jenis_kelamin, golongan_darah, alamat, 
                 agama, status_kawin, pekerjaan, telepon, file_kk, pas_foto, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            mysqli_stmt_bind_param($stmt, "issssssssssssssss",
                $_SESSION['user_id'], $nik, $no_kk, $nama, $tempat_lahir, $tanggal_lahir, $jenis_kelamin, $gol_darah, $alamat,
                $agama, $status_kawin, $pekerjaan, $telepon, $resKK['filename'], $resFoto['filename'], $status, $tanggal
            );

            if(mysqli_stmt_execute($stmt)){
                $pesan = "Pengajuan KTP berhasil dikirim.";
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
<title>Pengajuan KTP - Kecamatan Digital</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height:100vh; display:flex; align-items:center; justify-content:center; font-family:Arial,sans-serif; padding:20px; }
.card-container { max-width:700px; width:100%; background:white; border-radius:20px; box-shadow:0 10px 40px rgba(0,0,0,0.2); padding:30px; }
input, textarea, select { width:100%; padding:10px; margin:8px 0; border-radius:5px; border:1px solid #ccc; }
button { background: linear-gradient(135deg,#667eea,#764ba2); color:#fff; border:none; padding:12px; border-radius:8px; cursor:pointer; }
button:hover { opacity:0.9; }
.alert-success { background:#e6ffed; border:1px solid #46a069; }
.alert-error { background:#ffe6e6; border:1px solid #d34747; }
</style>
</head>
<body>
<div class="card-container">
<h2 class="mb-4 text-center">Form Pengajuan KTP</h2>

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
    <label>Nama Lengkap</label>
    <input type="text" name="nama" required value="<?= isset($_POST['nama'])?htmlspecialchars($_POST['nama']):'' ?>">

    <label>NIK</label>
    <input type="text" name="nik" required value="<?= isset($_POST['nik'])?htmlspecialchars($_POST['nik']):'' ?>">

    <label>No. KK</label>
    <input type="text" name="no_kk" value="<?= isset($_POST['no_kk'])?htmlspecialchars($_POST['no_kk']):'' ?>">

    <label>Tempat Lahir</label>
    <input type="text" name="tempat_lahir" value="<?= isset($_POST['tempat_lahir'])?htmlspecialchars($_POST['tempat_lahir']):'' ?>">

    <label>Tanggal Lahir</label>
    <input type="date" name="tanggal_lahir" value="<?= isset($_POST['tanggal_lahir'])?htmlspecialchars($_POST['tanggal_lahir']):'' ?>">

    <label>Jenis Kelamin</label>
    <select name="jenis_kelamin">
        <option value="">-- Pilih --</option>
        <option value="L" <?= (isset($_POST['jenis_kelamin']) && $_POST['jenis_kelamin']=='L')?'selected':'' ?>>Laki-laki</option>
        <option value="P" <?= (isset($_POST['jenis_kelamin']) && $_POST['jenis_kelamin']=='P')?'selected':'' ?>>Perempuan</option>
    </select>

    <label>Golongan Darah</label>
    <input type="text" name="gol_darah" value="<?= isset($_POST['gol_darah'])?htmlspecialchars($_POST['gol_darah']):'' ?>">

    <label>Alamat</label>
    <textarea name="alamat" required><?= isset($_POST['alamat'])?htmlspecialchars($_POST['alamat']):'' ?></textarea>

    <label>Agama</label>
    <input type="text" name="agama" value="<?= isset($_POST['agama'])?htmlspecialchars($_POST['agama']):'' ?>">

    <label>Status Kawin</label>
    <input type="text" name="status_kawin" value="<?= isset($_POST['status_kawin'])?htmlspecialchars($_POST['status_kawin']):'' ?>">

    <label>Pekerjaan</label>
    <input type="text" name="pekerjaan" value="<?= isset($_POST['pekerjaan'])?htmlspecialchars($_POST['pekerjaan']):'' ?>">

    <label>Telepon</label>
    <input type="text" name="telepon" value="<?= isset($_POST['telepon'])?htmlspecialchars($_POST['telepon']):'' ?>">

    <label>Upload KK (jpg,jpeg,png,pdf | max 2MB)</label>
    <input type="file" name="berkas_kk" accept=".jpg,.jpeg,.png,.pdf" required>

    <label>Upload Foto (jpg,jpeg,png | max 2MB)</label>
    <input type="file" name="foto" accept=".jpg,.jpeg,.png" required>

    <button type="submit" class="mt-3 w-100">Ajukan KTP</button>
</form>

<p class="mt-3 text-center"><a href="index.php">← Kembali ke Dashboard</a></p>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
