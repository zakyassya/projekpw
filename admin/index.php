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
    <title>Admin Dashboard - Kecamatan Digital</title>
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
        }
        .admin-navbar {
            background: #0d6efd;
            border-bottom: 3px solid #0d6efd;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 15px 25px;
        }
        .admin-navbar .navbar-brand { font-size: 1.5rem; font-weight: 600; color: white; }
        .admin-container { padding: 25px; }
        .admin-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: none;
        }
        .admin-card-header {
            background: #0d6efd;
            color: white;
            border-radius: 12px 12px 0 0;
            padding: 20px;
            border: none;
        }
        .table-container {
            padding: 20px;
            overflow-x: auto;
        }
        .table {
            margin-bottom: 0;
        }
        .table thead th {
            background: #f8f9fa;
            color: #0d6efd;
            font-weight: 600;
            border-bottom: 2px solid #0d6efd;
            padding: 12px;
        }
        .table tbody td {
            padding: 12px;
            vertical-align: middle;
        }
        .table tbody tr:hover {
            background: #f8f9fa;
        }
        .badge-status {
            font-weight: 500;
            padding: 6px 12px;
        }
        .btn-action {
            font-size: 0.85rem;
            padding: 5px 10px;
            border-radius: 6px;
        }
        .btn-view { background: #0dcaf0; color: white; border: none; }
        .btn-view:hover { background: #0bb5d4; color: white; }
        .btn-edit { background: #ffc107; color: #333; border: none; }
        .btn-edit:hover { background: #ffb700; color: #333; }
        .btn-delete { background: #dc3545; color: white; border: none; }
        .btn-delete:hover { background: #bb2d3b; color: white; }
        .top-bar {
            background: white;
            padding: 15px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .top-bar h4 { margin: 0; color: #0d6efd; }
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .btn-logout {
            background: #0d6efd;
            border: none;
            color: white;
        }
        .btn-logout:hover { background: #0b5ed7; color: white; }
    </style>
</head>
<body>
    <!-- Admin Navbar -->
    <nav class="admin-navbar">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <span style="font-size: 1.8rem;">🏛️</span>
                <div>
                    <div class="navbar-brand mb-0">Kecamatan Digital</div>
                    <small class="text-muted">Admin Dashboard</small>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary">Admin</span>
            </div>
        </div>
    </nav>

    <div class="admin-container">
        <!-- Top Bar -->
        <div class="top-bar">
            <h4><i class="bi bi-table me-2"></i>Daftar Pengajuan</h4>
            <div class="user-info">
                <span class="text-muted">
                    <i class="bi bi-person-circle"></i> 
                    <strong><?= htmlspecialchars($_SESSION['nama_lengkap'] ?? 'Admin') ?></strong>
                </span>
                <a href="../logout.php" class="btn btn-sm btn-logout">
                    <i class="bi bi-box-arrow-right me-2"></i>Logout
                </a>
            </div>
        </div>

        <!-- Admin Card -->
        <div class="admin-card">
            <div class="admin-card-header">
                <h5 class="mb-0"><i class="bi bi-list-check me-2"></i>Semua Pengajuan Layanan</h5>
            </div>

            <div class="table-container">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><i class="bi bi-hash me-2"></i>No</th>
                                <th><i class="bi bi-file-text me-2"></i>Jenis Surat</th>
                                <th><i class="bi bi-person me-2"></i>Pemohon</th>
                                <th><i class="bi bi-calendar me-2"></i>Tanggal</th>
                                <th><i class="bi bi-flag me-2"></i>Status</th>
                                <th><i class="bi bi-sliders me-2"></i>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            $tables = [
                                'pengajuan_ktp'       => 'KTP Elektronik',
                                'pengajuan_kk'        => 'Kartu Keluarga',
                                'pengajuan_pindah'    => 'Pindah Datang',
                                'pengajuan_domisili'  => 'Surat Domisili',
                                'pengajuan_akta'      => 'Akta Kelahiran',
                                'pengajuan_usaha'     => 'Surat Usaha'
                            ];

                            foreach ($tables as $tbl => $nama) {
                                $sql = "SELECT p.*, u.nama_lengkap 
                                        FROM $tbl p 
                                        LEFT JOIN users u ON p.user_id = u.id 
                                        ORDER BY p.created_at DESC";
                                $res = mysqli_query($conn, $sql);

                                if (!$res) continue;

                                while ($row = mysqli_fetch_assoc($res)) {
                                    $status = $row['status'] ?? 'pending';
                                    $badge_class = $status === 'selesai' ? 'bg-success' :
                                                  ($status === 'ditolak' ? 'bg-danger' :
                                                  ($status === 'diproses' ? 'bg-info' : 'bg-warning'));

                                    echo "<tr>
                                        <td><strong>$no</strong></td>
                                        <td><span class='fw-bold' style='color: #0d6efd;'>$nama</span></td>
                                        <td>" . htmlspecialchars($row['nama_lengkap'] ?? 'User dihapus') . "</td>
                                        <td>" . date('d-m-Y H:i', strtotime($row['created_at'])) . "</td>
                                        <td><span class='badge badge-status $badge_class'>".ucfirst($status)."</span></td>
                                        <td>
                                            <a href='detail.php?id={$row['id']}&tbl=$tbl' class='btn btn-action btn-view' title='Lihat Detail'>
                                                <i class='bi bi-eye me-1'></i>Lihat
                                            </a>
                                            <a href='update.php?id={$row['id']}&tbl=$tbl' class='btn btn-action btn-edit' title='Ubah Status'>
                                                <i class='bi bi-pencil me-1'></i>Ubah
                                            </a>
                                            <a href='delete.php?id={$row['id']}&tbl=$tbl' 
                                               onclick='return confirm(\"Yakin hapus pengajuan ini?\")' 
                                               class='btn btn-action btn-delete' title='Hapus'>
                                                <i class='bi bi-trash me-1'></i>Hapus
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>