<?php
include "koneksi.php";
session_start();
date_default_timezone_set('Asia/Jakarta');

// Proteksi halaman untuk role 'admin' dan 'atasan'
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'atasan')) {
    header("Location: index.php");
    exit;
}

// Logika untuk menentukan rentang tanggal 6 hari (Senin - Sabtu)
$customWeek = $_GET['week'] ?? date('Y-m-d');
$start_date = date('Y-m-d', strtotime("monday this week", strtotime($customWeek)));
$end_date   = date('Y-m-d', strtotime("saturday this week", strtotime($customWeek)));
$filter_shift = $_GET['shift'] ?? '';

// Query SQL untuk mengambil data berdasarkan rentang tanggal mingguan
$query = mysqli_query($conn, "
    SELECT 
        k.nip, k.nama, k.jabatan, 
        h.tanggal_masuk, 
        h.waktu_masuk,
        h.status_kehadiran,
        s.nama_shift, s.jam_mulai, s.jam_selesai,
        j.tanggal AS tanggal_jadwal
    FROM data_kehadiran h
    JOIN data_karyawan k ON k.id_karyawan = h.id_karyawan
    LEFT JOIN data_jadwal_kerja j ON j.id_karyawan = h.id_karyawan AND j.tanggal = h.tanggal_masuk
    LEFT JOIN data_shift s ON s.id_shift = j.shift
    WHERE h.tanggal_masuk BETWEEN '$start_date' AND '$end_date'
    " . ($filter_shift ? " AND s.nama_shift = '$filter_shift'" : "") . "
    ORDER BY h.tanggal_masuk ASC, k.nama ASC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Kehadiran</title>
    <style>
        body { background: #e3f2fd; font-family: 'Segoe UI', sans-serif; }
        .container { max-width: 1100px; margin: 60px auto; background: white; padding: 40px; border-radius: 16px; box-shadow: 0 6px 20px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #00796b; }
        table { width: 100%; border-collapse: collapse; font-size: 15px; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 12px; text-align: center; }
        th { background: #00796b; color: white; }
        .late { background-color: #ffebee; color: #b71c1c; }
        .filters { text-align: center; margin-bottom: 20px; }
        .filters select, .filters button, .filters input { padding: 8px; margin: 5px; border-radius: 8px; border: 1px solid #ccc; }
        .tombol-container { text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;}
        .tombol { text-decoration: none; display: inline-block; color: white; padding: 10px 15px; border-radius: 8px; font-weight: bold; margin: 0 10px; border: none; cursor: pointer; font-size: 14px;}
        .tombol.pdf { background-color: #c62828; }
        .tombol.excel { background-color: #1D6F42; }
        .back { text-align: center; margin-top: 20px; }
        .back a { text-decoration: none; color: #00796b; font-weight: bold; }
    </style>
</head>
<body>
<div class="container">
    <h2>📊 Rekap Kehadiran (<?= date("d M Y", strtotime($start_date)) ?> - <?= date("d M Y", strtotime($end_date)) ?>)</h2>

    <div class="filters">
        <form method="get" style="display:inline-block;">
            <label>Pilih Tanggal:</label>
            <input type="date" name="week" value="<?= htmlspecialchars($customWeek) ?>">
            <button type="submit">Tampilkan Minggu</button>
        </form>
        <form method="get" style="display:inline-block;">
            <input type="hidden" name="week" value="<?= htmlspecialchars($customWeek) ?>">
            <label>Shift:</label>
            <select name="shift" onchange="this.form.submit()">
                <option value="">Semua</option>
                <option value="Pagi" <?= ($filter_shift == 'Pagi') ? 'selected' : '' ?>>Pagi</option>
                <option value="Siang" <?= ($filter_shift == 'Siang') ? 'selected' : '' ?>>Siang</option>
                <option value="Malam" <?= ($filter_shift == 'Malam') ? 'selected' : '' ?>>Malam</option>
                <option value="Full Day" <?= ($filter_shift == 'Full Day') ? 'selected' : '' ?>>Full Day</option>
            </select>
        </form>
    </div>

    <table>
        <tr>
            <th>No</th>
            <th>NIP</th>
            <th>Nama</th>
            <th>Jabatan</th>
            <th>Tanggal</th>
            <th>Waktu Masuk</th>
            <th>Shift</th>
            <th>Rentang Shift</th>
            <th>Status</th>
        </tr>
        <?php 
        $no = 1;
        while ($row = mysqli_fetch_assoc($query)):
            $status = "Tidak Ada Jadwal";
            $isLateClass = "";

            if (!empty($row['jam_mulai']) && !empty($row['jam_selesai'])) {
                if (!empty($row['waktu_masuk'])) {
                    $tanggal_absensi = $row['tanggal_masuk'];
                    $mulai_shift   = strtotime($tanggal_absensi . ' ' . $row['jam_mulai']);
                    $waktu_masuk = strtotime($tanggal_absensi . ' ' . $row['waktu_masuk']);
                    
                    $toleransi = 5;
                    $terlambat_total = 0;

                    if ($waktu_masuk > $mulai_shift) {
                        $terlambat_total = floor(($waktu_masuk - $mulai_shift) / 60);
                    }

                    if ($terlambat_total > $toleransi) {
                        $terlambat_dihitung = $terlambat_total - $toleransi;
                        $status = "Terlambat (" . $terlambat_dihitung . " menit)";
                        $isLateClass = "late";
                    } else {
                        $status = "Tepat Waktu";
                    }
                } else {
                    $status = "Belum Absen Masuk";
                }
            }
        ?>
        <tr class="<?= $isLateClass ?>">
            <td><?= $no++ ?></td>
            <td><?= $row['nip'] ?></td>
            <td><?= $row['nama'] ?></td>
            <td><?= $row['jabatan'] ?></td>
            <td><?= date("d-m-Y", strtotime($row['tanggal_masuk'])) ?></td>
            <td><?= !empty($row['waktu_masuk']) ? date("H:i", strtotime($row['waktu_masuk'])) : '-' ?></td>
            <td><?= $row['nama_shift'] ?? '-' ?></td>
            <td><?= (!empty($row['jam_mulai']) && !empty($row['jam_selesai'])) ? substr($row['jam_mulai'], 0, 5) . ' - ' . substr($row['jam_selesai'], 0, 5) : '-' ?></td>
            <td><?= $status ?></td>
        </tr>
        <?php endwhile; ?>
    </table>

    <div class="tombol-container">
        <a href="export_rekap_pdf.php?week=<?= htmlspecialchars($customWeek) ?>&shift=<?= htmlspecialchars($filter_shift) ?>" target="_blank" class="tombol pdf">
            📄 Cetak PDF
        </a>
        <a href="export_rekap.php?week=<?= htmlspecialchars($customWeek) ?>&shift=<?= htmlspecialchars($filter_shift) ?>" target="_blank" class="tombol excel">
            📊 Cetak Excel
        </a>
    </div>

    <div class="back">
        <a href="dashboard.php">← Kembali ke Dashboard</a>
    </div>
</div>
</body>
</html>