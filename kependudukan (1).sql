-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 25, 2025 at 06:15 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `kependudukan`
--

-- --------------------------------------------------------

--
-- Table structure for table `pengajuan_akta`
--

CREATE TABLE `pengajuan_akta` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `nama_anak` varchar(100) NOT NULL,
  `tempat_lahir` varchar(50) NOT NULL,
  `tanggal_lahir` date NOT NULL,
  `jenis_kelamin` enum('L','P') NOT NULL,
  `anak_ke` int(11) NOT NULL,
  `nama_ayah` varchar(100) NOT NULL,
  `nik_ayah` varchar(16) NOT NULL,
  `nama_ibu` varchar(100) NOT NULL,
  `nik_ibu` varchar(16) NOT NULL,
  `alamat` text NOT NULL,
  `telepon` varchar(15) NOT NULL,
  `file_surat_lahir` varchar(255) DEFAULT NULL,
  `file_kk` varchar(255) DEFAULT NULL,
  `file_ktp_ortu` varchar(255) DEFAULT NULL,
  `file_surat_nikah` varchar(255) DEFAULT NULL,
  `status` enum('pending','diproses','selesai','ditolak') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pengajuan_domisili`
--

CREATE TABLE `pengajuan_domisili` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `nik` varchar(16) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `alamat` text NOT NULL,
  `rt` varchar(5) NOT NULL,
  `rw` varchar(5) NOT NULL,
  `telepon` varchar(15) NOT NULL,
  `file_ktp` varchar(255) DEFAULT NULL,
  `file_kk` varchar(255) DEFAULT NULL,
  `file_surat_rt` varchar(255) DEFAULT NULL,
  `status` enum('pending','diproses','selesai','ditolak') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pengajuan_kk`
--

CREATE TABLE `pengajuan_kk` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `no_kk_lama` varchar(16) DEFAULT NULL,
  `nama_kepala_keluarga` varchar(100) NOT NULL,
  `alamat` text NOT NULL,
  `rt` varchar(5) NOT NULL,
  `rw` varchar(5) NOT NULL,
  `kelurahan` varchar(50) NOT NULL,
  `kecamatan` varchar(50) NOT NULL,
  `telepon` varchar(15) NOT NULL,
  `jenis_permohonan` enum('baru','perubahan','hilang') NOT NULL,
  `alasan` text DEFAULT NULL,
  `file_ktp_kepala` varchar(255) DEFAULT NULL,
  `file_kk_lama` varchar(255) DEFAULT NULL,
  `file_surat_nikah` varchar(255) DEFAULT NULL,
  `status` enum('pending','diproses','selesai','ditolak') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pengajuan_ktp`
--

CREATE TABLE `pengajuan_ktp` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `nik` varchar(16) NOT NULL,
  `no_kk` varchar(16) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `tempat_lahir` varchar(50) NOT NULL,
  `tanggal_lahir` date NOT NULL,
  `jenis_kelamin` enum('L','P') NOT NULL,
  `golongan_darah` varchar(3) DEFAULT NULL,
  `alamat` text NOT NULL,
  `agama` varchar(20) NOT NULL,
  `status_kawin` varchar(20) NOT NULL,
  `pekerjaan` varchar(50) NOT NULL,
  `telepon` varchar(15) NOT NULL,
  `file_kk` varchar(255) DEFAULT NULL,
  `pas_foto` varchar(255) DEFAULT NULL,
  `surat_pengantar` varchar(255) DEFAULT NULL,
  `status` enum('pending','diproses','selesai','ditolak') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pengajuan_ktp`
--

INSERT INTO `pengajuan_ktp` (`id`, `user_id`, `nik`, `no_kk`, `nama`, `tempat_lahir`, `tanggal_lahir`, `jenis_kelamin`, `golongan_darah`, `alamat`, `agama`, `status_kawin`, `pekerjaan`, `telepon`, `file_kk`, `pas_foto`, `surat_pengantar`, `status`, `created_at`) VALUES
(1, 3, '123456789', '', 'dap', '', '2025-11-24', 'L', NULL, 'Bantul', '', '', '', '', '1763996266_Screenshot 2024-09-26 000143.png', '1763996266_Screenshot 2024-09-26 144701.png', NULL, 'pending', '2025-11-24 08:57:46'),
(2, 3, '123456789', '', 'dap', '', '2025-11-24', 'L', NULL, 'Bantul', '', '', '', '', '1763996269_Screenshot 2024-09-26 000143.png', '1763996269_Screenshot 2024-09-26 144701.png', NULL, 'pending', '2025-11-24 08:57:49'),
(3, 3, '12345678910', '10987654321', 'Dapa Lindu', 'Bantul', '1999-09-09', 'L', 'Z', 'Bantulan', 'Islam', 'Kawin', 'Mancing', '085265487954', '1763996473_Screenshot 2024-09-26 000143.png', '1763996473_Screenshot 2024-09-26 144701.png', NULL, 'selesai', '2025-11-24 09:01:13');

