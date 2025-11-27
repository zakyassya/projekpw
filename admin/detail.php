<?php
include "../config.php";
if (!is_admin()) exit("Akses ditolak");

$id   = (int)$_GET['id'];
$tbl  = preg_replace('/[^a-z_]/', '', $_GET['tbl']); // sanitasi

$allowed = ['pengajuan_ktp', 'pengajuan_kk', 'pengajuan_pindah', 'pengajuan_domisili', 'pengajuan_akta', 'pengajuan_usaha'];
if (!in_array($tbl, $allowed)) exit("Tabel tidak diizinkan");

$sql  = "SELECT p.*, u.nama_lengkap FROM $tbl p JOIN users u ON p.user_id=u.id WHERE p.id=?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$res  = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($res);
if (!$data) exit("Data tidak ditemukan");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pengajuan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #f8f9fa;
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 25px;
        }

        .detail-container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }

        .detail-header {
            background: #0d6efd;
            color: white;
            padding: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .detail-header h2 {
            margin: 0;
            font-weight: 600;
        }

        .detail-body {
            padding: 30px;
        }

        .info-section {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
        }

        .info-section h5 {
            color: #0d6efd;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .info-row {
            display: grid;
            grid-template-columns: 200px 1fr;
            gap: 15px;
            margin-bottom: 12px;
        }

        .info-label {
            font-weight: 600;
            color: #333;
        }

        .info-value {
            color: #666;
            word-wrap: break-word;
        }

        .file-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 15px;
            background: #f0f7ff;
            border: 1px solid #0d6efd;
            border-radius: 6px;
            color: #0d6efd;
            text-decoration: none;
            transition: all 0.3s;
        }

        .file-link:hover {
            background: #0d6efd;
            color: white;
            text-decoration: none;
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

        .btn-edit {
            background: #0d6efd;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-edit:hover {
            background: #0b5ed7;
            color: white;
        }

        @media (max-width: 768px) {
            .info-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <div class="detail-container">
        <div class="detail-header">
            <h2><i class="bi bi-file-earmark-text me-3"></i>Detail Pengajuan</h2>
            <a href="index.php" class="btn-back">
                <i class="bi bi-arrow-left"></i>Kembali
            </a>
        </div>

        <div class="detail-body">
            <div class="info-section">
                <h5><i class="bi bi-person me-2"></i>Informasi Pemohon</h5>
                <div class="info-row">
                    <div class="info-label">Nama</div>
                    <div class="info-value"><?= htmlspecialchars($data['nama_lengkap']) ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Jenis Permohonan</div>
                    <div class="info-value"><span class="badge bg-info"><?= ucwords(str_replace('_', ' ', $tbl)) ?></span></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Tanggal Permohonan</div>
                    <div class="info-value"><?= date('d M Y H:i', strtotime($data['created_at'])) ?></div>
                </div>
            </div>

            <div class="info-section">
                <h5><i class="bi bi-folder-open me-2"></i>Detail Data</h5>
                <?php
                foreach ($data as $k => $v) {
                    if (in_array($k, ['id', 'user_id', 'nama_lengkap', 'created_at', 'status'])) continue;
                    if (str_starts_with($k, 'file_') && $v) {
                        $filename = basename($v);
                        $download_url = "download.php?file=" . urlencode($filename);
                        echo "<div class='info-row'>
                            <div class='info-label'>" . ucwords(str_replace('_', ' ', $k)) . "</div>
                            <div class='info-value'>
                                <a href='$download_url' class='file-link'>
                                    <i class='bi bi-file-earmark-arrow-down'></i>
                                    Unduh File
                                </a>
                            </div>
                        </div>";
                    } else {
                        echo "<div class='info-row'>
                            <div class='info-label'>" . ucwords(str_replace('_', ' ', $k)) . "</div>
                            <div class='info-value'>" . nl2br(htmlspecialchars($v)) . "</div>
                        </div>";
                    }
                }
                ?>
            </div>

            <div style="display: flex; gap: 10px; margin-top: 30px;">
                <a href="update.php?id=<?= $id ?>&tbl=<?= $tbl ?>" class="btn-edit">
                    <i class="bi bi-pencil"></i>Ubah Status
                </a>
                <a href="index.php" class="btn-back">
                    <i class="bi bi-arrow-left"></i>Kembali
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>