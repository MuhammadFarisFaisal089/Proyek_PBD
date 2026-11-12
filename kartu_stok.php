<?php
require_once 'auth.php';
require_roles(array(3));
require_once 'koneksi.php';

$query = $koneksi->query('SELECT k.idkartu_stok, k.created_at, k.jenis_transaksi, k.idtransaksi, k.masuk, k.keluar, k.stock, b.idbarang, b.nama FROM kartu_stok k JOIN barang b ON b.idbarang = k.idbarang ORDER BY k.idkartu_stok DESC');
?>
<!DOCTYPE html>
<html>
<head><title>Kartu Stok</title></head>
<body>
    <h2>Kartu Stok Gudang</h2>
    <p><a href="dashboard.php">Kembali ke Dashboard</a></p>
    <table border="1" cellpadding="8" cellspacing="0">
        <tr>
            <th>ID Kartu</th><th>Waktu</th><th>Barang</th><th>Jenis Transaksi</th>
            <th>ID Transaksi</th><th>Masuk</th><th>Keluar</th><th>Stok Akhir</th>
        </tr>
        <?php while ($data = $query->fetch_assoc()) { ?>
        <tr>
            <td><?php echo (int) $data['idkartu_stok']; ?></td>
            <td><?php echo h($data['created_at']); ?></td>
            <td><?php echo h($data['nama']); ?> (#<?php echo (int) $data['idbarang']; ?>)</td>
            <td><?php echo h($data['jenis_transaksi']); ?></td>
            <td><?php echo (int) $data['idtransaksi']; ?></td>
            <td style="color:green;">+<?php echo (int) $data['masuk']; ?></td>
            <td style="color:red;">-<?php echo (int) $data['keluar']; ?></td>
            <td><strong><?php echo (int) $data['stock']; ?></strong></td>
        </tr>
        <?php } ?>
    </table>
</body>
</html>
