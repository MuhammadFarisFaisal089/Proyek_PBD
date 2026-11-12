<?php
require_once 'auth.php';
require_login();

$role = (int) $_SESSION['idrole'];
$nama = h($_SESSION['username']);
$roleNames = array(1 => 'Administrator', 2 => 'Kasir', 3 => 'Staf Gudang');
?>
<!DOCTYPE html>
<html>
<head><title>Dashboard Sistem</title></head>
<body>
    <h2>Selamat Datang, <?php echo strtoupper($nama); ?>!</h2>
    <p>Anda login sebagai: <strong><?php echo h(isset($roleNames[$role]) ? $roleNames[$role] : 'Tidak diketahui'); ?></strong></p>
    <hr>

    <h3>Menu Navigasi</h3>
    <ul>
        <li><a href="katalog.php">Lihat Katalog Barang</a></li>
        <?php if ($role === 1) { ?>
            <li><a href="tambah_barang.php">Kelola Master Barang</a></li>
            <li><a href="pengadaan.php">Buat Pengadaan</a></li>
            <li><a href="#">Laporan Keuangan (Segera hadir)</a></li>
        <?php } ?>
        <?php if ($role === 2) { ?>
            <li><a href="penjualan.php">Transaksi Kasir</a></li>
        <?php } ?>  
        <?php if ($role === 3) { ?>
            <li><a href="pengadaan.php">Buat Pengadaan</a></li>
            <li><a href="terima_barang.php">Penerimaan Barang (Pilih PO)</a></li>
            <li><a href="kartu_stok.php">Cek Kartu Stok</a></li>
        <?php } ?>
        <li><a href="logout.php" style="color: red;">Keluar (Logout)</a></li>
    </ul>
</body>
</html>
      