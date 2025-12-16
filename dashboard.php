<?php
session_start();
include "koneksi.php";

// Validasi session login
if (!isset($_SESSION['role']) || !isset($_SESSION['nama'])) {
    header("Location: index.php");
    exit;
}

$role = $_SESSION['role'];
$nama = $_SESSION['nama'];
$jabatan = "-";

// Ambil jabatan berdasarkan role & id
if ($role == 'karyawan') {
    $id = $_SESSION['id_karyawan'];
    $q = mysqli_query($conn, "SELECT jabatan FROM data_karyawan WHERE id_karyawan='$id'");
    $d = mysqli_fetch_assoc($q);
    $jabatan = $d ? $d['jabatan'] : '-';
} else {
    $id = $_SESSION['id_user'];
    $q = mysqli_query($conn, "SELECT jabatan FROM data_user WHERE id_user='$id'");
    $d = mysqli_fetch_assoc($q);
    $jabatan = $d ? $d['jabatan'] : '-';
}

// Hitung jumlah karyawan aktif (khusus admin dan atasan)
$jumlahKaryawan = 0;
if ($role == 'admin' || $role == 'atasan') {
    $q = mysqli_query($conn, "SELECT COUNT(*) as total FROM data_karyawan WHERE status='aktif'");
    $d = mysqli_fetch_assoc($q);
    $jumlahKaryawan = $d ? $d['total'] : 0;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - SiKawan</title>
    <style>
        body {
            background: linear-gradient(to right, #e0f2f1, #f1f8e9);
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            padding: 0;
        }

        .navbar {
            background: #004d40;
            color: white;
            padding: 25px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }

        .navbar h1 {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
        }

        .navbar .user-info {
            text-align: right;
            font-size: 14px;
            line-height: 1.5;
        }

        .navbar .user-info span {
            display: block;
        }

        .navbar .user-info a {
            color: #ffccbc;
            text-decoration: underline;
            font-weight: bold;
        }

        .content {
            padding: 40px 30px;
        }

        h2 {
            color: #00695c;
        }

        .menu {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }

        .menu a {
            text-decoration: none;
            background-color: white;
            border-radius: 16px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 6px 15px rgba(0,0,0,0.1);
            color: #004d40;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .menu a:hover {
            background-color: #004d40;
            color: white;
        }

        .footer {
            text-align: center;
            margin-top: 50px;
            color: #555;
            font-size: 13px;
        }

        .summary {
            margin-top: 30px;
            background: #ffffff;
            padding: 25px;
            border-radius: 16px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.05);
            color: #00796b;
            font-weight: bold;
            font-size: 16px;
        }
    </style>
</head>
<body>

<div class="navbar">
    <h1>Dashboard SiKawan</h1>
    <div class="user-info">
        <span><strong><?= htmlspecialchars($nama) ?></strong> (<?= ucfirst($role) ?>)</span>
        <span><?= htmlspecialchars($jabatan) ?></span>
        <a href="index.php">Logout</a>
    </div>
</div>

<div class="content">
    <h2>Selamat datang di Sistem Kehadiran Karyawan</h2>

    <?php if ($role == 'admin' || $role == 'atasan'): ?>
        <div class="summary">
            Jumlah Karyawan Aktif: <?= $jumlahKaryawan ?>
        </div>
    <?php endif; ?>

    <div class="menu">
        <?php if ($role == 'karyawan'): ?>
            <a href="absensi.php">📝 Absensi</a>
            <a href="izin.php">📤 Pengajuan Izin</a>
            <a href="rekap_saya.php">📊 Rekap Pribadi</a>
        <?php endif; ?>

        <?php if ($role == 'admin'): ?>
            <a href="jadwal.php">📅 Manajemen Jadwal Kerja</a>
            <a href="rekap.php">📊 Kehadiran</a>
            <a href="data_karyawan.php">👥 Data Karyawan</a>
            <a href="verifikasi_izin.php">✅ Pengajuan dan Persetujuan Izin</a>
        <?php endif; ?>

        <?php if ($role == 'atasan'): ?>
            <a href="verifikasi_izin.php">✅ Pengajuan dan Persetujuan Izin</a>
            <a href="rekap_atasan.php">📊 Kehadiran</a>
            <a href="data_karyawan.php">👥 Data Karyawan</a>
        <?php endif; ?>
    </div>

    <div class="footer">
        &copy; <?= date("Y") ?> SiKawan - Sistem Kehadiran Karyawan
    </div>
</div>

</body>
</html>
