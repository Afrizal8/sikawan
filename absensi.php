<?php
include "koneksi.php";
session_start();
date_default_timezone_set('Asia/Jakarta');

// Validasi hanya untuk role 'karyawan'
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'karyawan') {
    header("Location: index.php");
    exit;
}

$id_karyawan = $_SESSION['id_karyawan'];
$tanggal = date("Y-m-d");
$waktu_masuk = date("H:i:s");
$pesan = "";

// Cek apakah sudah absen hari ini
$cek = mysqli_query($conn, "SELECT * FROM data_kehadiran WHERE id_karyawan='$id_karyawan' AND tanggal_masuk='$tanggal'");
if (mysqli_num_rows($cek) > 0) {
    $pesan = "⚠️ Anda sudah melakukan absensi hari ini ($tanggal).";
} else {
    $simpan = mysqli_query($conn, "INSERT INTO data_kehadiran (id_karyawan, tanggal_masuk, waktu_masuk, status_kehadiran) VALUES ('$id_karyawan', '$tanggal', CURTIME(), 'hadir')");
    $pesan = $simpan ? "✅ Absensi berhasil dicatat untuk tanggal $tanggal." : "❌ Gagal menyimpan absensi.";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Absensi Harian - SiKawan</title>
    <style>
        body {
            background-color: #eef6f7;
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 500px;
            margin: 80px auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-align: center;
        }

        h2 {
            color: #00796b;
            margin-bottom: 20px;
        }

        .message {
            font-size: 16px;
            font-weight: 500;
            color: #333;
            background-color: #f0f0f0;
            border-left: 5px solid #00796b;
            padding: 15px;
            border-radius: 8px;
        }

        .back {
            margin-top: 30px;
        }

        .back a {
            color: #00796b;
            text-decoration: none;
            font-weight: 600;
        }

        .back a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Absensi Kehadiran</h2>
    <div class="message"><?= $pesan ?></div>

    <div class="back">
        <a href="dashboard.php">← Kembali ke Dashboard</a>
    </div>
</div>

</body>
</html>
