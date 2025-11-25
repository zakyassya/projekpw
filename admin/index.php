<?php
include "../config.php";

// Pastikan user sudah login
if (!is_logged_in()) {
    header("Location: ../login.php");
    exit();
}

// Pastikan user adalah admin
if (!is_admin()) {
    header("Location: ../index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Kecamatan Digital</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background: #f8f9fa; }
        .card-header { background: linear-gradient(135deg, #667eea, #764ba2); color: white; }
    </style>
</head>
<body>
<div class="container-fluid mt-4">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Panel Admin</h4>
            <div>
                <span class="me-3">Halo, <?= htmlspecialchars($_SESSION['nama_lengkap'] ?? 'Admin') ?></span>
                <a href="../logout.php" class="btn btn-light btn-sm">Logout</a>
            </div>
        </div>
        <div class="card-body">
            <h5>Daftar Semua Pengajuan</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>Jenis Surat</th>
                            <th>Pemohon</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                            <th width="120">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        $tables = [
                            'pengajuan_ktp'       => 'KTP Elektronik',
                            'pengajuan_kk'        => 'Kartu Keluarga',
                            'pengajuan_pindah'    => 'Pindah Datang/Domisili',
                            'pengajuan_domisili'  => 'Surat Domisili',
                            'pengajuan_akta'      => 'Akta Kelahiran',
                            'pengajuan_usaha'     => 'Surat Keterangan Usaha'
                        ];

                        foreach ($tables as $tbl => $nama) {
                            $sql = "SELECT p.*, u.nama_lengkap 
                                    FROM $tbl p 
                                    LEFT JOIN users u ON p.user_id = u.id 
                                    ORDER BY p.created_at DESC";
                            $res = mysqli_query($conn, $sql);

                            if (!$res) continue; // skip kalau tabel belum ada

                            while ($row = mysqli_fetch_assoc($res)) {
                                // Perbaikan status sesuai database kamu
                                $status = $row['status'] ?? 'pending';
                                $badge = $status === 'selesai' ? 'success' :
                                        ($status === 'ditolak' ? 'danger' :
                                        ($status === 'diproses' ? 'primary' : 'warning'));

                                echo "<tr>
                                    <td>$no</td>
                                    <td><strong>$nama</strong></td>
                                    <td>" . htmlspecialchars($row['nama_lengkap'] ?? 'User dihapus') . "</td>
                                    <td>" . date('d-m-Y H:i', strtotime($row['created_at'])) . "</td>
                                    <td><span class='badge bg-$badge text-white px-3 py-2'>".ucfirst($status)."</span></td>
                                    <td>
                                        <a href='detail.php?id={$row['id']}&tbl=$tbl' class='btn btn-sm btn-info' title='Lihat'>
                                            View
                                        </a>
                                        <a href='update.php?id={$row['id']}&tbl=$tbl' class='btn btn-sm btn-warning' title='Ubah Status'>
                                            Edit
                                        </a>
                                        <a href='delete.php?id={$row['id']}&tbl=$tbl' 
                                           onclick='return confirm(\"Yakin hapus pengajuan ini?\")' 
                                           class='btn btn-sm btn-danger' title='Hapus'>
                                            Delete
                                        </a>
                                    </td>
                                </tr>";
                                $no++;
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>