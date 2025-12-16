<?php
include "koneksi.php";
session_start();
date_default_timezone_set('Asia/Jakarta');

// Proteksi hanya karyawan
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'karyawan') {
    header("Location: index.php");
    exit;
}

// Ambil NIP dari session
$nip = $_SESSION['nip'] ?? null;

// Ambil data id_karyawan
$getId = mysqli_query($conn, "SELECT id_karyawan FROM data_karyawan WHERE nip='$nip' AND status='aktif'");
$karyawan = mysqli_fetch_assoc($getId);
$id_karyawan = $karyawan['id_karyawan'] ?? null;

if (!$id_karyawan) {
    die("❌ Data karyawan tidak ditemukan.");
}

// Ambil filter bulan & tahun
$bulan = $_GET['bulan'] ?? date("m");
$tahun = $_GET['tahun'] ?? date("Y");

// Ambil data kehadiran + data shift dari jadwal
$query = mysqli_query($conn, "
    SELECT k.tanggal_masuk, k.waktu_masuk, k.status_kehadiran, s.jam_mulai
    FROM data_kehadiran k
    LEFT JOIN data_jadwal_kerja j ON j.id_karyawan = k.id_karyawan AND j.tanggal = k.tanggal_masuk
    LEFT JOIN data_shift s ON s.id_shift = j.shift
    WHERE k.id_karyawan = '$id_karyawan' AND MONTH(k.tanggal_masuk) = '$bulan' AND YEAR(k.tanggal_masuk) = '$tahun'
    ORDER BY k.tanggal_masuk DESC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Kehadiran Pribadi</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f5f8fa;
            padding: 20px;
        }
        h2 {
            color: #00796b;
            text-align: center;
        }
        .filter-form {
            text-align: center;
            margin-bottom: 20px;
        }
        .filter-form select, .filter-form button {
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 14px;
            margin: 0 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            margin-top: 10px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ccc;
            text-align: center;
        }
        th {
            background: #00796b;
            color: white;
        }
        tr:nth-child(even) { background-color: #f1f1f1; }
        tr:hover { background-color: #e0f7fa; }
        .late { background-color: #ffe0e0; }
    </style>
</head>
<body>

<h2>📋 Rekap Kehadiran Anda</h2>

<div class="filter-form">
    <form method="get">
        <label for="bulan">Bulan:</label>
        <select name="bulan" id="bulan">
            <?php for ($i = 1; $i <= 12; $i++):
                $value = str_pad($i, 2, "0", STR_PAD_LEFT); ?>
                <option value="<?= $value ?>" <?= ($bulan == $value) ? "selected" : "" ?>>
                    <?= date("F", mktime(0, 0, 0, $i, 10)) ?>
                </option>
            <?php endfor; ?>
        </select>
        <label for="tahun">Tahun:</label>
        <select name="tahun" id="tahun">
            <?php for ($y = date("Y") - 2; $y <= date("Y") + 1; $y++): ?>
                <option value="<?= $y ?>" <?= ($tahun == $y) ? "selected" : "" ?>><?= $y ?></option>
            <?php endfor; ?>
        </select>
        <button type="submit">Tampilkan</button>
    </form>
</div>

<table>
    <tr>
        <th>Tanggal</th>
        <th>Waktu Masuk</th>
        <th>Status</th>
        <th>Keterangan</th>
    </tr>
    <?php 
    $hadir = $terlambat = $tanpa_jadwal = 0;
    while ($row = mysqli_fetch_assoc($query)):
        $keterangan = "-";
        $isLateClass = "";

        if ($row['status_kehadiran'] == 'hadir') {
            $hadir++;
        }

        if ($row['jam_mulai']) {
            $telat = (strtotime($row['waktu_masuk']) - strtotime($row['jam_mulai'])) / 60;
            if ($telat > 0) {
                $keterangan = "Terlambat (" . round($telat) . " menit)";
                $isLateClass = "late";
                $terlambat++;
            } else {
                $keterangan = "Tepat Waktu";
            }
        } else {
            $keterangan = "Tidak Ada Jadwal";
            $tanpa_jadwal++;
        }
    ?>
    <tr class="<?= $isLateClass ?>">
        <td><?= date("d-m-Y", strtotime($row['tanggal_masuk'])) ?></td>
        <td><?= date("H:i", strtotime($row['waktu_masuk'])) ?></td>
        <td><?= ucfirst($row['status_kehadiran']) ?></td>
        <td><?= $keterangan ?></td>
    </tr>
    <?php endwhile; ?>
</table>

<?php
// Ambil total jadwal dari tabel data_jadwal_kerja
$qJadwal = mysqli_query($conn, "
    SELECT COUNT(*) as total 
    FROM data_jadwal_kerja 
    WHERE id_karyawan='$id_karyawan' AND MONTH(tanggal)='$bulan' AND YEAR(tanggal)='$tahun'
");
$dJadwal = mysqli_fetch_assoc($qJadwal);
$total_jadwal = $dJadwal['total'] ?? 0;
?>

<div style="margin-top: 30px; background: #f1f8e9; padding: 20px; border-radius: 8px; font-size: 14px;">
    <h3 style="color:#00796b;">📈 Ringkasan Kehadiran Bulan Ini:</h3>
    <ul>
        <li><strong>Total Hari Terjadwal:</strong> <?= $total_jadwal ?> hari</li>
        <li><strong>Total Hadir:</strong> <?= $hadir ?> kali</li>
        <li><strong>Total Terlambat:</strong> <?= $terlambat ?> kali</li>
        <li><strong>Tanpa Jadwal:</strong> <?= $tanpa_jadwal ?> kali</li>
    </ul>
</div>

<div style="text-align:center; margin-top: 20px;">
    <a href="dashboard.php" style="text-decoration: none; color: #00796b; font-weight: bold;">
        ← Kembali ke Dashboard
    </a>
</div>

</body>
</html>
