-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 10, 2025 at 04:13 PM
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
-- Database: `sikawan`
--

-- --------------------------------------------------------

--
-- Table structure for table `data_jadwal_kerja`
--

CREATE TABLE `data_jadwal_kerja` (
  `id_jadwal` int(11) NOT NULL,
  `id_karyawan` int(11) DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `shift` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `data_jadwal_kerja`
--

INSERT INTO `data_jadwal_kerja` (`id_jadwal`, `id_karyawan`, `tanggal`, `shift`) VALUES
(4, 1, '2025-07-10', 4),
(5, 1, '2025-07-11', 4),
(6, 1, '2025-07-12', 4),
(7, 2, '2025-07-10', 2),
(8, 2, '2025-07-11', 2),
(9, 2, '2025-07-12', 2);

-- --------------------------------------------------------

--
-- Table structure for table `data_karyawan`
--

CREATE TABLE `data_karyawan` (
  `id_karyawan` int(11) NOT NULL,
  `nip` varchar(20) DEFAULT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `jabatan` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `status` enum('aktif','nonaktif') DEFAULT NULL,
  `role` enum('karyawan') DEFAULT 'karyawan',
  `tanggal_masuk` date DEFAULT NULL,
  `tanggal_keluar` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `data_karyawan`
--

INSERT INTO `data_karyawan` (`id_karyawan`, `nip`, `nama`, `jabatan`, `password`, `status`, `role`, `tanggal_masuk`, `tanggal_keluar`) VALUES
(1, '1987654321', 'Rudi Santoso', 'Staff IT', 'rudi123', 'aktif', 'karyawan', '2023-01-10', NULL),
(2, '1987654322', 'Ayu Lestari', 'Staff HR', 'ayu123', 'aktif', 'karyawan', '2023-03-15', NULL),
(3, '1987654323', 'Dani Permana', 'Staff Keuangan', 'dani123', 'nonaktif', 'karyawan', '2022-07-01', '2023-12-31');

-- --------------------------------------------------------

--
-- Table structure for table `data_kehadiran`
--

CREATE TABLE `data_kehadiran` (
  `id_absen` int(11) NOT NULL,
  `id_karyawan` int(11) DEFAULT NULL,
  `tanggal_masuk` date DEFAULT NULL,
  `waktu_masuk` time DEFAULT NULL,
  `status_kehadiran` enum('hadir','izin','sakit','alpha') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `data_kehadiran`
--

INSERT INTO `data_kehadiran` (`id_absen`, `id_karyawan`, `tanggal_masuk`, `waktu_masuk`, `status_kehadiran`) VALUES
(1, 1, '2025-07-10', NULL, 'hadir');

-- --------------------------------------------------------

--
-- Table structure for table `data_perizinan`
--

CREATE TABLE `data_perizinan` (
  `id_izin` int(11) NOT NULL,
  `id_karyawan` int(11) DEFAULT NULL,
  `jenis` enum('cuti','sakit','izin') DEFAULT NULL,
  `alasan` text DEFAULT NULL,
  `tanggal_pengajuan` date DEFAULT NULL,
  `tanggal_mulai` date DEFAULT NULL,
  `tanggal_selesai` date DEFAULT NULL,
  `status` enum('menunggu','disetujui','ditolak') DEFAULT 'menunggu',
  `tanggal_persetujuan` date DEFAULT NULL,
  `disetujui_oleh` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `data_perizinan`
--

INSERT INTO `data_perizinan` (`id_izin`, `id_karyawan`, `jenis`, `alasan`, `tanggal_pengajuan`, `tanggal_mulai`, `tanggal_selesai`, `status`, `tanggal_persetujuan`, `disetujui_oleh`) VALUES
(1, 1, 'cuti', 'Stuban dengan Universitas Garut', '2025-07-10', '2025-07-15', '2025-07-16', 'disetujui', '2025-07-10', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `data_shift`
--

CREATE TABLE `data_shift` (
  `id_shift` int(11) NOT NULL,
  `nama_shift` varchar(50) DEFAULT NULL,
  `jam_mulai` time DEFAULT NULL,
  `jam_selesai` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `data_shift`
--

INSERT INTO `data_shift` (`id_shift`, `nama_shift`, `jam_mulai`, `jam_selesai`) VALUES
(1, 'Pagi', '08:00:00', '16:00:00'),
(2, 'Siang', '13:00:00', '21:00:00'),
(3, 'Malam', '21:00:00', '05:00:00'),
(4, 'Full Day', '08:00:00', '20:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `data_user`
--

CREATE TABLE `data_user` (
  `id_user` int(11) NOT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `jabatan` varchar(50) DEFAULT NULL,
  `role` enum('admin','atasan') DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `data_user`
--

INSERT INTO `data_user` (`id_user`, `nama`, `jabatan`, `role`, `password`) VALUES
(1, 'Admin HR', 'HR Manager', 'admin', 'admin123'),
(2, 'Juts Lucu', 'Direktur Utama', 'atasan', '12345');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `data_jadwal_kerja`
--
ALTER TABLE `data_jadwal_kerja`
  ADD PRIMARY KEY (`id_jadwal`),
  ADD KEY `id_karyawan` (`id_karyawan`),
  ADD KEY `fk_shift` (`shift`);

--
-- Indexes for table `data_karyawan`
--
ALTER TABLE `data_karyawan`
  ADD PRIMARY KEY (`id_karyawan`),
  ADD UNIQUE KEY `nip` (`nip`);

--
-- Indexes for table `data_kehadiran`
--
ALTER TABLE `data_kehadiran`
  ADD PRIMARY KEY (`id_absen`),
  ADD KEY `id_karyawan` (`id_karyawan`);

--
-- Indexes for table `data_perizinan`
--
ALTER TABLE `data_perizinan`
  ADD PRIMARY KEY (`id_izin`),
  ADD KEY `id_karyawan` (`id_karyawan`),
  ADD KEY `disetujui_oleh` (`disetujui_oleh`);

--
-- Indexes for table `data_shift`
--
ALTER TABLE `data_shift`
  ADD PRIMARY KEY (`id_shift`);

--
-- Indexes for table `data_user`
--
ALTER TABLE `data_user`
  ADD PRIMARY KEY (`id_user`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `data_jadwal_kerja`
--
ALTER TABLE `data_jadwal_kerja`
  MODIFY `id_jadwal` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `data_karyawan`
--
ALTER TABLE `data_karyawan`
  MODIFY `id_karyawan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `data_kehadiran`
--
ALTER TABLE `data_kehadiran`
  MODIFY `id_absen` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `data_perizinan`
--
ALTER TABLE `data_perizinan`
  MODIFY `id_izin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `data_shift`
--
ALTER TABLE `data_shift`
  MODIFY `id_shift` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `data_user`
--
ALTER TABLE `data_user`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `data_jadwal_kerja`
--
ALTER TABLE `data_jadwal_kerja`
  ADD CONSTRAINT `data_jadwal_kerja_ibfk_1` FOREIGN KEY (`id_karyawan`) REFERENCES `data_karyawan` (`id_karyawan`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_shift` FOREIGN KEY (`shift`) REFERENCES `data_shift` (`id_shift`) ON DELETE SET NULL;

--
-- Constraints for table `data_kehadiran`
--
ALTER TABLE `data_kehadiran`
  ADD CONSTRAINT `data_kehadiran_ibfk_1` FOREIGN KEY (`id_karyawan`) REFERENCES `data_karyawan` (`id_karyawan`) ON DELETE CASCADE;

--
-- Constraints for table `data_perizinan`
--
ALTER TABLE `data_perizinan`
  ADD CONSTRAINT `data_perizinan_ibfk_1` FOREIGN KEY (`id_karyawan`) REFERENCES `data_karyawan` (`id_karyawan`) ON DELETE CASCADE,
  ADD CONSTRAINT `data_perizinan_ibfk_2` FOREIGN KEY (`disetujui_oleh`) REFERENCES `data_user` (`id_user`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
