<?php
require_once 'auth.php';
require_roles(array(1, 3));
require_once 'koneksi.php';

$purchaseId = filter_var(isset($_GET['id']) ? $_GET['id'] : null, FILTER_VALIDATE_INT);
if ($purchaseId === false || $purchaseId < 1) {
    http_response_code(400);
    exit('ID pengadaan tidak valid.');
}

$header = $koneksi->prepare('SELECT p.idpengadaan, p.status, p.subtotal_nilai, p.ppn, p.total_nilai, v.nama_vendor FROM pengadaan p JOIN vendor v ON v.idvendor = p.vendor_idvendor WHERE p.idpengadaan = ?');
$header->bind_param('i', $purchaseId);
$header->execute();
$purchase = $header->get_result()->fetch_assoc();
$header->close();
if (!$purchase) {
    http_response_code(404);
    exit('Pengadaan tidak ditemukan.');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $itemId = filter_var(isset($_POST['idbarang']) ? $_POST['idbarang'] : null, FILTER_VALIDATE_INT);
    $unitPrice = filter_var(isset($_POST['harga_satuan']) ? $_POST['harga_satuan'] : null, FILTER_VALIDATE_INT);
    $quantity = filter_var(isset($_POST['jumlah']) ? $_POST['jumlah'] : null, FILTER_VALIDATE_INT);

    if ($purchase['status'] !== 'P') {
        $error = 'Pengadaan tidak lagi berstatus terbuka.';
    } elseif ($itemId === false || $itemId < 1 || $unitPrice === false || $unitPrice < 1 || $quantity === false || $quantity < 1 || $unitPrice > floor(2147483647 / $quantity)) {
        $error = 'Barang, harga, atau jumlah tidak valid.';
    } else {
        try {
            $koneksi->begin_transaction();

            $lock = $koneksi->prepare('SELECT status FROM pengadaan WHERE idpengadaan = ? FOR UPDATE');
            $lock->bind_param('i', $purchaseId);
            $lock->execute();
            $lockedPurchase = $lock->get_result()->fetch_assoc();
            $lock->close();
            if (!$lockedPurchase || $lockedPurchase['status'] !== 'P') {
                throw new RuntimeException('Pengadaan tidak terbuka.');
            }

            $item = $koneksi->prepare('SELECT idbarang FROM barang WHERE idbarang = ? AND status = 1 FOR UPDATE');
            $item->bind_param('i', $itemId);
            $item->execute();
            $itemExists = $item->get_result()->fetch_assoc();
            $item->close();
            if (!$itemExists) {
                throw new RuntimeException('Barang tidak ditemukan atau nonaktif.');
            }

            $duplicate = $koneksi->prepare('SELECT 1 FROM detail_pengadaan WHERE idpengadaan = ? AND idbarang = ? LIMIT 1');
            $duplicate->bind_param('ii', $purchaseId, $itemId);
            $duplicate->execute();
            $hasDuplicate = (bool) $duplicate->get_result()->fetch_row();
            $duplicate->close();
            if ($hasDuplicate) {
                throw new RuntimeException('Barang sudah ada di PO ini; buat PO baru untuk menambah baris lain.');
            }

            $subtotal = $unitPrice * $quantity;
            $insert = $koneksi->prepare('INSERT INTO detail_pengadaan (harga_satuan, jumlah, sub_total, idbarang, idpengadaan) VALUES (?, ?, ?, ?, ?)');
            $insert->bind_param('iiiii', $unitPrice, $quantity, $subtotal, $itemId, $purchaseId);
            $insert->execute();
            $insert->close();

            $totals = $koneksi->prepare('UPDATE pengadaan SET subtotal_nilai = (SELECT COALESCE(SUM(sub_total), 0) FROM detail_pengadaan WHERE idpengadaan = ?), ppn = 0, total_nilai = (SELECT COALESCE(SUM(sub_total), 0) FROM detail_pengadaan WHERE idpengadaan = ?) WHERE idpengadaan = ?');
            $totals->bind_param('iii', $purchaseId, $purchaseId, $purchaseId);
            $totals->execute();
            $totals->close();
            $koneksi->commit();

            header('Location: detail_pengadaan.php?id=' . $purchaseId . '&added=1');
            exit;
        } catch (RuntimeException $exception) {
            $koneksi->rollback();
            $error = $exception->getMessage();
        } catch (mysqli_sql_exception $exception) {
            $koneksi->rollback();
            error_log($exception->getMessage());
            $error = 'Detail pengadaan gagal disimpan.';
        }
    }
}