-- --------------------------------------------------------

--
-- Table structure for table `pengajuan_pindah`
--

CREATE TABLE `pengajuan_pindah` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `nik` varchar(16) NOT NULL,
  `no_kk` varchar(16) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `alamat_asal` text NOT NULL,
  `alamat_tujuan` text NOT NULL,
  `rt_tujuan` varchar(5) NOT NULL,
  `rw_tujuan` varchar(5) NOT NULL,
  `kelurahan_tujuan` varchar(50) NOT NULL,
  `kecamatan_tujuan` varchar(50) NOT NULL,
  `alasan_pindah` text NOT NULL,
  `telepon` varchar(15) NOT NULL,
  `file_ktp` varchar(255) DEFAULT NULL,
  `file_kk` varchar(255) DEFAULT NULL,
  `file_surat_pengantar` varchar(255) DEFAULT NULL,
  `status` enum('pending','diproses','selesai','ditolak') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pengajuan_usaha`
--

CREATE TABLE `pengajuan_usaha` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `nik` varchar(16) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `nama_usaha` varchar(100) NOT NULL,
  `jenis_usaha` varchar(50) NOT NULL,
  `alamat_usaha` text NOT NULL,
  `rt` varchar(5) NOT NULL,
  `rw` varchar(5) NOT NULL,
  `modal_usaha` decimal(15,2) DEFAULT NULL,
  `jumlah_karyawan` int(11) DEFAULT NULL,
  `telepon` varchar(15) NOT NULL,
  `file_ktp` varchar(255) DEFAULT NULL,
  `file_kk` varchar(255) DEFAULT NULL,
  `file_foto_usaha` varchar(255) DEFAULT NULL,
  `file_surat_pernyataan` varchar(255) DEFAULT NULL,
  `status` enum('pending','diproses','selesai','ditolak') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `nama_lengkap`, `email`, `role`, `created_at`) VALUES
(3, 'coba', '$2y$10$s.FGcbmBoiZnJ6y6LDx6fe5qM3RweuDa0u8t03b9HrNv4Ry2KoPJu', 'coba', 'coba@gmail.com', 'user', '2025-11-24 14:12:24'),
(4, 'admin', '$2y$10$EkkuBQ.IlVsFF.duauXtquA4dZllD/091x9TVz6wl/xAKsTRwhKLC', 'Administrator Kecamatan', 'admin@kecamatan.go.id', 'admin', '2025-11-25 16:04:41'),
(5, 'ngadimin', 'min123', 'ngadimin', 'ngadimin@kependudukan.com', 'admin', '2025-11-25 16:20:34');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `pengajuan_akta`
--
ALTER TABLE `pengajuan_akta`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `pengajuan_domisili`
--
ALTER TABLE `pengajuan_domisili`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `pengajuan_kk`
--
ALTER TABLE `pengajuan_kk`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `pengajuan_ktp`
--
ALTER TABLE `pengajuan_ktp`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `pengajuan_pindah`
--
ALTER TABLE `pengajuan_pindah`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `pengajuan_usaha`
--
ALTER TABLE `pengajuan_usaha`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `pengajuan_akta`
--
ALTER TABLE `pengajuan_akta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pengajuan_domisili`
--
ALTER TABLE `pengajuan_domisili`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pengajuan_kk`
--
ALTER TABLE `pengajuan_kk`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pengajuan_ktp`
--
ALTER TABLE `pengajuan_ktp`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `pengajuan_pindah`
--
ALTER TABLE `pengajuan_pindah`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pengajuan_usaha`
--
ALTER TABLE `pengajuan_usaha`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `pengajuan_akta`
--
ALTER TABLE `pengajuan_akta`
  ADD CONSTRAINT `pengajuan_akta_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `pengajuan_domisili`
--
ALTER TABLE `pengajuan_domisili`
  ADD CONSTRAINT `pengajuan_domisili_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pengajuan_kk`
--
ALTER TABLE `pengajuan_kk`
  ADD CONSTRAINT `pengajuan_kk_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `pengajuan_ktp`
--
ALTER TABLE `pengajuan_ktp`
  ADD CONSTRAINT `pengajuan_ktp_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `pengajuan_pindah`
--
ALTER TABLE `pengajuan_pindah`
  ADD CONSTRAINT `pengajuan_pindah_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `pengajuan_usaha`
--
ALTER TABLE `pengajuan_usaha`
  ADD CONSTRAINT `pengajuan_usaha_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
