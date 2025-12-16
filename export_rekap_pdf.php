<?php
// 1. Load library mPDF dari folder vendor
require_once __DIR__ . '/vendor/autoload.php';

// 2. Sertakan koneksi dan logika yang sudah ada
include "koneksi.php";
session_start();
date_default_timezone_set('Asia/Jakarta');

// (Letakkan kode proteksi role Anda di sini jika perlu)

// 3. Ambil data dari database
$customWeek = $_GET['week'] ?? date('Y-m-d');
$start_date = date('Y-m-d', strtotime("monday this week", strtotime($customWeek)));
$end_date   = date('Y-m-d', strtotime("saturday this week", strtotime($customWeek)));
$filter_shift = $_GET['shift'] ?? '';

$query = mysqli_query($conn, "
    SELECT k.nip, k.nama, k.jabatan, h.tanggal_masuk, h.waktu_masuk, s.nama_shift, s.jam_mulai, s.jam_selesai
    FROM data_kehadiran h
    JOIN data_karyawan k ON k.id_karyawan = h.id_karyawan
    LEFT JOIN data_jadwal_kerja j ON j.id_karyawan = h.id_karyawan AND j.tanggal = h.tanggal_masuk
    LEFT JOIN data_shift s ON s.id_shift = j.shift
    WHERE h.tanggal_masuk BETWEEN '$start_date' AND '$end_date'
    " . ($filter_shift ? " AND s.nama_shift = '$filter_shift'" : "") . "
    ORDER BY h.tanggal_masuk ASC, k.nama ASC
");

// 4. Buat objek mPDF
$mpdf = new \Mpdf\Mpdf();

// 5. Buat konten HTML yang akan dicetak
$html = '
<!DOCTYPE html>
<html>
<head>
    <title>Rekap Kehadiran</title>
    <style>
        body { font-family: sans-serif; }
        h2 { text-align: center; color: #00796b; }
        table { width: 100%; border-collapse: collapse; font-size: 12px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
        th { background: #00796b; color: white; }
        .late { background-color: #ffebee; }
    </style>
</head>
<body>
    <h2>Rekap Kehadiran (' . date("d M Y", strtotime($start_date)) . ' - ' . date("d M Y", strtotime($end_date)) . ')</h2>
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
        </tr>';

$no = 1;
while ($row = mysqli_fetch_assoc($query)) {
    // --- LOGIKA PERHITUNGAN STATUS YANG BENAR DITEMPEL DI SINI ---
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
    // --- BATAS LOGIKA PERHITUNGAN STATUS ---

    $html .= '
        <tr class="' . $isLateClass . '">
            <td>' . $no++ . '</td>
            <td>' . $row['nip'] . '</td>
            <td>' . $row['nama'] . '</td>
            <td>' . $row['jabatan'] . '</td>
            <td>' . date("d-m-Y", strtotime($row['tanggal_masuk'])) . '</td>
            <td>' . (!empty($row['waktu_masuk']) ? date("H:i", strtotime($row['waktu_masuk'])) : '-') . '</td>
            <td>' . ($row['nama_shift'] ?? '-') . '</td>
            <td>' . (!empty($row['jam_mulai']) ? substr($row['jam_mulai'], 0, 5) . ' - ' . substr($row['jam_selesai'], 0, 5) : '-') . '</td>
            <td>' . $status . '</td>
        </tr>';
}

$html .= '
    </table>
</body>
</html>';


// 6. Tulis HTML ke mPDF dan output sebagai file PDF
$mpdf->WriteHTML($html);
$mpdf->Output('rekap_kehadiran.pdf', 'D');

exit;
?>