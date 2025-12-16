<?php
include "koneksi.php";
session_start();

// Validasi role admin/atasan
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'atasan'])) {
    echo "⛔ Akses ditolak!";
    exit;
}

// Ambil id atasan/admin untuk tanda persetujuan
$id_approver = $_SESSION['id_user'] ?? null;

if (isset($_GET['setujui'])) {
    $id_izin = $_GET['setujui'];
    mysqli_query($conn, "
        UPDATE data_perizinan 
        SET status='disetujui', tanggal_persetujuan=NOW() 
        WHERE id_izin='$id_izin'
    ");
}

if (isset($_GET['tolak'])) {
    $id_izin = $_GET['tolak'];
    mysqli_query($conn, "
        UPDATE data_perizinan 
        SET status='ditolak', tanggal_persetujuan=NOW() 
        WHERE id_izin='$id_izin'
    ");
}

// Ambil semua pengajuan izin + data karyawan
$q = mysqli_query($conn, "
    SELECT p.*, k.nip, k.nama 
    FROM data_perizinan p 
    JOIN data_karyawan k ON k.id_karyawan = p.id_karyawan 
    ORDER BY p.tanggal_pengajuan DESC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Verifikasi Izin</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f5f8fa; padding: 20px; }
        h2 { text-align: center; color: #00796b; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; background: #fff; }
        th, td { padding: 10px; border: 1px solid #ccc; text-align: center; font-size: 14px; }
        th { background: #00796b; color: white; }
        .button { padding: 6px 12px; border-radius: 6px; color: white; text-decoration: none; }
        .yes { background: #2e7d32; }
        .no { background: #c62828; }
        .back { text-align: center; margin-top: 30px; }
        .back a { text-decoration: none; color: #00796b; font-weight: bold; }
        .back a:hover { text-decoration: underline; }
    </style>
</head>
<body>

<h2>✅ Verifikasi Pengajuan Izin</h2>

<table>
    <tr>
        <th>NIP</th>
        <th>Nama</th>
        <th>Jenis</th>
        <th>Alasan</th>
        <th>Periode</th>
        <th>Status</th>
        <th>Aksi</th>
    </tr>
    <?php while ($row = mysqli_fetch_assoc($q)): ?>
    <tr>
        <td><?= $row['nip'] ?></td>
        <td><?= $row['nama'] ?></td>
        <td><?= ucfirst($row['jenis']) ?></td>
        <td><?= $row['alasan'] ?></td>
        <td><?= $row['tanggal_mulai'] ?> s/d <?= $row['tanggal_selesai'] ?></td>
        <td><?= ucfirst($row['status']) ?></td>
        <td>
            <?php if ($row['status'] === 'menunggu'): ?>
                <a href="?setujui=<?= $row['id_izin'] ?>" class="button yes">Setujui</a>
                <a href="?tolak=<?= $row['id_izin'] ?>" class="button no">Tolak</a>
            <?php else: ?>
                -
            <?php endif; ?>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

<div class="back">
    <a href="dashboard.php">← Kembali ke Dashboard</a>
</div>

</body>
</html>
