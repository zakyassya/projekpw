<?php
include 'config.php';
require_login();

// Ambil statistik
$sql_penduduk = "SELECT COUNT(*) as total FROM pengajuan_ktp WHERE status = 'selesai'";
$result_penduduk = mysqli_query($conn, $sql_penduduk);
$penduduk = mysqli_fetch_assoc($result_penduduk)['total'];

$sql_kk = "SELECT COUNT(*) as total FROM pengajuan_kk WHERE status = 'selesai'";
$result_kk = mysqli_query($conn, $sql_kk);
$kk = mysqli_fetch_assoc($result_kk)['total'];

$sql_layanan = "SELECT 
    (SELECT COUNT(*) FROM pengajuan_ktp WHERE MONTH(created_at) = MONTH(CURRENT_DATE)) +
    (SELECT COUNT(*) FROM pengajuan_kk WHERE MONTH(created_at) = MONTH(CURRENT_DATE)) +
    (SELECT COUNT(*) FROM pengajuan_akta WHERE MONTH(created_at) = MONTH(CURRENT_DATE)) +
    (SELECT COUNT(*) FROM pengajuan_pindah WHERE MONTH(created_at) = MONTH(CURRENT_DATE)) +
    (SELECT COUNT(*) FROM pengajuan_usaha WHERE MONTH(created_at) = MONTH(CURRENT_DATE)) as total";
