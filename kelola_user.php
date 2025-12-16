<?php

include "koneksi.php";
session_start();
if (!isset($_SESSION['nip']) || $_SESSION['role'] != 'admin') {
    echo "⛔ Akses ditolak!";
    exit;
}

$pesan = "";
if (isset($_POST['tambah'])) {
    $nip = $_POST['nip'];
    $nama = $_POST['nama'];
    $password = $_POST['password'];
    $role = $_POST['role'];
    $status = $_POST['status'];
    $masuk = $_POST['tanggal_masuk'];

    $insert = mysqli_query($conn, "INSERT INTO users (nip, nama, password, role, status, tanggal_masuk) VALUES ('$nip','$nama','$password','$role','$status','$masuk')");
    $pesan = $insert ? "✅ User berhasil ditambahkan." : "❌ Gagal menambahkan user.";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Akun User</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f5f8fa; padding: 20px; }
        .form-container { background: white; padding: 30px; border-radius: 12px; max-width: 600px; margin: auto; }
        label { display: block; margin-top: 10px; }
        input, select { width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ccc; border-radius: 8px; }
        button { margin-top: 20px; padding: 12px; background-color: #00796b; color: white; border: none; border-radius: 8px; font-size: 16px; cursor: pointer; }
        button:hover { background-color: #004d40; }
        .message { text-align: center; margin-top: 15px; color: #00796b; font-weight: bold; }
    </style>
</head>
<body>
<div class="form-container">
    <h2>Tambah Akun Karyawan</h2>
    <?php if ($pesan): ?>
        <div class="message"> <?= $pesan ?> </div>
    <?php endif; ?>
    <form method="post">
        <label>NIP</label>
        <input type="text" name="nip" required>

        <label>Nama Lengkap</label>
        <input type="text" name="nama" required>

        <label>Password</label>
        <input type="text" name="password" required>

        <label>Role</label>
        <select name="role" required>
            <option value="karyawan">Karyawan</option>
            <option value="atasan">Atasan</option>
            <option value="admin">Admin</option>
        </select>

        <label>Status</label>
        <select name="status" required>
            <option value="aktif">Aktif</option>
            <option value="cuti">Cuti</option>
            <option value="nonaktif">Nonaktif</option>
        </select>

        <label>Tanggal Masuk</label>
        <input type="date" name="tanggal_masuk" required>

        <button type="submit" name="tambah">Tambah User</button>
    </form>
</div>
</body>
</html>
