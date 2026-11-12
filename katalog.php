<?php
require_once 'auth.php';
require_login();
require_once 'koneksi.php';

$result = $koneksi->query('SELECT idbarang, nama, nama_satuan, harga FROM view_katalog_barang WHERE status = 1 ORDER BY nama');
$canManage = (int) $_SESSION['idrole'] === 1;
?>
<!DOCTYPE html>
<html>
<head><title>Katalog Barang</title></head>
<body>
    <h2>Data Barang</h2>
    <a href="dashboard.php">Kembali ke Dashboard</a>
    <?php if ($canManage) { ?> | <a href="tambah_barang.php">Tambah Barang</a><?php } ?>
    <table border="1" cellpadding="6" cellspacing="0">
        <tr>
            <th>ID</th>
            <th>Nama Barang</th>
            <th>Satuan</th>
            <th>Harga</th>
            <?php if ($canManage) { ?><th>Aksi</th><?php } ?>
        </tr>
        <?php while ($row = $result->fetch_assoc()) { ?>
        <tr>
            <td><?php echo (int) $row['idbarang']; ?></td>
            <td><?php echo h($row['nama']); ?></td>
            <td><?php echo h($row['nama_satuan']); ?></td>
            <td>Rp <?php echo number_format((int) $row['harga'], 0, ',', '.'); ?></td>
            <?php if ($canManage) { ?>
            <td>
                <a href="edit_barang.php?id=<?php echo (int) $row['idbarang']; ?>">Edit</a>
                <form method="POST" action="hapus_barang.php" style="display:inline" onsubmit="return confirm('Hapus barang ini?')">
                    <input type="hidden" name="csrf_token" value="<?php echo h(csrf_token()); ?>">
                    <input type="hidden" name="id" value="<?php echo (int) $row['idbarang']; ?>">
                    <button type="submit">Hapus</button>
                </form>
            </td>
            <?php } ?>
        </tr>
        <?php } ?>
    </table>
</body>
</html>