$result_layanan = mysqli_query($conn, $sql_layanan);
$layanan = mysqli_fetch_assoc($result_layanan)['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Kecamatan Digital</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .navbar-top {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin: 20px;
            padding: 15px 25px;
        }
        .navbar-top .navbar-brand { font-size: 1.5rem; font-weight: 600; color: #667eea; }
        .nav-menu {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin: 0 20px 25px;
            padding: 10px 20px;
        }
        .nav-menu .nav-link {
            color: #667eea;
            font-weight: 500;
            transition: all 0.3s;
            border-radius: 8px;
            margin: 5px;
        }
        .nav-menu .nav-link:hover,
        .nav-menu .nav-link.active {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }
        .container-dashboard { padding: 0 20px 20px; }
        .card-stat {
            background: white;
            border-radius: 12px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: all 0.3s;
            border: none;
        }
        .card-stat:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.2);
        }
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #667eea;
        }
        .stat-label { color: #666; font-size: 0.95rem; margin-top: 10px; }
        .card-service {
            background: white;
            border-radius: 12px;
            padding: 30px;
            text-align: center;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            text-decoration: none;
            color: #333;
            border: none;
            display: block;
            height: 100%;
        }
        .card-service:hover {
            transform: translateY(-10px);
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            box-shadow: 0 12px 30px rgba(102, 126, 234, 0.3);
        }
        .service-icon { font-size: 3rem; margin-bottom: 15px; }
        .section-title { 
            color: white; 
            text-align: center; 
            font-weight: 600; 
            margin: 30px 20px 20px;
            font-size: 1.8rem;
        }
        .hero-card {
            background: white;
            border-radius: 12px;
            padding: 40px;
            margin: 20px;
            text-align: center;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        .footer {
            background: white;
            border-radius: 12px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin: 25px 20px;
            color: #666;
        }
        .btn-logout { background: linear-gradient(135deg, #667eea, #764ba2); border: none; }
        .btn-logout:hover { opacity: 0.9; }
    </style>
</head>
<body>
    <!-- Navbar Top -->
    <nav class="navbar navbar-top">
        <div class="w-100 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <span style="font-size: 2rem; margin-right: 15px;">🏛️</span>
                <div>
                    <div class="navbar-brand mb-0 pb-0">Kecamatan Digital</div>
                    <small class="text-muted">Portal Layanan Administrasi</small>
                </div>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="text-muted">
                    <i class="bi bi-person-circle"></i> 
                    <strong><?= htmlspecialchars($_SESSION['nama_lengkap'] ?? 'User') ?></strong>
                </span>
                <a href="logout.php" class="btn btn-sm btn-logout text-white">
                    <i class="bi bi-box-arrow-right me-2"></i>Keluar
                </a>
            </div>
        </div>
    </nav>

    <!-- Navigation Menu -->
    <nav class="nav-menu">
        <div class="d-flex flex-wrap justify-content-center gap-1">
            <a class="nav-link active" href="index.php"><i class="bi bi-house-door me-2"></i>Beranda</a>
            <a class="nav-link" href="ktp.php"><i class="bi bi-card-heading me-2"></i>KTP</a>
            <a class="nav-link" href="kartu-keluarga.php"><i class="bi bi-people me-2"></i>KK</a>
            <a class="nav-link" href="akta-kelahiran.php"><i class="bi bi-file-earmark-text me-2"></i>Akta</a>
            <a class="nav-link" href="pindah-alamat.php"><i class="bi bi-arrow-right me-2"></i>Pindah</a>
            <a class="nav-link" href="surat-usaha.php"><i class="bi bi-briefcase me-2"></i>Usaha</a>
            <a class="nav-link" href="surat-domisili.php"><i class="bi bi-house me-2"></i>Domisili</a>
        </div>
    </nav>

    <div class="container-dashboard">
        <!-- Hero Section -->
        <div class="hero-card">
            <h2 class="mb-3" style="color: #667eea;">Selamat Datang di Portal Kecamatan</h2>
            <p class="lead text-muted">Kami menyediakan layanan administrasi kependudukan yang cepat, mudah, dan terpercaya untuk seluruh masyarakat.</p>
        </div>

        <!-- Statistics -->
        <div class="row g-4 mb-4">
            <div class="col-md-6 col-lg-3">
                <div class="card-stat">
                    <div class="stat-number"><?= number_format($penduduk) ?></div>
                    <div class="stat-label">KTP Terproses</div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card-stat">
                    <div class="stat-number"><?= number_format($kk) ?></div>
                    <div class="stat-label">Kartu Keluarga</div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card-stat">
                    <div class="stat-number"><?= number_format($layanan) ?></div>
                    <div class="stat-label">Layanan Bulan Ini</div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card-stat">
                    <div class="stat-number">98%</div>
                    <div class="stat-label">Kepuasan Pelanggan</div>
                </div>
            </div>
        </div>

        <!-- Services Section -->
        <h3 class="section-title">Layanan Kami</h3>
        <div class="row g-4 mb-4">
            <div class="col-md-6 col-lg-4">
                <a href="ktp.php" class="card-service text-decoration-none">
                    <div class="service-icon">🪪</div>
                    <h5 class="fw-bold">Pembuatan KTP</h5>
                    <p class="mb-0 small">Pengurusan KTP baru dan perpanjangan dengan cepat</p>
                </a>
            </div>
            <div class="col-md-6 col-lg-4">
                <a href="kartu-keluarga.php" class="card-service text-decoration-none">
                    <div class="service-icon">👨‍👩‍👧‍👦</div>
                    <h5 class="fw-bold">Kartu Keluarga</h5>
                    <p class="mb-0 small">Pembuatan dan perubahan data KK</p>
                </a>
            </div>
            <div class="col-md-6 col-lg-4">
                <a href="akta-kelahiran.php" class="card-service text-decoration-none">
                    <div class="service-icon">📄</div>
                    <h5 class="fw-bold">Akta Kelahiran</h5>
                    <p class="mb-0 small">Pengurusan akta kelahiran lengkap</p>
                </a>
            </div>
            <div class="col-md-6 col-lg-4">
                <a href="surat-domisili.php" class="card-service text-decoration-none">
                    <div class="service-icon">🏠</div>
                    <h5 class="fw-bold">Surat Domisili</h5>
                    <p class="mb-0 small">Surat keterangan domisili resmi</p>
                </a>
            </div>
            <div class="col-md-6 col-lg-4">
                <a href="pindah-alamat.php" class="card-service text-decoration-none">
                    <div class="service-icon">📍</div>
                    <h5 class="fw-bold">Pindah Alamat</h5>
                    <p class="mb-0 small">Surat keterangan pindah datang</p>
                </a>
            </div>
            <div class="col-md-6 col-lg-4">
                <a href="surat-usaha.php" class="card-service text-decoration-none">
                    <div class="service-icon">💼</div>
                    <h5 class="fw-bold">Surat Usaha</h5>
                    <p class="mb-0 small">Legalisasi usaha mikro kecil</p>
                </a>
            </div>
        </div>

        <!-- Footer -->
        <footer class="footer">
            <p class="mb-1"><strong>&copy; 2024 Kecamatan Digital</strong></p>
            <p class="mb-0 small">Melayani dengan sepenuh hati | Jam: Sen-Jum 08:00-16:00 WIB</p>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
 