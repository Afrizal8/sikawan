<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "sikawan";

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Optional: fungsi bantu ambil ID dari nama
if (!function_exists('getUserIdByNama')) {
    function getUserIdByNama($conn, $nama) {
        // Cek dari data_karyawan dulu
        $q = mysqli_query($conn, "SELECT id_karyawan AS id FROM data_karyawan WHERE nama='$nama'");
        $d = mysqli_fetch_assoc($q);
        if ($d) return $d['id'];

        // Kalau tidak ketemu, cek dari data_user
        $q2 = mysqli_query($conn, "SELECT id_user AS id FROM data_user WHERE nama='$nama'");
        $d2 = mysqli_fetch_assoc($q2);
        return $d2 ? $d2['id'] : null;
    }
}
?>
