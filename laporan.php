<?php include 'koneksi.php'; ?>
<!DOCTYPE html>
<html>
<head>
  <title>Laporan Pemesanan</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<header><h1>Laporan Pemesanan</h1></header>
<div class="container">
  <h2>📄 Data Pemesanan</h2>
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

            if ($row['jam_mulai'] && $row['jam_selesai']) {
                $masuk = strtotime($row['waktu_masuk']);
                $mulai = strtotime($row['jam_mulai']);
                $selesai = strtotime($row['jam_selesai']);

                if ($selesai < $mulai) {
                    $selesai = strtotime('+1 day', $selesai);
                    if ($masuk < $mulai) {
                        $masuk = strtotime('+1 day', $masuk);
                    }
                }

                $terlambat = ($masuk - $mulai) / 60;
                if ($terlambat > 0) {
                    $status = "Terlambat (" . floor($terlambat) . " menit)";
                    $isLateClass = "late";
                } else {
                    $status = "Tepat Waktu";
                }
            }

            ?>
            <tr class="<?= $isLateClass ?>">
                <td><?= $no++ ?></td>
                <td><?= $row['nip'] ?></td>
                <td><?= $row['nama'] ?></td>
                <td><?= $row['jabatan'] ?></td>
                <td><?= date("d-m-Y", strtotime($row['tanggal_masuk'])) ?></td>
                <td><?= $row['waktu_masuk'] ? date("H:i", strtotime($row['waktu_masuk'])) : '-' ?></td>
                <td><?= $row['nama_shift'] ?? '-' ?></td>
                <td><?= ($row['jam_mulai'] && $row['jam_selesai']) ? substr($row['jam_mulai'], 0, 5) . ' - ' . substr($row['jam_selesai'], 0, 5) : '-' ?></td>
                <td><?= $status ?></td>
            </tr>
        <?php endwhile; ?>
  </table>
  <button class="print-btn" onclick="window.print()">🖨 Cetak</button><br><br>
  <a class="btn-link" href="dashboard.php">⬅ Kembali</a>
</div>
</body>
</html>