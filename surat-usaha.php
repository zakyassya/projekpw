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
        .note { background: #fff3cd; border-left: 4px solid #ffc107; padding: 12px; margin-top: 10px; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="form-container">
        <div class="form-header">
            <h2><i class="bi bi-briefcase me-3"></i>Pengajuan Surat Usaha</h2>
            <p>Lengkapi data usaha Anda untuk permohonan surat keterangan usaha</p>
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
                    <h5><i class="bi bi-person me-2"></i>Data Pemilik</h5>
                    <div class="form-row">
                        <div class="form-group">
                            <label>NIK <span class="required">*</span></label>
                            <input type="text" name="nik" class="form-control" required 
                                value="<?= htmlspecialchars($_POST['nik'] ?? '') ?>" 
                                placeholder="16 digit NIK">
                        </div>
                        <div class="form-group">
                            <label>Nama Pemilik <span class="required">*</span></label>
                            <input type="text" name="nama" class="form-control" required 
                                value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>" 
                                placeholder="Nama lengkap">
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h5><i class="bi bi-building me-2"></i>Data Usaha</h5>
                    <div class="form-group">
                        <label>Nama Usaha <span class="required">*</span></label>
                        <input type="text" name="nama_usaha" class="form-control" required 
                            value="<?= htmlspecialchars($_POST['nama_usaha'] ?? '') ?>" 
                            placeholder="Nama bisnis/usaha">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Jenis Usaha <span class="required">*</span></label>
                            <input type="text" name="jenis_usaha" class="form-control" required 
                                value="<?= htmlspecialchars($_POST['jenis_usaha'] ?? '') ?>" 
                                placeholder="Contoh: Toko, Jasa, Produksi">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Alamat Usaha <span class="required">*</span></label>
                        <textarea name="alamat_usaha" class="form-control" rows="3" required 
                            placeholder="Alamat tempat usaha"><?= htmlspecialchars($_POST['alamat_usaha'] ?? '') ?></textarea>
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
                </div>

                <div class="form-section">
                    <h5><i class="bi bi-cash me-2"></i>Detail Usaha (Opsional)</h5>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Modal Usaha</label>
                            <input type="number" name="modal_usaha" class="form-control" 
                                value="<?= htmlspecialchars($_POST['modal_usaha'] ?? '') ?>" 
                                placeholder="Dalam Rupiah">
                            <div class="form-text">Biarkan kosong jika tidak ingin disertakan</div>
                        </div>
                        <div class="form-group">
                            <label>Jumlah Karyawan</label>
                            <input type="number" name="jumlah_karyawan" class="form-control" 
                                value="<?= htmlspecialchars($_POST['jumlah_karyawan'] ?? '') ?>" 
                                placeholder="Jumlah orang">
                            <div class="form-text">Biarkan kosong jika tidak ingin disertakan</div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h5><i class="bi bi-telephone me-2"></i>Kontak</h5>
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
                        <label for="file_ktp">KTP <span class="required">*</span></label>
                        <div class="file-upload-wrapper">
                            <label for="file_ktp" class="file-upload-label">
                                <i class="bi bi-cloud-arrow-up"></i>
                                <span>Klik untuk upload (JPG, PNG, PDF - Max 2MB)</span>
                            </label>
                            <input type="file" id="file_ktp" name="file_ktp" accept=".jpg,.jpeg,.png,.pdf" required>
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
                        <label for="file_foto_usaha">Foto Usaha <span class="required">*</span></label>
                        <div class="file-upload-wrapper">
                            <label for="file_foto_usaha" class="file-upload-label">
                                <i class="bi bi-cloud-arrow-up"></i>
                                <span>Klik untuk upload (JPG, PNG, PDF - Max 2MB)</span>
                            </label>
                            <input type="file" id="file_foto_usaha" name="file_foto_usaha" accept=".jpg,.jpeg,.png,.pdf" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="file_surat_pernyataan">Surat Pernyataan <span class="required">*</span></label>
                        <div class="file-upload-wrapper">
                            <label for="file_surat_pernyataan" class="file-upload-label">
                                <i class="bi bi-cloud-arrow-up"></i>
                                <span>Klik untuk upload (JPG, PNG, PDF - Max 2MB)</span>
                            </label>
                            <input type="file" id="file_surat_pernyataan" name="file_surat_pernyataan" accept=".jpg,.jpeg,.png,.pdf" required>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-3">
                    <button type="submit" class="btn-submit">
                        <i class="bi bi-check-circle me-2"></i>Ajukan Surat Usaha
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
