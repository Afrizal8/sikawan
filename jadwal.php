<?php
include "koneksi.php";
session_start();

// Validasi hanya admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "\u26d4\ufe0f Akses ditolak!";
    exit;
}

$pesan = "";

// Ambil data shift untuk form dan tabel
$shiftsForm = mysqli_query($conn, "SELECT * FROM data_shift ORDER BY id_shift ASC");
$shiftsTable = mysqli_query($conn, "SELECT * FROM data_shift ORDER BY id_shift ASC");

// Simpan atau Update jadwal kerja 
if (isset($_POST['simpan'])) {
    $nip     = mysqli_real_escape_string($conn, $_POST['nip']);
    $tanggal = $_POST['tanggal'];
    $shift   = $_POST['shift'];
    // Anda sudah mengatur 6 hari, jadi kita gunakan angka 6
    $jumlah_hari = 6; 

    // Ambil id_karyawan dari NIP
    $q = mysqli_query($conn, "SELECT id_karyawan FROM data_karyawan WHERE nip='$nip' AND status='aktif'");
    $d = mysqli_fetch_assoc($q);
    $id_karyawan = $d['id_karyawan'] ?? null;

    if ($id_karyawan) {
        for ($i = 0; $i < $jumlah_hari; $i++) {
            $tgl = date('Y-m-d', strtotime("+$i days", strtotime($tanggal)));
            
            // Cek apakah jadwal sudah ada untuk tanggal ini
            $cek = mysqli_query($conn, "SELECT id_jadwal FROM data_jadwal_kerja WHERE id_karyawan='$id_karyawan' AND tanggal='$tgl'");
            
            // Logika baru: INSERT jika belum ada, UPDATE jika sudah ada
            if (mysqli_num_rows($cek) == 0) {
                // Jika BELUM ADA jadwal, lakukan INSERT
                $action = mysqli_query($conn, "INSERT INTO data_jadwal_kerja (id_karyawan, tanggal, shift) VALUES ('$id_karyawan', '$tgl', '$shift')");
            } else {
                // Jika SUDAH ADA jadwal, lakukan UPDATE
                $action = mysqli_query($conn, "UPDATE data_jadwal_kerja SET shift = '$shift' WHERE id_karyawan = '$id_karyawan' AND tanggal = '$tgl'");
            }

            // Cek jika ada error saat insert atau update
            if (!$action) {
                $pesan = "Gagal memproses jadwal: " . mysqli_error($conn);
                break; // Hentikan loop jika ada error
            }
        }
        
        if ($pesan === "") {
            $pesan = "✅ Jadwal berhasil diproses mulai $tanggal.";
        }
    } else {
        $pesan = "❌ NIP tidak ditemukan atau status tidak aktif.";
    }
}

// Ambil data karyawan aktif
$karyawanAktif = mysqli_query($conn, "SELECT id_karyawan, nip, nama FROM data_karyawan WHERE status='aktif' ORDER BY nip ASC");

