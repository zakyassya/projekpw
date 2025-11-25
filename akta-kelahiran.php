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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: #f8f9fa;
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 25px;
        }
        .form-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            overflow: hidden;
        }
        .form-header {
            background: #0d6efd;
            color: white;
            padding: 30px;
            text-align: center;
        }
        .form-header h2 { margin: 0; font-weight: 600; font-size: 1.8rem; }
        .form-header p { margin: 5px 0 0; opacity: 0.9; font-size: 0.95rem; }
        .form-body { padding: 30px; }
        .form-section { margin-bottom: 30px; }
        .form-section h5 {
            color: #0d6efd;
            font-weight: 600;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }
        .form-group { margin-bottom: 15px; }
        .form-group label {
            font-weight: 500;
            color: #333;
            margin-bottom: 8px;
            display: block;
        }
        .form-control {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 10px 12px;
            font-size: 0.95rem;
            transition: all 0.3s;
        }
        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .form-text { font-size: 0.85rem; color: #666; margin-top: 5px; }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        @media (max-width: 768px) {
            .form-row { grid-template-columns: 1fr; }
        }
        .btn-submit {
            background: #0d6efd;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s;
        }
        .btn-submit:hover {
            opacity: 0.95;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
            color: white;
        }
        .btn-back {
            background: #f8f9fa;
            color: #0d6efd;
            border: 1px solid #ddd;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }
        .btn-back:hover {
            background: #e9ecef;
            color: #0d6efd;
            text-decoration: none;
        }
        .alert-success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            border-radius: 8px;
            padding: 12px 15px;
            margin-bottom: 20px;
        }
        .alert-danger {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            border-radius: 8px;
            padding: 12px 15px;
            margin-bottom: 20px;
        }
        .alert-danger ul { margin: 0; padding-left: 20px; }
        .required { color: #dc3545; }
        .file-upload-wrapper {
            position: relative;
            display: inline-block;
            width: 100%;
        }
        .file-upload-label {
            display: block;
            padding: 12px 15px;
            background: #f8f9fa;
            border: 2px dashed #0d6efd;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        .file-upload-label:hover {
            background: #e9ecef;
            border-color: #0d6efd;
        }
        .file-upload-label i { margin-right: 8px; }
        .form-body input[type="file"] { display: none; }
    </style>
</head>
<body>
    <div class="form-container">
        <div class="form-header">
            <h2><i class="bi bi-file-earmark me-3"></i>Pengajuan Akta Kelahiran</h2>
            <p>Lengkapi data anak untuk pembuatan akta kelahiran</p>
        </div>

        <div class="form-body">
            <?php if($pesan): ?>
                <div class="alert-success">
                    <i class="bi bi-check-circle me-2"></i>
                    <strong>Berhasil!</strong> <?= htmlspecialchars($pesan) ?>
                </div>
            <?php endif; ?>

            <?php if($errors): ?>
                <div class="alert-danger">
                    <strong><i class="bi bi-exclamation-circle me-2"></i>Ada kesalahan:</strong>
                    <ul>
                        <?php foreach($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" novalidate>
                <div class="form-section">
                    <h5><i class="bi bi-person-badge me-2"></i>Data Anak</h5>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Nama Anak <span class="required">*</span></label>
                            <input type="text" name="nama_anak" class="form-control" required 
                                value="<?= htmlspecialchars($_POST['nama_anak'] ?? '') ?>" 
                                placeholder="Nama lengkap anak">
                        </div>
                        <div class="form-group">
                            <label>Jenis Kelamin <span class="required">*</span></label>
                            <select name="jenis_kelamin" class="form-control" required>
                                <option value="">-- Pilih --</option>
                                <option value="L" <?= (isset($_POST['jenis_kelamin']) && $_POST['jenis_kelamin']=='L') ? 'selected' : '' ?>>Laki-laki</option>
                                <option value="P" <?= (isset($_POST['jenis_kelamin']) && $_POST['jenis_kelamin']=='P') ? 'selected' : '' ?>>Perempuan</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h5><i class="bi bi-calendar me-2"></i>Data Kelahiran</h5>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Tempat Lahir <span class="required">*</span></label>
                            <input type="text" name="tempat_lahir" class="form-control" required 
                                value="<?= htmlspecialchars($_POST['tempat_lahir'] ?? '') ?>" 
                                placeholder="Kota/Kabupaten">
                        </div>
                        <div class="form-group">
                            <label>Tanggal Lahir <span class="required">*</span></label>
                            <input type="date" name="tanggal_lahir" class="form-control" required 
                                value="<?= htmlspecialchars($_POST['tanggal_lahir'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Anak Ke <span class="required">*</span></label>
                            <input type="number" name="anak_ke" class="form-control" required 
                                value="<?= htmlspecialchars($_POST['anak_ke'] ?? '') ?>" 
                                placeholder="1, 2, 3, dst">
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h5><i class="bi bi-people me-2"></i>Data Orang Tua</h5>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Nama Ayah <span class="required">*</span></label>
                            <input type="text" name="nama_ayah" class="form-control" required 
                                value="<?= htmlspecialchars($_POST['nama_ayah'] ?? '') ?>" 
                                placeholder="Nama lengkap ayah">
                        </div>
                        <div class="form-group">
                            <label>NIK Ayah <span class="required">*</span></label>
                            <input type="text" name="nik_ayah" class="form-control" required 
                                value="<?= htmlspecialchars($_POST['nik_ayah'] ?? '') ?>" 
                                placeholder="16 digit NIK">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Nama Ibu <span class="required">*</span></label>
                            <input type="text" name="nama_ibu" class="form-control" required 
                                value="<?= htmlspecialchars($_POST['nama_ibu'] ?? '') ?>" 
                                placeholder="Nama lengkap ibu">
                        </div>
                        <div class="form-group">
                            <label>NIK Ibu <span class="required">*</span></label>
                            <input type="text" name="nik_ibu" class="form-control" required 
                                value="<?= htmlspecialchars($_POST['nik_ibu'] ?? '') ?>" 
                                placeholder="16 digit NIK">
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h5><i class="bi bi-house me-2"></i>Alamat & Kontak</h5>
                    <div class="form-group">
                        <label>Alamat <span class="required">*</span></label>
                        <textarea name="alamat" class="form-control" rows="3" required 
                            placeholder="Jl. / No. RT/RW / Desa / Kota"><?= htmlspecialchars($_POST['alamat'] ?? '') ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>No. Telepon <span class="required">*</span></label>
                        <input type="tel" name="telepon" class="form-control" required 
                            value="<?= htmlspecialchars($_POST['telepon'] ?? '') ?>" 
                            placeholder="0812345678">
                    </div>
                </div>

                <div class="form-section">
                    <h5><i class="bi bi-file-earmark-arrow-up me-2"></i>Upload Dokumen</h5>
                    <div class="form-group">
                        <label for="file_surat_lahir">Surat Kelahiran <span class="required">*</span></label>
                        <div class="file-upload-wrapper">
                            <label for="file_surat_lahir" class="file-upload-label">
                                <i class="bi bi-cloud-arrow-up"></i>
                                <span>Klik untuk upload (JPG, PNG, PDF - Max 2MB)</span>
                            </label>
                            <input type="file" id="file_surat_lahir" name="file_surat_lahir" accept=".jpg,.jpeg,.png,.pdf" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="file_kk">Kartu Keluarga <span class="required">*</span></label>
                        <div class="file-upload-wrapper">
                            <label for="file_kk" class="file-upload-label">
                                <i class="bi bi-cloud-arrow-up"></i>
                                <span>Klik untuk upload (JPG, PNG, PDF - Max 2MB)</span>
                            </label>
                            <input type="file" id="file_kk" name="file_kk" accept=".jpg,.jpeg,.png,.pdf" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="file_ktp_ortu">KTP Orang Tua <span class="required">*</span></label>
                        <div class="file-upload-wrapper">
                            <label for="file_ktp_ortu" class="file-upload-label">
                                <i class="bi bi-cloud-arrow-up"></i>
                                <span>Klik untuk upload (JPG, PNG, PDF - Max 2MB)</span>
                            </label>
                            <input type="file" id="file_ktp_ortu" name="file_ktp_ortu" accept=".jpg,.jpeg,.png,.pdf" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="file_surat_nikah">Surat Nikah Orang Tua <span class="required">*</span></label>
                        <div class="file-upload-wrapper">
                            <label for="file_surat_nikah" class="file-upload-label">
                                <i class="bi bi-cloud-arrow-up"></i>
                                <span>Klik untuk upload (JPG, PNG, PDF - Max 2MB)</span>
                            </label>
                            <input type="file" id="file_surat_nikah" name="file_surat_nikah" accept=".jpg,.jpeg,.png,.pdf" required>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-3">
                    <button type="submit" class="btn-submit">
                        <i class="bi bi-check-circle me-2"></i>Ajukan Akta Kelahiran
                    </button>
                    <a href="index.php" class="btn-back">
                        <i class="bi bi-arrow-left"></i>Kembali
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
