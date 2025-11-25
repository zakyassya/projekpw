<?php
// config.php
session_start();

// Konfigurasi Database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'kependudukan');

// Koneksi ke database
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Cek koneksi
if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Set charset UTF-8
mysqli_set_charset($conn, "utf8");

// Fungsi untuk mencegah SQL Injection
function clean_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = mysqli_real_escape_string($conn, $data);
    return $data;
}

// Fungsi untuk cek login
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Fungsi untuk cek role admin
function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Fungsi redirect jika belum login
function require_login() {
    if (!is_logged_in()) {
        header("Location: login.php");
        exit();
    }
}

// Fungsi untuk upload file
function upload_file($file, $target_dir = "../uploads/") {
    $target_file = $target_dir . time() . "_" . basename($file["name"]);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
    // Cek apakah file adalah gambar atau PDF
    $allowed_types = array('jpg', 'jpeg', 'png', 'pdf');
    
    if (!in_array($imageFileType, $allowed_types)) {
        return array('success' => false, 'message' => 'Hanya file JPG, JPEG, PNG, dan PDF yang diperbolehkan.');
    }
    
    // Cek ukuran file (max 2MB)
    if ($file["size"] > 2000000) {
        return array('success' => false, 'message' => 'Ukuran file terlalu besar. Maksimal 2MB.');
    }
    
    // Upload file
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return array('success' => true, 'filename' => basename($target_file));
    } else {
        return array('success' => false, 'message' => 'Terjadi kesalahan saat mengupload file.');
    }
}
?>