<?php
// Memasukkan autoloader dari Composer
require 'vendor/autoload.php';

// Memasukkan class yang akan kita gunakan
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\DataType; // <-- Tambahkan 'use' untuk DataType

// --- Kode koneksi database dan query Anda tetap sama ---
include "koneksi.php";
session_start();
date_default_timezone_set('Asia/Jakarta');

// (Letakkan kode proteksi role Anda di sini jika perlu)
// if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'atasan')) { ... }

// Ambil filter dari URL atau gunakan default
$customWeek = $_GET['week'] ?? date('Y-m-d');
$start_date = date('Y-m-d', strtotime("monday this week", strtotime($customWeek)));
$end_date   = date('Y-m-d', strtotime("saturday this week", strtotime($customWeek)));
$filter_shift = $_GET['shift'] ?? '';

// Query SQL menggunakan rentang tanggal mingguan
$query = mysqli_query($conn, "
    SELECT u.nip, u.nama, u.jabatan, a.tanggal_masuk, a.waktu_masuk, s.nama_shift, s.jam_mulai, s.jam_selesai
    FROM data_kehadiran a
    JOIN data_karyawan u ON u.id_karyawan = a.id_karyawan
    LEFT JOIN data_jadwal_kerja j ON j.id_karyawan = a.id_karyawan AND j.tanggal = a.tanggal_masuk
    LEFT JOIN data_shift s ON s.id_shift = j.shift
    WHERE a.tanggal_masuk BETWEEN '$start_date' AND '$end_date'
    " . ($filter_shift ? " AND s.nama_shift = '$filter_shift'" : "") . "
    ORDER BY a.tanggal_masuk ASC, u.nama ASC
");

// --- Mulai membuat file Excel ---

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Rekap Kehadiran');

// Menulis Header Kolom
$sheet->setCellValue('A1', 'No');
$sheet->setCellValue('B1', 'NIP');
$sheet->setCellValue('C1', 'Nama');
$sheet->setCellValue('D1', 'Jabatan');
$sheet->setCellValue('E1', 'Tanggal');
$sheet->setCellValue('F1', 'Waktu Masuk');
$sheet->setCellValue('G1', 'Shift');
$sheet->setCellValue('H1', 'Rentang Shift');
$sheet->setCellValue('I1', 'Status');

// Memberi Warna pada Header
$headerStyle = [
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '00796B']],
];
$sheet->getStyle('A1:I1')->applyFromArray($headerStyle);

// Menulis data dari database ke Excel
$rowNumber = 2; // Mulai dari baris ke-2
$no = 1;
while ($row = mysqli_fetch_assoc($query)) {
    
    // Logika perhitungan status keterlambatan
    $status = "Tidak Ada Jadwal";
    $isLate = false;

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
                $isLate = true;
            } else {
                $status = "Tepat Waktu";
            }
        } else {
            $status = "Belum Absen Masuk";
        }
    }

    $sheet->setCellValue('A' . $rowNumber, $no++);
    // --- PERBAIKAN DI SINI ---
    // Gunakan konstanta DataType::TYPE_STRING untuk mengatur format NIP sebagai Teks
    $sheet->setCellValueExplicit('B' . $rowNumber, $row['nip'], DataType::TYPE_STRING);
    $sheet->setCellValue('C' . $rowNumber, $row['nama']);
    $sheet->setCellValue('D' . $rowNumber, $row['jabatan']);
    $sheet->setCellValue('E' . $rowNumber, $row['tanggal_masuk']);
    $sheet->setCellValue('F' . $rowNumber, !empty($row['waktu_masuk']) ? date("H:i", strtotime($row['waktu_masuk'])) : '-');
    $sheet->setCellValue('G' . $rowNumber, $row['nama_shift'] ?? '-');
    $sheet->setCellValue('H' . $rowNumber, !empty($row['jam_mulai']) ? substr($row['jam_mulai'], 0, 5) . " - " . substr($row['jam_selesai'], 0, 5) : '-');
    $sheet->setCellValue('I' . $rowNumber, $status);

    // Memberi Warna pada Baris Berdasarkan Kondisi
    if ($isLate) {
        $lateStyle = [
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFEBEE']],
        ];
        $sheet->getStyle('A' . $rowNumber . ':I' . $rowNumber)->applyFromArray($lateStyle);
    }
    
    $rowNumber++;
}

// Mengatur lebar kolom agar otomatis
foreach (range('A', 'I') as $columnID) {
    $sheet->getColumnDimension($columnID)->setAutoSize(true);
}

// --- Mengirim file Excel ke Browser ---
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="rekap_kehadiran.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;

?>