$lines = $koneksi->prepare('SELECT dp.idbarang, b.nama, s.nama_satuan, dp.harga_satuan, dp.jumlah, dp.sub_total FROM detail_pengadaan dp JOIN barang b ON b.idbarang = dp.idbarang JOIN satuan s ON s.idsatuan = b.idsatuan WHERE dp.idpengadaan = ? ORDER BY dp.iddetail_pengadaan');
$lines->bind_param('i', $purchaseId);
$lines->execute();
$items = $lines->get_result()->fetch_all(MYSQLI_ASSOC);
$lines->close();

$availableItems = $koneksi->query('SELECT idbarang, nama FROM barang WHERE status = 1 ORDER BY nama')->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html>
<head><title>Detail Pengadaan</title></head>
<body>
    <h2>Detail PO #<?php echo (int) $purchaseId; ?></h2>
    <p>Vendor: <?php echo h($purchase['nama_vendor']); ?> | Status: <?php echo h($purchase['status']); ?></p>
    <p><a href="pengadaan.php">Buat PO lain</a> | <a href="dashboard.php">Dashboard</a></p>
    <?php if ($error !== '') { ?><p style="color:red;"><?php echo h($error); ?></p><?php } ?>
    <?php if (isset($_GET['added'])) { ?><p style="color:green;">Item berhasil ditambahkan.</p><?php } ?>

    <?php if ($purchase['status'] === 'P') { ?>
    <h3>Tambah Item PO</h3>
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo h(csrf_token()); ?>">
        <label>Barang:</label>
        <select name="idbarang" required>
            <option value="">-- Pilih barang --</option>
            <?php foreach ($availableItems as $item) { ?>
                <option value="<?php echo (int) $item['idbarang']; ?>"><?php echo h($item['nama']); ?></option>
            <?php } ?>
        </select>
        <label>Harga beli:</label>
        <input type="number" name="harga_satuan" min="1" step="1" required>
        <label>Jumlah:</label>
        <input type="number" name="jumlah" min="1" step="1" required>
        <button type="submit">Tambah Item</button>
    </form>
    <?php } ?>

    <h3>Item Pengadaan</h3>
    <table border="1" cellpadding="6" cellspacing="0">
        <tr><th>Barang</th><th>Jumlah</th><th>Harga Satuan</th><th>Subtotal</th></tr>
        <?php foreach ($items as $item) { ?>
        <tr>
            <td><?php echo h($item['nama']); ?> (<?php echo h($item['nama_satuan']); ?>)</td>
            <td><?php echo (int) $item['jumlah']; ?></td>
            <td>Rp <?php echo number_format((int) $item['harga_satuan'], 0, ',', '.'); ?></td>
            <td>Rp <?php echo number_format((int) $item['sub_total'], 0, ',', '.'); ?></td>
        </tr>
        <?php } ?>
    </table>
    <p>Subtotal: Rp <?php echo number_format((int) $purchase['subtotal_nilai'], 0, ',', '.'); ?> |
       PPN: Rp <?php echo number_format((int) $purchase['ppn'], 0, ',', '.'); ?> |
       Total: Rp <?php echo number_format((int) $purchase['total_nilai'], 0, ',', '.'); ?></p>
    <?php if (count($items) > 0) { ?>
        <a href="terima_barang.php?idpo=<?php echo (int) $purchaseId; ?>">Lanjut ke Penerimaan</a>
    <?php } ?>
</body>
</html>
