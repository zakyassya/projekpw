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
    <title>Pengajuan Kartu Keluarga - Kecamatan Digital</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .form-header h2 { margin: 0; font-weight: 600; font-size: 1.8rem; }
        .form-header p { margin: 5px 0 0; opacity: 0.9; font-size: 0.95rem; }
        .form-body { padding: 30px; }
        .form-section {
            margin-bottom: 30px;
        }
        .form-section h5 {
            color: #667eea;
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
            border-color: #667eea;
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
            background: linear-gradient(135deg, #667eea, #764ba2);
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
            color: #667eea;
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
            color: #667eea;
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
            border: 2px dashed #667eea;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        .file-upload-label:hover {
            background: #e9ecef;
            border-color: #764ba2;
        }
        .file-upload-label i { margin-right: 8px; }
        .form-body input[type="file"] { display: none; }
    </style>
</head>
<body>
    <div class="form-container">
        <div class="form-header">
            <h2><i class="bi bi-people me-3"></i>Pengajuan Kartu Keluarga</h2>
            <p>Lengkapi data untuk permohonan Kartu Keluarga baru atau perubahan</p>
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
                    <h5><i class="bi bi-person me-2"></i>Data Kepala Keluarga</h5>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Nama Kepala Keluarga <span class="required">*</span></label>
                            <input type="text" name="nama_kepala_keluarga" class="form-control" required 
                                value="<?= htmlspecialchars($_POST['nama_kepala_keluarga'] ?? '') ?>" 
                                placeholder="Nama lengkap kepala keluarga">
                        </div>
                        <div class="form-group">
                            <label>No. KK Lama</label>
                            <input type="text" name="no_kk_lama" class="form-control" 
                                value="<?= htmlspecialchars($_POST['no_kk_lama'] ?? '') ?>" 
                                placeholder="Jika ada KK lama">
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h5><i class="bi bi-house me-2"></i>Alamat</h5>
                    <div class="form-group">
                        <label>Alamat <span class="required">*</span></label>
                        <textarea name="alamat" class="form-control" rows="3" required 
                            placeholder="Jl. / No. RT/RW / Desa / Kota"><?= htmlspecialchars($_POST['alamat'] ?? '') ?></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>RT <span class="required">*</span></label>
                            <input type="text" name="rt" class="form-control" required 
                                value="<?= htmlspecialchars($_POST['rt'] ?? '') ?>" 
                                placeholder="00">
                        </div>
                        <div class="form-group">
                            <label>RW <span class="required">*</span></label>
                            <input type="text" name="rw" class="form-control" required 
                                value="<?= htmlspecialchars($_POST['rw'] ?? '') ?>" 
                                placeholder="00">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Kelurahan <span class="required">*</span></label>
                            <input type="text" name="kelurahan" class="form-control" required 
                                value="<?= htmlspecialchars($_POST['kelurahan'] ?? '') ?>" 
                                placeholder="Nama kelurahan">
                        </div>
                        <div class="form-group">
                            <label>Kecamatan <span class="required">*</span></label>
                            <input type="text" name="kecamatan" class="form-control" required 
                                value="<?= htmlspecialchars($_POST['kecamatan'] ?? '') ?>" 
                                placeholder="Nama kecamatan">
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h5><i class="bi bi-telephone me-2"></i>Kontak & Permohonan</h5>
                    <div class="form-group">
                        <label>No. Telepon <span class="required">*</span></label>
                        <input type="tel" name="telepon" class="form-control" required 
                            value="<?= htmlspecialchars($_POST['telepon'] ?? '') ?>" 
                            placeholder="0812345678">
                    </div>
                    <div class="form-group">
                        <label>Jenis Permohonan <span class="required">*</span></label>
                        <select name="jenis_permohonan" class="form-control" required>
                            <option value="">-- Pilih --</option>
                            <option value="baru" <?= (isset($_POST['jenis_permohonan']) && $_POST['jenis_permohonan']=='baru') ? 'selected' : '' ?>>Baru</option>
                            <option value="perubahan" <?= (isset($_POST['jenis_permohonan']) && $_POST['jenis_permohonan']=='perubahan') ? 'selected' : '' ?>>Perubahan</option>
                            <option value="hilang" <?= (isset($_POST['jenis_permohonan']) && $_POST['jenis_permohonan']=='hilang') ? 'selected' : '' ?>>Hilang</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Alasan Permohonan</label>
                        <textarea name="alasan" class="form-control" rows="3" 
                            placeholder="Jelaskan alasan permohonan"><?= htmlspecialchars($_POST['alasan'] ?? '') ?></textarea>
                    </div>
                </div>

                <div class="form-section">
                    <h5><i class="bi bi-file-earmark-arrow-up me-2"></i>Upload Dokumen</h5>
                    <div class="form-group">
                        <label for="file_ktp_kepala">KTP Kepala Keluarga <span class="required">*</span></label>
                        <div class="file-upload-wrapper">
                            <label for="file_ktp_kepala" class="file-upload-label">
                                <i class="bi bi-cloud-arrow-up"></i>
                                <span>Klik untuk upload (JPG, PNG, PDF - Max 2MB)</span>
                            </label>
                            <input type="file" id="file_ktp_kepala" name="file_ktp_kepala" accept=".jpg,.jpeg,.png,.pdf" required>
                        </div>
                        <div class="form-text">Format: JPG, JPEG, PNG, PDF | Ukuran maksimal: 2MB</div>
                    </div>

                    <div class="form-group">
                        <label for="file_kk_lama">Kartu Keluarga Lama <span class="required">*</span></label>
                        <div class="file-upload-wrapper">
                            <label for="file_kk_lama" class="file-upload-label">
                                <i class="bi bi-cloud-arrow-up"></i>
                                <span>Klik untuk upload (JPG, PNG, PDF - Max 2MB)</span>
                            </label>
                            <input type="file" id="file_kk_lama" name="file_kk_lama" accept=".jpg,.jpeg,.png,.pdf" required>
                        </div>
                        <div class="form-text">Format: JPG, JPEG, PNG, PDF | Ukuran maksimal: 2MB</div>
                    </div>

                    <div class="form-group">
                        <label for="file_surat_nikah">Surat Nikah <span class="required">*</span></label>
                        <div class="file-upload-wrapper">
                            <label for="file_surat_nikah" class="file-upload-label">
                                <i class="bi bi-cloud-arrow-up"></i>
                                <span>Klik untuk upload (JPG, PNG, PDF - Max 2MB)</span>
                            </label>
                            <input type="file" id="file_surat_nikah" name="file_surat_nikah" accept=".jpg,.jpeg,.png,.pdf" required>
                        </div>
                        <div class="form-text">Format: JPG, JPEG, PNG, PDF | Ukuran maksimal: 2MB</div>
                    </div>
                </div>

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
</body>
</html>
