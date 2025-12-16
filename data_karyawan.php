<?php
session_start();
include "koneksi.php";

// Hanya admin dan atasan yang bisa mengakses
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'atasan'])) {
    echo "⛔ Akses ditolak!";
    exit;
}

$pesan = "";

// Tambah karyawan
if (isset($_POST['tambah'])) {
    $nip = $_POST['nip'];
    $nama = $_POST['nama'];
    $jabatan = $_POST['jabatan'];
    $password = $_POST['password'];
    $status = $_POST['status'];

    // Cek jika NIP sudah digunakan
    $cek = mysqli_query($conn, "SELECT * FROM data_karyawan WHERE nip='$nip'");
    if (mysqli_num_rows($cek) > 0) {
        $pesan = "❌ NIP sudah digunakan!";
    } else {
        $insert = mysqli_query($conn, "
            INSERT INTO data_karyawan 
            (nip, nama, jabatan, password, role, status, tanggal_masuk) 
            VALUES ('$nip', '$nama', '$jabatan', '$password', 'karyawan', '$status', NOW())
        ");
        $pesan = $insert ? "✅ Karyawan berhasil ditambahkan." : "❌ Gagal menambah karyawan.";
    }
}

// Ubah status aktif/nonaktif
if (isset($_GET['ubah_status'])) {
    $nip = $_GET['ubah_status'];
    $q = mysqli_query($conn, "SELECT status FROM data_karyawan WHERE nip='$nip'");
    $d = mysqli_fetch_assoc($q);
    $statusBaru = ($d['status'] === 'aktif') ? 'nonaktif' : 'aktif';
    mysqli_query($conn, "UPDATE data_karyawan SET status='$statusBaru' WHERE nip='$nip'");
    header("Location: data_karyawan.php");
    exit;
}

// Hapus karyawan
if (isset($_GET['hapus'])) {
    $nip = $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM data_karyawan WHERE nip='$nip'");
    header("Location: data_karyawan.php");
    exit;
}

// Ambil data semua karyawan
$q = mysqli_query($conn, "SELECT nip, nama, jabatan, role, status FROM data_karyawan ORDER BY nip ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Karyawan - SiKawan</title>
    <style>
        body { background-color: #f1f8e9; font-family: 'Segoe UI', sans-serif; margin: 0; padding: 0; }
        .container { max-width: 1000px; margin: 50px auto; background: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #33691e; margin-bottom: 30px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 14px; }
        th, td { border: 1px solid #ccc; padding: 12px; text-align: center; }
        th { background: #558b2f; color: white; }
        tr:nth-child(even) { background: #f9fbe7; }
        tr:hover { background: #f1f8e9; }
        .form-section { margin-top: 40px; }
        label { font-weight: 600; display: block; margin-top: 12px; }
        input, select { width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ccc; margin-top: 5px; }
        button { background: #558b2f; color: white; padding: 12px 20px; margin-top: 20px; border: none; border-radius: 8px; cursor: pointer; }
        button:hover { background: #33691e; }
        .aksi a { margin: 0 5px; text-decoration: none; color: #2e7d32; }
        .aksi a:hover { text-decoration: underline; }
        .message { text-align: center; color: #33691e; font-weight: bold; margin-top: 15px; }
        .back { margin-top: 30px; text-align: center; }
        .back a { text-decoration: none; color: #558b2f; font-weight: bold; }
    </style>
</head>
<body>
<div class="container">
    <h2>👥 Data Karyawan</h2>

    <?php if ($pesan): ?>
        <div class="message"><?= $pesan ?></div>
    <?php endif; ?>

    <table>
        <tr>
            <th>No</th>
            <th>NIP</th>
            <th>Nama</th>
            <th>Jabatan</th>
            <th>Role</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
        <?php $no = 1; while ($d = mysqli_fetch_assoc($q)): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= $d['nip'] ?></td>
                <td><?= $d['nama'] ?></td>
                <td><?= $d['jabatan'] ?></td>
                <td><?= $d['role'] ?></td>
                <td><?= ucfirst($d['status']) ?></td>
                <td class="aksi">
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <a href="?ubah_status=<?= $d['nip'] ?>" onclick="return confirm('Ubah status karyawan ini?')">Ubah Status</a> |
                        <a href="?hapus=<?= $d['nip'] ?>" onclick="return confirm('Yakin ingin menghapus?')">Hapus</a>
                    <?php else: ?>
                        Tidak tersedia
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>

    <?php if ($_SESSION['role'] === 'admin'): ?>
    <div class="form-section">
        <h3>➕ Tambah Karyawan</h3>
        <form method="post">
            <label for="nip">NIP</label>
            <input type="text" name="nip" id="nip" required>

            <label for="nama">Nama</label>
            <input type="text" name="nama" id="nama" required>

            <label for="jabatan">Jabatan</label>
            <input type="text" name="jabatan" id="jabatan" required>

            <label for="password">Password</label>
            <input type="text" name="password" id="password" required>

            <label for="status">Status</label>
            <select name="status" id="status" required>
                <option value="aktif">Aktif</option>
                <option value="nonaktif">Nonaktif</option>
            </select>

            <button type="submit" name="tambah">Tambah Karyawan</button>
        </form>
    </div>
    <?php endif; ?>

    <div class="back">
        <a href="dashboard.php">← Kembali ke Dashboard</a>
    </div>
</div>
</body>
</html>
