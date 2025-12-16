<?php
include "koneksi.php";
session_start();

$pesan = "";
$login_sukses = false; // <-- Tambahkan variabel penanda ini

if (isset($_POST['login'])) {
    $input = trim($_POST['user']);
    $password = trim($_POST['password']);

    // Cek sebagai karyawan
    $qKaryawan = mysqli_query($conn, "SELECT * FROM data_karyawan WHERE nip='$input' AND password='$password' AND status='aktif'");
    $dataKaryawan = mysqli_fetch_assoc($qKaryawan);

    if ($dataKaryawan) {
        $_SESSION['id_karyawan'] = $dataKaryawan['id_karyawan'];
        $_SESSION['nip'] = $dataKaryawan['nip'];
        $_SESSION['nama'] = $dataKaryawan['nama'];
        $_SESSION['role'] = $dataKaryawan['role'];
        
        $login_sukses = true; // <-- Atur penanda jadi true, jangan redirect dulu
    }

    // Cek sebagai admin/atasan
    if (!$login_sukses) { // Hanya cek jika login karyawan gagal
        $qUser = mysqli_query($conn, "SELECT * FROM data_user WHERE nama='$input' AND password='$password'");
        $dataUser = mysqli_fetch_assoc($qUser);

        if ($dataUser) {
            $_SESSION['id_user'] = $dataUser['id_user'];
            $_SESSION['nama'] = $dataUser['nama'];
            $_SESSION['role'] = $dataUser['role'];
            
            $login_sukses = true; // <-- Atur penanda jadi true, jangan redirect dulu
        }
    }

    // Gagal login
    if (!$login_sukses) {
        $pesan = "❌ NIP atau Nama atau Password salah!";
    }
}
?>



<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login SiKawan</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background: #e0f7fa;
            font-family: 'Segoe UI', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .login-box {
            background: #ffffff;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }

        .login-box h2 {
            margin-bottom: 20px;
            text-align: center;
            color: #00796b;
        }

        .login-box input[type="text"],
        .login-box input[type="password"] {
            width: 100%;
            padding: 12px;
            margin: 8px 0 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }

        .login-box button {
            background-color: #00796b;
            color: white;
            border: none;
            padding: 12px;
            width: 100%;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
        }

        .login-box button:hover {
            background-color: #004d40;
        }

        .error {
            color: red;
            text-align: center;
            margin-bottom: 10px;
        }

        .footer {
            margin-top: 20px;
            font-size: 12px;
            color: #aaa;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="login-box">
    <h2>Login SiKawan</h2>

    <?php if ($pesan): ?>
        <div class="error"><?= $pesan ?></div>
    <?php endif; ?>

    <form method="post">
        <label for="user">NIP (untuk Karyawan) / Nama (untuk Admin/Atasan)</label>
        <input type="text" name="user" id="user" required>

        <label for="password">Password</label>
        <input type="password" name="password" id="password" required>

        <button type="submit" name="login">Masuk</button>
    </form>

    <div class="footer">
        &copy; <?= date("Y") ?> SiKawan • Sistem Kehadiran Karyawan
    </div>
</div>
<?php if ($login_sukses): ?>
<script>
    Swal.fire({
        title: 'Login Sukses!',
        text: 'Anda akan diarahkan ke dashboard...',
        icon: 'success',
        width: '380px',
        timer: 1000, // Pop-up akan hilang setelah 1 detik
        showConfirmButton: false, // Tombol OK disembunyikan
        allowOutsideClick: false // Tidak bisa di-klik di luar pop-up
    }).then(() => {
        // Arahkan ke dashboard setelah timer selesai
        window.location.href = "dashboard.php";
    });
</script>
<?php endif; ?>
</body>
</html>
