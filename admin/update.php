<?php
include "../config.php";
if (!is_admin()) exit("Akses ditolak");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id     = (int)$_POST['id'];
    $tbl    = preg_replace('/[^a-z_]/','',$_POST['tbl']);
    $status = $_POST['status'];

    $allowed = ['pengajuan_ktp','pengajuan_kk','pengajuan_pindah','pengajuan_domisili','pengajuan_akta','pengajuan_usaha'];
    if (!in_array($tbl, $allowed)) exit("Tabel tidak valid");

    $sql = "UPDATE $tbl SET status=? WHERE id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "si", $status, $id);
    mysqli_stmt_execute($stmt);
    header("Location: index.php");
    exit();
}

$id  = (int)$_GET['id'];
$tbl = preg_replace('/[^a-z_]/','',$_GET['tbl']);
$allowed = ['pengajuan_ktp','pengajuan_kk','pengajuan_pindah','pengajuan_domisili','pengajuan_akta','pengajuan_usaha'];
if (!in_array($tbl, $allowed)) exit("Tabel tidak diizinkan");

$sql = "SELECT status FROM $tbl WHERE id=?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($res);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ubah Status Pengajuan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: #f8f9fa;
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 25px;
        }
        .update-container {
            max-width: 500px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            overflow: hidden;
        }
        .update-header {
            background: #0d6efd;
            color: white;
            padding: 25px;
            text-align: center;
        }
        .update-header h2 { margin: 0; font-weight: 600; }
        .update-body { padding: 30px; }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
            display: block;
        }
        .form-select {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 12px;
            font-size: 0.95rem;
            transition: all 0.3s;
        }
        .form-select:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }
        .status-info {
            background: #f8f9fa;
            border-left: 4px solid #0d6efd;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            color: #666;
        }
        .btn-submit {
            background: #0d6efd;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s;
            margin-bottom: 10px;
        }
        .btn-submit:hover {
            background: #0b5ed7;
            color: white;
        }
        .btn-back {
            background: #f8f9fa;
            color: #0d6efd;
            border: 1px solid #ddd;
            padding: 12px 25px;
            border-radius: 8px;
            text-decoration: none;
            display: block;
            text-align: center;
            transition: all 0.3s;
        }
        .btn-back:hover {
            background: #e9ecef;
            color: #0d6efd;
            text-decoration: none;
        }
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-top: 5px;
        }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-proses { background: #d1ecf1; color: #0c5460; }
        .status-selesai { background: #d4edda; color: #155724; }
        .status-ditolak { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <div class="update-container">
        <div class="update-header">
            <h2><i class="bi bi-pencil-square me-2"></i>Ubah Status Pengajuan</h2>
        </div>

        <div class="update-body">
            <div class="status-info">
                <i class="bi bi-info-circle me-2"></i>
                <strong>Status Saat Ini:</strong>
                <br>
                <span class="status-badge status-<?= $row['status'] ?>">
                    <?= ucfirst($row['status']) ?>
                </span>
            </div>

            <form method="POST">
                <input type="hidden" name="id" value="<?= $id ?>">
                <input type="hidden" name="tbl" value="<?= $tbl ?>">

                <div class="form-group">
                    <label for="status">Pilih Status Baru</label>
                    <select id="status" name="status" class="form-select" required>
                        <option value="">-- Pilih Status --</option>
                        <option value="pending" <?= $row['status']=='pending' ? 'selected' : '' ?>>
                            Pending (Menunggu Verifikasi)
                        </option>
                        <option value="proses" <?= $row['status']=='proses' ? 'selected' : '' ?>>
                            Proses (Sedang Diproses)
                        </option>
                        <option value="selesai" <?= $row['status']=='selesai' ? 'selected' : '' ?>>
                            Selesai (Permohonan Disetujui)
                        </option>
                        <option value="ditolak" <?= $row['status']=='ditolak' ? 'selected' : '' ?>>
                            Ditolak (Permohonan Ditolak)
                        </option>
                    </select>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="bi bi-check-circle me-2"></i>Update Status
                </button>
                <a href="index.php" class="btn-back">
                    <i class="bi bi-arrow-left me-2"></i>Batal
                </a>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>