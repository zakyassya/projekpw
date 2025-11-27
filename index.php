<?php
include 'config.php';
require_login();

// Ambil statistik - Hitung jumlah SEMUA pengajuan (regardless of status)
$sql_penduduk = "SELECT COUNT(*) as total FROM pengajuan_ktp";
$result_penduduk = mysqli_query($conn, $sql_penduduk);
if (!$result_penduduk) {
    $penduduk = 0;
} else {
    $row = mysqli_fetch_assoc($result_penduduk);
    $penduduk = isset($row['total']) ? $row['total'] : 0;
}

$sql_kk = "SELECT COUNT(*) as total FROM pengajuan_kk";
$result_kk = mysqli_query($conn, $sql_kk);
if (!$result_kk) {
    $kk = 0;
} else {
    $row = mysqli_fetch_assoc($result_kk);
    $kk = isset($row['total']) ? $row['total'] : 0;
}

// Ambil data per jenis layanan
$sql_ktp = "SELECT COUNT(*) as total FROM pengajuan_ktp";
$result_ktp = mysqli_query($conn, $sql_ktp);
$ktp_count = mysqli_fetch_assoc($result_ktp)['total'] ?? 0;

$sql_domisili = "SELECT COUNT(*) as total FROM pengajuan_domisili";
$result_domisili = mysqli_query($conn, $sql_domisili);
$domisili_count = mysqli_fetch_assoc($result_domisili)['total'] ?? 0;

$sql_usaha = "SELECT COUNT(*) as total FROM pengajuan_usaha";
$result_usaha = mysqli_query($conn, $sql_usaha);
$usaha_count = mysqli_fetch_assoc($result_usaha)['total'] ?? 0;

$sql_pindah = "SELECT COUNT(*) as total FROM pengajuan_pindah";
$result_pindah = mysqli_query($conn, $sql_pindah);
$pindah_count = mysqli_fetch_assoc($result_pindah)['total'] ?? 0;

$sql_akta = "SELECT COUNT(*) as total FROM pengajuan_akta";
$result_akta = mysqli_query($conn, $sql_akta);
$akta_count = mysqli_fetch_assoc($result_akta)['total'] ?? 0;

// Ambil pengajuan user saat ini
$id_user = $_SESSION['user_id'] ?? null;
$pengajuan_list = [];