// Jadwal mingguan
$customWeek = $_GET['week'] ?? date('Y-m-d');
$startOfWeek = date('Y-m-d', strtotime("monday this week", strtotime($customWeek)));
$endOfWeek   = date('Y-m-d', strtotime("saturday this week", strtotime($startOfWeek))); 
$hari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']; 
$dateMap = [];
for ($i = 0; $i < 6; $i++) { 
    $dateMap[$hari[$i]] = date('Y-m-d', strtotime("+$i days", strtotime($startOfWeek)));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manajemen Jadwal - SiKawan</title>
    <style>
        body { background: #e3f2fd; font-family: sans-serif; }
        .container { max-width: 1100px; margin: 50px auto; background: white; padding: 30px; border-radius: 10px; }
        h2 { text-align: center; color: #00796b; }
        label { margin-top: 15px; display: block; font-weight: 600; }
        input, select, button { width: 100%; padding: 10px; margin-top: 5px; border-radius: 8px; border: 1px solid #ccc; }
        .message { text-align: center; color: #00796b; margin-top: 20px; font-weight: bold; }
        table { width: 100%; margin-top: 30px; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 12px; text-align: center; }
        th { background: #00796b; color: white; }
        .izin { background: #ffecb3; color: #d84315; font-weight: bold; }
        .back { margin-top: 40px; text-align: center; }
        .back a { text-decoration: none; color: #00796b; font-weight: bold; }
    </style>
</head>
<body>
<div class="container">
    <h2>Manajemen Jadwal Kerja</h2>

    <?php if ($pesan): ?><div class="message"><?= $pesan ?></div><?php endif; ?>

    <form method="post">
        <label for="nip">Pilih Karyawan</label>
        <select name="nip" id="nip" required>
            <option value="">-- Pilih --</option>
            <?php mysqli_data_seek($karyawanAktif, 0); while ($k = mysqli_fetch_assoc($karyawanAktif)): ?>
                <option value="<?= $k['nip'] ?>"><?= $k['nip'] ?> - <?= $k['nama'] ?></option>
            <?php endwhile; ?>
        </select>

        <label for="tanggal">Tanggal Mulai</label>
        <input type="date" name="tanggal" id="tanggal" required>

        <label for="shift">Pilih Shift</label>
        <select name="shift" id="shift" required>
            <option value="">-- Pilih Shift --</option>
            <?php while ($s = mysqli_fetch_assoc($shiftsForm)): ?>
                <option value="<?= $s['id_shift'] ?>"><?= $s['nama_shift'] ?> (<?= substr($s['jam_mulai'],0,5) ?> - <?= substr($s['jam_selesai'],0,5) ?>)</option>
            <?php endwhile; ?>
        </select>

        <button type="submit" name="simpan">Simpan Jadwal</button>
    </form>

    <h3 style="margin-top:40px;">📅 Jadwal Mingguan (<?= date("d M", strtotime($startOfWeek)) ?> - <?= date("d M Y", strtotime($endOfWeek)) ?>)</h3>
    <form method="get">
        <input type="date" name="week" value="<?= $customWeek ?>" required>
        <button type="submit">Tampilkan Minggu Ini</button>
    </form>

    <table>
        <tr>
            <th>NIP</th>
            <th>Nama</th>
            <?php foreach ($hari as $h): ?>
                <th><?= $h ?><br><small><?= date('d/m', strtotime($dateMap[$h])) ?></small></th>
            <?php endforeach; ?>
        </tr>
        <?php mysqli_data_seek($karyawanAktif, 0); while ($k = mysqli_fetch_assoc($karyawanAktif)): ?>
            <tr>
                <td><?= $k['nip'] ?></td>
                <td><?= $k['nama'] ?></td>
                <?php foreach ($dateMap as $tgl): ?>
                    <?php
                    $izin = mysqli_query($conn, "SELECT jenis FROM data_perizinan WHERE id_karyawan='{$k['id_karyawan']}' AND status='disetujui' AND '$tgl' BETWEEN tanggal_mulai AND tanggal_selesai");
                    if (mysqli_num_rows($izin)) {
                        $i = mysqli_fetch_assoc($izin);
                        echo "<td class='izin'>IZIN (" . strtoupper($i['jenis']) . ")</td>";
                    } else {
                        $jadwal = mysqli_query($conn, "SELECT s.nama_shift FROM data_jadwal_kerja j JOIN data_shift s ON s.id_shift = j.shift WHERE j.id_karyawan='{$k['id_karyawan']}' AND j.tanggal='$tgl'");
                        $j = mysqli_fetch_assoc($jadwal);
                        echo "<td>" . ($j['nama_shift'] ?? '-') . "</td>";
                    }
                    ?>
                <?php endforeach; ?>
            </tr>
        <?php endwhile; ?>
    </table>

    <div class="back">
        <a href="dashboard.php">&larr; Kembali ke Dashboard</a>
    </div>
</div>
</body>
</html>
