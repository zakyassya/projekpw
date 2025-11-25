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
<html>
<head>
    <title>Ubah Status</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card">
        <div class="card-header">Ubah Status Pengajuan</div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="id" value="<?= $id ?>">
                <input type="hidden" name="tbl" value="<?= $tbl ?>">
                <div class="mb-3">
                    <label>Status Baru</label>
                    <select name="status" class="form-select" required>
                        <option value="pending"   <?= $row['status']=='pending'?'selected':'' ?>>Pending</option>
                        <option value="proses"    <?= $row['status']=='proses'?'selected':'' ?>>Proses</option>
                        <option value="selesai"   <?= $row['status']=='selesai'?'selected':'' ?>>Selesai</option>
                        <option value="ditolak"   <?= $row['status']=='ditolak'?'selected':'' ?>>Ditolak</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Update Status</button>
                <a href="index.php" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>
</body>
</html>