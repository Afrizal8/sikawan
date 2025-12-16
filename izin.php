<?php
include "koneksi.php";
session_start();
date_default_timezone_set('Asia/Jakarta');

// Validasi hanya karyawan
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'karyawan') {
    header("Location: index.php");
    exit;
}

$id_karyawan = $_SESSION['id_karyawan'];
$pesan = "";
$sukses = false; // Penanda untuk pop-up

// Proses pengajuan izin
if (isset($_POST['ajukan'])) {
    $jenis   = mysqli_real_escape_string($conn, $_POST['jenis']);
    $alasan  = mysqli_real_escape_string($conn, $_POST['alasan']);
    $mulai   = $_POST['mulai'];
    $selesai = $_POST['selesai'];

    if (strtotime($mulai) > strtotime($selesai)) {
        $pesan = "❌ Tanggal selesai tidak boleh lebih awal dari tanggal mulai.";
    } else {
        // Cek bentrok izin sebelumnya
        $cek = mysqli_query($conn, "SELECT * FROM data_perizinan 
            WHERE id_karyawan = '$id_karyawan'
            AND (
                (tanggal_mulai BETWEEN '$mulai' AND '$selesai') OR 
                (tanggal_selesai BETWEEN '$mulai' AND '$selesai') OR 
                ('$mulai' BETWEEN tanggal_mulai AND tanggal_selesai)
            )
            AND status IN ('menunggu', 'disetujui')");

        if (mysqli_num_rows($cek) > 0) {
            $pesan = "⚠️ Anda sudah memiliki izin dalam rentang tanggal tersebut.";
        } else {
            $insert = mysqli_query($conn, "INSERT INTO data_perizinan 
                (id_karyawan, jenis, alasan, tanggal_pengajuan, tanggal_mulai, tanggal_selesai, status) 
                VALUES 
                ('$id_karyawan','$jenis','$alasan',NOW(),'$mulai','$selesai','menunggu')");
            
            if ($insert) {
                $sukses = true; // Set penanda sukses menjadi true
            } else {
                $pesan = "❌ Gagal mengajukan izin.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pengajuan Izin - SiKawan</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* ... (CSS Anda tetap sama, tidak perlu diubah) ... */
        body { background-color: #f5f8fa; font-family: 'Segoe UI', sans-serif; margin: 0; padding: 0; }
        .container { max-width: 500px; margin: 60px auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #00796b; margin-bottom: 20px; }
        label { display: block; margin-top: 12px; color: #333; }
        select, input[type="text"], input[type="date"] { width: 100%; padding: 10px; margin-top: 6px; border: 1px solid #ccc; border-radius: 8px; }
        button { margin-top: 20px; width: 100%; padding: 12px; background-color: #00796b; color: white; border: none; border-radius: 8px; font-size: 16px; cursor: pointer; }
        button:hover { background-color: #004d40; }
        .message { text-align: center; margin-top: 15px; color: #c62828; font-weight: 600; background: #ffebee; padding: 12px; border-left: 4px solid #e53935; border-radius: 8px; }
        .back { text-align: center; margin-top: 25px; }
        .back a { color: #00796b; text-decoration: none; }
        .back a:hover { text-decoration: underline; }
    </style>
</head>
<body>

<div class="container">
    <h2>Form Pengajuan Izin</h2>

    <?php if ($pesan): ?>
        <div class="message"><?= $pesan ?></div>
    <?php endif; ?>

    <form method="post">
        <label for="jenis">Jenis Izin</label>
        <select name="jenis" id="jenis" required>
            <option value="">-- Pilih Jenis Izin --</option>
            <option value="Cuti">Cuti</option>
            <option value="Sakit">Sakit</option>
            <option value="Dinas">Dinas</option>
        </select>

        <label for="alasan">Alasan</label>
        <input type="text" name="alasan" id="alasan" placeholder="Contoh: Urusan keluarga" required>

        <label for="mulai">Tanggal Mulai</label>
        <input type="date" name="mulai" id="mulai" required>

        <label for="selesai">Tanggal Selesai</label>
        <input type="date" name="selesai" id="selesai" required>

        <button type="submit" name="ajukan">Ajukan Izin</button>
    </form>

    <div class="back">
        <a href="dashboard.php">← Kembali ke Dashboard</a>
    </div>
</div>

<?php if ($sukses): ?>
<script>
    Swal.fire({
        title: 'Berhasil!',
        text: 'Izin berhasil diajukan.',
        icon: 'success',
        timer: 2000, // Durasi 2 detik
        showConfirmButton: false
    }).then(() => {
        // Baris ini akan dijalankan setelah timer pop-up selesai
        window.location.href = "dashboard.php";
    });
</script>
<?php endif; ?>

</body>
</html>