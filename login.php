<?php
session_start(); // INI YANG HILANG NGAB! Tambah ini di atas

include 'config.php'; // pastikan ini konek DB bener

// Redirect kalau sudah login
if (isset($_SESSION['user_id'])) {
    header("Location: admin/index.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email_or_username = trim($_POST['email_or_username'] ?? '');
    $password          = $_POST['password'] ?? '';

    if (empty($email_or_username) || empty($password)) {
        $error = "Username/Email dan Password harus diisi!";
    } else {
        // Cari user berdasarkan username ATAU email
        $sql = "SELECT id, username, password, nama_lengkap, role FROM users WHERE username = ? OR email = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ss", $email_or_username, $email_or_username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) === 1) {
            $user = mysqli_fetch_assoc($result);

            $login_berhasil = false;

            // Cek apakah password sudah di-hash (bcrypt)
            if (password_verify($password, $user['password'])) {
                $login_berhasil = true;
            } 
            // Kalau belum di-hash (masih plain text / md5 lama), cek langsung
            else if ($user['password'] === $password || $user['password'] === md5($password)) {
                $login_berhasil = true;

                // OTOMATIS REHASH password jadi bcrypt biar next time aman!
                $new_hash = password_hash($password, PASSWORD_DEFAULT);
                $update_sql = "UPDATE users SET password = ? WHERE id = ?";
                $update_stmt = mysqli_prepare($conn, $update_sql);
                mysqli_stmt_bind_param($update_stmt, "si", $new_hash, $user['id']);
                mysqli_stmt_execute($update_stmt);
                mysqli_stmt_close($update_stmt);
            }

            if ($login_berhasil) {
                // Session login
                $_SESSION['user_id']       = $user['id'];
                $_SESSION['username']      = $user['username'];
                $_SESSION['nama_lengkap']  = $user['nama_lengkap'];
                $_SESSION['role']          = $user['role'];

                // Debug sementara: Cek session ke-set (hapus nanti)
                // var_dump($_SESSION); exit();

                header("Location: admin/index.php");
                exit();
            } else {
                $error = "Password salah!";
            }
        } else {
            $error = "Username atau Email tidak ditemukan!";
        }

        mysqli_stmt_close($stmt);
    }
}

// Close conn kalau perlu (opsional, tapi bagus)
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Kecamatan Digital</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body{background: linear-gradient(135deg,#667eea,#764ba2);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
        .login-card{background:white;border-radius:20px;box-shadow:0 10px 40px rgba(0,0,0,.2);overflow:hidden}
        .login-left{padding:40px}
        .login-right{background:linear-gradient(135deg,#667eea,#764ba2);color:white;padding:50px}
        .logo-circle{width:80px;height:80px;background:linear-gradient(135deg,#667eea,#764ba2);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:40px;margin:0 auto 20px;color:white}
        .btn-primary{background:linear-gradient(135deg,#667eea,#764ba2);border:none;padding:12px 0;font-weight:600}
        .btn-primary:hover{opacity:.9;transform:translateY(-2px)}
    </style>
</head>
<body>
    <div class="container">
        <div class="login-card">
            <div class="row g-0">
                <div class="col-md-6">
                    <div class="login-left">
                        <div class="logo-circle">🏛️</div>
                        <h2 class="text-center mb-2">Login</h2>
                        <p class="text-center text-muted mb-4">Kecamatan Kita</p>

                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show">
                                <i class="bi bi-exclamation-triangle-fill"></i> <?= $error ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label class="form-label">Username atau Email</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input type="text" class="form-control" name="email_or_username" placeholder="Masukkan username atau email" required autofocus>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                    <input type="password" class="form-control" name="password" placeholder="Masukkan password" required>
                                </div>
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="remember">
                                <label class="form-check-label" for="remember">Ingat Saya</label>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-box-arrow-in-right"></i> Masuk
                            </button>

                            <div class="text-center mt-3">
                                <span class="text-muted">Belum punya akun?</span>
                                <a href="register.php" class="text-decoration-none fw-bold"> Daftar di sini</a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="login-right text-center">
                        <h3 class="mb-4">Selamat Datang!</h3>
                        <p>Akses layanan administrasi kependudukan dengan mudah dan cepat</p>
                        <ul class="list-unstyled mt-4">
                            <li class="mb-3"><i class="bi bi-check-circle-fill text-success"></i> Pengajuan dokumen online</li>
                            <li class="mb-3"><i class="bi bi-check-circle-fill text-success"></i> Tracking status permohonan</li>
                            <li class="mb-3"><i class="bi bi-check-circle-fill text-success"></i> Notifikasi real-time</li>
                            <li class="mb-3"><i class="bi bi-check-circle-fill text-success"></i> Proses cepat & transparan</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>