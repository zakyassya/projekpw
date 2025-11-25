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
    <!-- Bootstrap CSS -->
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
        .form-body {
            padding: 30px;
        }
        .form-section {
            margin-bottom: 30px;
        }
        .form-section h5 {
            color: #0d6efd;
            font-weight: 600;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }
        .form-group {
            margin-bottom: 15px;
        }
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
        <!-- Header -->
        <div class="form-header">
            <h2><i class="bi bi-card-heading me-3"></i>Pengajuan KTP Elektronik</h2>
            <p>Lengkapi data pribadi Anda untuk memproses pengajuan KTP</p>
        </div>

        <!-- Form Body -->
        <div class="form-body">
            <!-- Pesan Sukses -->
            <?php if($pesan): ?>
                <div class="alert-success">
                    <i class="bi bi-check-circle me-2"></i>
                    <strong>Berhasil!</strong> <?= htmlspecialchars($pesan) ?>
                </div>
            <?php endif; ?>

            <!-- Error Messages -->
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
                <!-- Data Pribadi -->
                <div class="form-section">
                    <h5><i class="bi bi-person me-2"></i>Data Pribadi</h5>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Nama Lengkap <span class="required">*</span></label>
                            <input type="text" name="nama" class="form-control" required 
                                value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>" 
                                placeholder="Nama lengkap sesuai KK">
                        </div>
                        <div class="form-group">
                            <label>NIK <span class="required">*</span></label>
                            <input type="text" name="nik" class="form-control" required 
                                value="<?= htmlspecialchars($_POST['nik'] ?? '') ?>" 
                                placeholder="16 digit NIK">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>No. Kartu Keluarga</label>
                            <input type="text" name="no_kk" class="form-control" 
                                value="<?= htmlspecialchars($_POST['no_kk'] ?? '') ?>" 
                                placeholder="16 digit KK">
                        </div>
                        <div class="form-group">
                            <label>Jenis Kelamin</label>
                            <select name="jenis_kelamin" class="form-control">
                                <option value="">-- Pilih --</option>
                                <option value="L" <?= (isset($_POST['jenis_kelamin']) && $_POST['jenis_kelamin']=='L') ? 'selected' : '' ?>>Laki-laki</option>
                                <option value="P" <?= (isset($_POST['jenis_kelamin']) && $_POST['jenis_kelamin']=='P') ? 'selected' : '' ?>>Perempuan</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Data Kelahiran -->
                <div class="form-section">
                    <h5><i class="bi bi-calendar me-2"></i>Data Kelahiran</h5>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Tempat Lahir</label>
                            <input type="text" name="tempat_lahir" class="form-control" 
                                value="<?= htmlspecialchars($_POST['tempat_lahir'] ?? '') ?>" 
                                placeholder="Kota/Kabupaten">
                        </div>
                        <div class="form-group">
                            <label>Tanggal Lahir</label>
                            <input type="date" name="tanggal_lahir" class="form-control" 
                                value="<?= htmlspecialchars($_POST['tanggal_lahir'] ?? '') ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Golongan Darah</label>
                            <select name="gol_darah" class="form-control">
                                <option value="">-- Pilih --</option>
                                <option value="O" <?= (isset($_POST['gol_darah']) && $_POST['gol_darah']=='O') ? 'selected' : '' ?>>O</option>
                                <option value="A" <?= (isset($_POST['gol_darah']) && $_POST['gol_darah']=='A') ? 'selected' : '' ?>>A</option>
                                <option value="B" <?= (isset($_POST['gol_darah']) && $_POST['gol_darah']=='B') ? 'selected' : '' ?>>B</option>
                                <option value="AB" <?= (isset($_POST['gol_darah']) && $_POST['gol_darah']=='AB') ? 'selected' : '' ?>>AB</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Agama</label>
                            <input type="text" name="agama" class="form-control" 
                                value="<?= htmlspecialchars($_POST['agama'] ?? '') ?>" 
                                placeholder="Agama">
                        </div>
                    </div>
                </div>

                <!-- Alamat & Kontak -->
                <div class="form-section">
                    <h5><i class="bi bi-house me-2"></i>Alamat & Kontak</h5>
                    <div class="form-group">
                        <label>Alamat <span class="required">*</span></label>
                        <textarea name="alamat" class="form-control" rows="3" required 
                            placeholder="Jl. / No. RT/RW / Desa / Kota"><?= htmlspecialchars($_POST['alamat'] ?? '') ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>No. Telepon</label>
                        <input type="tel" name="telepon" class="form-control" 
                            value="<?= htmlspecialchars($_POST['telepon'] ?? '') ?>" 
                            placeholder="0812345678">
                    </div>
                </div>

                <!-- Status Sosial -->
                <div class="form-section">
                    <h5><i class="bi bi-person-check me-2"></i>Status Sosial</h5>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Status Kawin</label>
                            <select name="status_kawin" class="form-control">
                                <option value="">-- Pilih --</option>
                                <option value="Belum Kawin" <?= (isset($_POST['status_kawin']) && $_POST['status_kawin']=='Belum Kawin') ? 'selected' : '' ?>>Belum Kawin</option>
                                <option value="Kawin" <?= (isset($_POST['status_kawin']) && $_POST['status_kawin']=='Kawin') ? 'selected' : '' ?>>Kawin</option>
                                <option value="Cerai" <?= (isset($_POST['status_kawin']) && $_POST['status_kawin']=='Cerai') ? 'selected' : '' ?>>Cerai</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Pekerjaan</label>
                            <input type="text" name="pekerjaan" class="form-control" 
                                value="<?= htmlspecialchars($_POST['pekerjaan'] ?? '') ?>" 
                                placeholder="Pekerjaan Anda">
                        </div>
                    </div>
                </div>

                <!-- Upload Dokumen -->
                <div class="form-section">
                    <h5><i class="bi bi-file-earmark-arrow-up me-2"></i>Upload Dokumen</h5>
                    <div class="form-group">
                        <label for="berkas_kk">Scan/Foto Kartu Keluarga <span class="required">*</span></label>
                        <div class="file-upload-wrapper">
                            <label for="berkas_kk" class="file-upload-label">
                                <i class="bi bi-cloud-arrow-up"></i>
                                <span>Klik untuk upload atau drag file (JPG, PNG, PDF - Max 2MB)</span>
                            </label>
                            <input type="file" id="berkas_kk" name="berkas_kk" accept=".jpg,.jpeg,.png,.pdf" required>
                        </div>
                        <div class="form-text">Format: JPG, JPEG, PNG, PDF | Ukuran maksimal: 2MB</div>
                    </div>

                    <div class="form-group">
                        <label for="foto">Pas Foto <span class="required">*</span></label>
                        <div class="file-upload-wrapper">
                            <label for="foto" class="file-upload-label">
                                <i class="bi bi-cloud-arrow-up"></i>
                                <span>Klik untuk upload atau drag file (JPG, PNG - Max 2MB)</span>
                            </label>
                            <input type="file" id="foto" name="foto" accept=".jpg,.jpeg,.png" required>
                        </div>
                        <div class="form-text">Format: JPG, JPEG, PNG | Ukuran maksimal: 2MB</div>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="d-flex gap-3">
                    <button type="submit" class="btn-submit">
                        <i class="bi bi-check-circle me-2"></i>Ajukan Permohonan
                    </button>
                    <a href="index.php" class="btn-back">
                        <i class="bi bi-arrow-left"></i>Kembali
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // File drag and drop
        document.querySelectorAll('.file-upload-label').forEach(label => {
            label.addEventListener('dragover', (e) => {
                e.preventDefault();
                label.style.background = '#e9ecef';
            });
            label.addEventListener('dragleave', () => {
                label.style.background = '#f8f9fa';
            });
        });
    </script>
</body>
</html>