if ($id_user) {
    // KTP
    $sql_user_ktp = "SELECT 'KTP' as jenis, id, created_at, status FROM pengajuan_ktp WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $sql_user_ktp);
    mysqli_stmt_bind_param($stmt, "i", $id_user);
    mysqli_stmt_execute($stmt);
    $result_ktp = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result_ktp)) {
        $pengajuan_list[] = $row;
    }

    // Kartu Keluarga
    $sql_user_kk = "SELECT 'Kartu Keluarga' as jenis, id, created_at, status FROM pengajuan_kk WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $sql_user_kk);
    mysqli_stmt_bind_param($stmt, "i", $id_user);
    mysqli_stmt_execute($stmt);
    $result_kk = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result_kk)) {
        $pengajuan_list[] = $row;
    }

    // Domisili
    $sql_user_domisili = "SELECT 'Surat Domisili' as jenis, id, created_at, status FROM pengajuan_domisili WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $sql_user_domisili);
    mysqli_stmt_bind_param($stmt, "i", $id_user);
    mysqli_stmt_execute($stmt);
    $result_domisili = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result_domisili)) {
        $pengajuan_list[] = $row;
    }

    // Usaha
    $sql_user_usaha = "SELECT 'Surat Usaha' as jenis, id, created_at, status FROM pengajuan_usaha WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $sql_user_usaha);
    mysqli_stmt_bind_param($stmt, "i", $id_user);
    mysqli_stmt_execute($stmt);
    $result_usaha = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result_usaha)) {
        $pengajuan_list[] = $row;
    }

    // Pindah Alamat
    $sql_user_pindah = "SELECT 'Pindah Alamat' as jenis, id, created_at, status FROM pengajuan_pindah WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $sql_user_pindah);
    mysqli_stmt_bind_param($stmt, "i", $id_user);
    mysqli_stmt_execute($stmt);
    $result_pindah = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result_pindah)) {
        $pengajuan_list[] = $row;
    }

    // Akta Kelahiran
    $sql_user_akta = "SELECT 'Akta Kelahiran' as jenis, id, created_at, status FROM pengajuan_akta WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $sql_user_akta);
    mysqli_stmt_bind_param($stmt, "i", $id_user);
    mysqli_stmt_execute($stmt);
    $result_akta = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result_akta)) {
        $pengajuan_list[] = $row;
    }

    // Sort by tanggal terbaru
    usort($pengajuan_list, function ($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Kecamatan Kami</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            background: #f8f9fa;
            min-height: 100vh;
            padding: 20px;
        }

        .navbar-custom {
            background: white;
            border-radius: 15px;
            padding: 15px 25px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .logo-header {
            font-size: 30px;
            margin-right: 15px;
        }

        .nav-menu {
            background: white;
            border-radius: 15px;
            padding: 10px 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .nav-menu .nav-link {
            color: #0d6efd;
            font-weight: 600;
            padding: 10px 20px;
            border-radius: 8px;
            transition: all 0.3s;
        }

        .nav-menu .nav-link:hover,
        .nav-menu .nav-link.active {
            background: #0d6efd;
            color: white;
        }

        .hero-card {
            background: white;
            border-radius: 15px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(13, 110, 253, 0.3);
        }

        .stat-number {
            font-size: 36px;
            font-weight: bold;
            color: #0d6efd;
        }

        .service-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            text-decoration: none;
            color: #333;
            display: block;
            transition: all 0.3s;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        .service-card:hover {
            transform: translateY(-10px);
            background: #0d6efd;
            color: white;
            box-shadow: 0 15px 40px rgba(13, 110, 253, 0.3);
        }

        .service-icon {
            font-size: 50px;
            margin-bottom: 15px;
        }

        .footer {
            background: white;
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            margin-top: 30px;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Header -->
        <nav class="navbar navbar-custom">
            <div class="d-flex align-items-center">
                <span class="logo-header">🏛️</span>
                <div>
                    <h5 class="mb-0">Kecamatan Kami</h5>
                    <small class="text-muted">Layanan Administrasi Kependudukan</small>
                </div>
            </div>
            <div>
                <span class="me-3"><i class="bi bi-person-circle"></i> <?php echo $_SESSION['nama_lengkap']; ?></span>
                <a href="logout.php" class="btn btn-outline-danger btn-sm">
                    <i class="bi bi-box-arrow-right"></i> Keluar
                </a>
            </div>
        </nav>

        <!-- Menu Navigation -->
        <nav class="nav-menu">
            <ul class="nav justify-content-center flex-wrap">
                <li class="nav-item">
                    <a class="nav-link active" href="index.php">
                        <i class="bi bi-house-door"></i> Beranda
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="ktp.php">
                        <i class="bi bi-card-heading"></i> KTP
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="kartu-keluarga.php">
                        <i class="bi bi-people"></i> Kartu Keluarga
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="akta-kelahiran.php">
                        <i class="bi bi-file-earmark-text"></i> Akta Kelahiran
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="pindah-alamat.php">
                        <i class="bi bi-box-arrow-right"></i> Pindah Alamat
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="surat-usaha.php">
                        <i class="bi bi-briefcase"></i> Surat Usaha
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Hero Section -->
        <div class="hero-card">
            <h2 class="text-primary mb-3">Selamat Datang di Portal Kecamatan Kami</h2>
            <p class="lead">Kami menyediakan layanan administrasi kependudukan yang cepat, mudah, dan terpercaya untuk seluruh masyarakat</p>
        </div>

        <!-- Statistics -->
        <div class="row g-3 mb-4 justify-content-center">
            <div class="col-lg-2 col-md-3 col-sm-6">
                <div class="stat-card">
                    <div class="stat-number"><?php echo number_format($ktp_count); ?></div>
                    <div class="text-muted">Pembuatan KTP</div>
                </div>
            </div>
            <div class="col-lg-2 col-md-3 col-sm-6">
                <div class="stat-card">
                    <div class="stat-number"><?php echo number_format($domisili_count); ?></div>
                    <div class="text-muted">Surat Domisili</div>
                </div>
            </div>
            <div class="col-lg-2 col-md-3 col-sm-6">
                <div class="stat-card">
                    <div class="stat-number"><?php echo number_format($usaha_count); ?></div>
                    <div class="text-muted">Surat Usaha</div>
                </div>
            </div>
            <div class="col-lg-2 col-md-3 col-sm-6">
                <div class="stat-card">
                    <div class="stat-number"><?php echo number_format($pindah_count); ?></div>
                    <div class="text-muted">Pindah Alamat</div>
                </div>
            </div>
            <div class="col-lg-2 col-md-3 col-sm-6">
                <div class="stat-card">
                    <div class="stat-number"><?php echo number_format($akta_count); ?></div>
                    <div class="text-muted">Akta Kelahiran</div>
                </div>
            </div>
        </div>

        <!-- Status Pengajuan User -->
        <div class="card my-4" style="background: white; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.1);">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Status Pengajuan Anda</h4>
            </div>
            <div class="card-body">
                <?php if (empty($pengajuan_list)): ?>
                    <p class="text-muted">Tidak ada pengajuan surat saat ini.</p>
                <?php else: ?>
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Jenis Surat</th>
                                <th>Tanggal Pengajuan</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pengajuan_list as $peng): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($peng['jenis']); ?></strong></td>
                                    <td><?php echo date('d-m-Y H:i', strtotime($peng['created_at'])); ?></td>
                                    <td>
                                        <span class="badge <?php
                                                            $status = strtolower($peng['status']);
                                                            if ($status == 'selesai') echo 'bg-success';
                                                            elseif ($status == 'diproses') echo 'bg-info';
                                                            elseif ($status == 'ditolak') echo 'bg-danger';
                                                            else echo 'bg-warning';
                                                            ?>">
                                            <?php echo ucfirst($peng['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- Services -->
        <h3 class="text-white text-center mb-4">Layanan Kami</h3>
        <div class="row g-3 mb-4">
            <div class="col-md-4 col-sm-6">
                <a href="ktp.php" class="service-card">
                    <div class="service-icon">🪪</div>
                    <h5>Pembuatan KTP</h5>
                    <p class="mb-0">Pengurusan KTP baru dan perpanjangan</p>
                </a>
            </div>
            <div class="col-md-4 col-sm-6">
                <a href="kartu-keluarga.php" class="service-card">
                    <div class="service-icon">👨‍👩‍👧‍👦</div>
                    <h5>Kartu Keluarga</h5>
                    <p class="mb-0">Pembuatan dan perubahan KK</p>
                </a>
            </div>
            <div class="col-md-4 col-sm-6">
                <a href="akta-kelahiran.php" class="service-card">
                    <div class="service-icon">📄</div>
                    <h5>Akta Kelahiran</h5>
                    <p class="mb-0">Pengurusan akta kelahiran</p>
                </a>
            </div>
            <div class="col-md-4 col-sm-6">
                <a href="surat-domisili.php" class="service-card">
                    <div class="service-icon">🏠</div>
                    <h5>Surat Domisili</h5>
                    <p class="mb-0">Surat keterangan domisili</p>
                </a>
            </div>
            <div class="col-md-4 col-sm-6">
                <a href="pindah-alamat.php" class="service-card">
                    <div class="service-icon">📦</div>
                    <h5>Pindah Alamat</h5>
                    <p class="mb-0">Surat keterangan pindah</p>
                </a>
            </div>
            <div class="col-md-4 col-sm-6">
                <a href="surat-usaha.php" class="service-card">
                    <div class="service-icon">💼</div>
                    <h5>Surat Usaha</h5>
                    <p class="mb-0">Legalisasi usaha mikro</p>
                </a>
            </div>
        </div>

        <!-- Footer -->
        <footer class="footer">
            <p class="mb-1"> Kecamatan Kami | Melayani dengan Sepenuh Hati</p>
            <p class="mb-0 text-muted">Jam Operasional: Senin - Jumat, 08:00 - 16:00 WIB</p>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>