<?php
include "../config.php";
if (!is_admin()) exit("Akses ditolak");

$id   = (int)$_GET['id'];
$tbl  = preg_replace('/[^a-z_]/', '', $_GET['tbl']); // sanitasi

$allowed = ['pengajuan_ktp','pengajuan_kk','pengajuan_pindah','pengajuan_domisili','pengajuan_akta','pengajuan_usaha'];
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
    <title>Detail Pengajuan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5>Detail Pengajuan <?= ucwords(str_replace('_',' ',$tbl)) ?></h5>
        </div>
        <div class="card-body">
            <p><strong>User:</strong> <?= htmlspecialchars($data['nama_lengkap']) ?></p>
            <p><strong>Tanggal:</strong> <?= $data['created_at'] ?></p>
            <hr>
            <?php
            foreach ($data as $k => $v) {
                if (in_array($k, ['id','user_id','nama_lengkap','created_at','status'])) continue;
                if (str_starts_with($k, 'file_') && $v) {
                    $path = "../uploads/" . basename($v);
                    echo "<p><strong>" . ucwords(str_replace('_',' ',$k)) . ":</strong><br>
                          <a href='$path' target='_blank' class='btn btn-sm btn-success'>Lihat File</a></p>";
                } else {
                    echo "<p><strong>" . ucwords(str_replace('_',' ',$k)) . ":</strong> " . nl2br(htmlspecialchars($v)) . "</p>";
                }
            }
            ?>
            <hr>
            <a href="index.php" class="btn btn-secondary">Kembali</a>
        </div>
    </div>
</div>
</body>
</html>