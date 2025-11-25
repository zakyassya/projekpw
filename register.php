<?php
include 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = clean_input($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $nama_lengkap = clean_input($_POST['nama_lengkap']);
    $email = clean_input($_POST['email']);
    
    // Validasi
    if (empty($username) || empty($password) || empty($nama_lengkap)) {
        $error = "Semua field wajib diisi!";
    } elseif ($password !== $confirm_password) {
        $error = "Password dan konfirmasi password tidak cocok!";
    } elseif (strlen($password) < 6) {
        $error = "Password minimal 6 karakter!";
    } else {
        // Cek apakah username sudah ada
        $sql = "SELECT id FROM users WHERE username = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        
        if (mysqli_stmt_num_rows($stmt) > 0) {
            $error = "Username sudah terdaftar!";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert ke database
            $sql = "INSERT INTO users (username, password, nama_lengkap, email, role) VALUES (?, ?, ?, ?, 'user')";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ssss", $username, $hashed_password, $nama_lengkap, $email);
            
            if (mysqli_stmt_execute($stmt)) {
                $success = "Pendaftaran berhasil! Silakan login.";
            } else {
                $error = "Terjadi kesalahan. Silakan coba lagi.";
            }
        }
        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi - Kecamatan Digital</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        section { min-height: 100vh; display: flex; flex-direction: column; }
        .container-fluid { flex: 1; }
        .h-custom { height: calc(100% - 73px); }
        @media (max-width: 450px) { .h-custom { height: 100%; } }
        img.img-fluid { max-height: 400px; object-fit: contain; }
    </style>
</head>
<body>
    <section>
        <div class="container-fluid h-custom">
            <div class="row d-flex justify-content-center align-items-center h-100">
                <!-- Gambar kiri -->
                <div class="col-md-9 col-lg-6 col-xl-5">
                    <img src="uploads/foto/undraw_town_oesm.svg"
                        class="img-fluid" alt="Registration illustration">
                </div>

                <!-- Form kanan -->
                <div class="col-md-8 col-lg-6 col-xl-4 offset-xl-1">
                    <!-- Error Alert -->
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                            <i class="bi bi-exclamation-circle-fill"></i>
                            <strong>Error!</strong> <?= htmlspecialchars($error) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Success Alert -->
                    <?php if ($success): ?>
                        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                            <i class="bi bi-check-circle-fill"></i>
                            <strong>Sukses!</strong> <?= htmlspecialchars($success) ?>
                            <a href="login.php" class="alert-link">Klik di sini untuk login</a>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Registration Form -->
                    <form method="POST" action="">
                        <h3 class="mb-4">Buat Akun Baru</h3>

                        <div class="form-outline mb-4">
                            <input type="text" id="username" name="username" class="form-control form-control-lg"
                                placeholder="Masukan Username" required autofocus />
                            <label class="form-label" for="username">Username</label>
                        </div>

                        <div class="form-outline mb-4">
                            <input type="text" id="nama_lengkap" name="nama_lengkap" class="form-control form-control-lg"
                                placeholder="Masukkan nama lengkap" required />
                            <label class="form-label" for="nama_lengkap">Nama Lengkap</label>
                        </div>

                        <div class="form-outline mb-4">
                            <input type="email" id="email" name="email" class="form-control form-control-lg"
                                placeholder="Masukkan email aktif" />
                            <label class="form-label" for="email">Email (Opsional)</label>
                        </div>

                        <div class="form-outline mb-4">
                            <input type="password" id="password" name="password" class="form-control form-control-lg"
                                placeholder="Minimal 6 karakter" required />
                            <label class="form-label" for="password">Password</label>
                            <small class="text-muted d-block mt-1">Minimum 6 karakter</small>
                        </div>

                        <div class="form-outline mb-4">
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control form-control-lg"
                                placeholder="Ketik ulang password" required />
                            <label class="form-label" for="confirm_password">Konfirmasi Password</label>
                        </div>

                        <div class="text-center text-lg-start mt-4 pt-2">
                            <button type="submit" class="btn btn-primary btn-lg w-100" style="padding-left: 2.5rem; padding-right: 2.5rem;">
                                <i class="bi bi-person-plus me-2"></i>Daftar Sekarang
                            </button>
                            <p class="small fw-bold mt-2 pt-1 mb-0">
                                Sudah punya akun? <a href="login.php" class="link-primary">Login di sini</a>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="d-flex flex-column flex-md-row text-center text-md-start justify-content-between py-4 px-4 px-xl-5 bg-primary">
            <div class="text-white mb-3 mb-md-0">
                Kelurahan Kami.
            </div>
        </div>
    </section>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>