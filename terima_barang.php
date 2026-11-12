<?php
require_once 'auth.php';
require_roles(array(3));
require_once 'koneksi.php';

$rawPurchaseId = isset($_GET['idpo']) ? $_GET['idpo'] : (isset($_POST['idpo']) ? $_POST['idpo'] : null);
$purchaseId = filter_var($rawPurchaseId, FILTER_VALIDATE_INT);

if ($rawPurchaseId === null && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $pendingPurchases = $koneksi->query("
        SELECT p.idpengadaan, p.`timestamp`, p.total_nilai, v.nama_vendor,
               COUNT(dp.iddetail_pengadaan) AS jumlah_item,
               COALESCE(SUM(dp.jumlah), 0) AS total_unit
        FROM pengadaan p
        JOIN vendor v ON v.idvendor = p.vendor_idvendor
        JOIN detail_pengadaan dp ON dp.idpengadaan = p.idpengadaan
        WHERE p.status = 'P'
        GROUP BY p.idpengadaan, p.`timestamp`, p.total_nilai, v.nama_vendor
        ORDER BY p.idpengadaan DESC
    ");
    ?>
    <!DOCTYPE html>
    <html>
    <head><title>Pilih PO Penerimaan</title></head>
    <body>
        <h2>PO Menunggu Penerimaan</h2>
        <p><a href="dashboard.php">Kembali ke Dashboard</a></p>
        <?php if ($pendingPurchases->num_rows === 0) { ?>
            <p>Tidak ada PO terbuka yang memiliki item untuk diterima.</p>
            <p><a href="pengadaan.php">Buat Pengadaan</a></p>
        <?php } else { ?>
            <table border="1" cellpadding="6" cellspacing="0">
                <tr>
                    <th>ID PO</th><th>Tanggal</th><th>Vendor</th>
                    <th>Jumlah Item</th><th>Total Unit</th><th>Total Nilai</th><th>Aksi</th>
                </tr>
                <?php while ($row = $pendingPurchases->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo (int) $row['idpengadaan']; ?></td>
                        <td><?php echo h($row['timestamp']); ?></td>
                        <td><?php echo h($row['nama_vendor']); ?></td>
                        <td><?php echo (int) $row['jumlah_item']; ?></td>
                        <td><?php echo (int) $row['total_unit']; ?></td>
                        <td>Rp <?php echo number_format((int) $row['total_nilai'], 0, ',', '.'); ?></td>
                        <td><a href="terima_barang.php?idpo=<?php echo (int) $row['idpengadaan']; ?>">Pilih PO</a></td>
                    </tr>
                <?php } ?>
            </table>
        <?php } ?>
    </body>
    </html>
    <?php
    exit;
}

if ($purchaseId === false || $purchaseId < 1) {
    http_response_code(400);
    exit('ID pengadaan tidak valid.');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $submittedQuantities = isset($_POST['jumlah']) && is_array($_POST['jumlah']) ? $_POST['jumlah'] : array();

    try {
        $koneksi->begin_transaction();
        $lock = $koneksi->prepare('SELECT idpengadaan, status FROM pengadaan WHERE idpengadaan = ? FOR UPDATE');
        $lock->bind_param('i', $purchaseId);
        $lock->execute();
        $purchase = $lock->get_result()->fetch_assoc();
        $lock->close();

        if (!$purchase) {
            throw new RuntimeException('Pengadaan tidak ditemukan.');
        }
        if ($purchase['status'] !== 'P') {
            throw new RuntimeException('Pengadaan tidak berstatus terbuka.');
        }

        $lineQuery = $koneksi->prepare('SELECT dp.idbarang, dp.harga_satuan, dp.jumlah, b.nama, COALESCE((SELECT SUM(dpr.jumlah_terima) FROM detail_penerimaan dpr JOIN penerimaan pr ON pr.idpenerimaan = dpr.idpenerimaan WHERE pr.idpengadaan = dp.idpengadaan AND dpr.barang_idbarang = dp.idbarang), 0) AS sudah_diterima FROM detail_pengadaan dp JOIN barang b ON b.idbarang = dp.idbarang WHERE dp.idpengadaan = ? ORDER BY dp.iddetail_pengadaan');
        $lineQuery->bind_param('i', $purchaseId);
        $lineQuery->execute();
        $lines = $lineQuery->get_result()->fetch_all(MYSQLI_ASSOC);
        $lineQuery->close();
        if (count($lines) === 0) {
            throw new RuntimeException('PO belum memiliki item.');
        }

        $toReceive = array();
        foreach ($lines as $line) {
            $itemId = (int) $line['idbarang'];
            $rawQuantity = isset($submittedQuantities[$itemId]) ? $submittedQuantities[$itemId] : '0';
            $quantity = filter_var($rawQuantity, FILTER_VALIDATE_INT);
            $remaining = (int) $line['jumlah'] - (int) $line['sudah_diterima'];
            if ($quantity === false || $quantity < 0 || $quantity > $remaining) {
                throw new RuntimeException('Jumlah penerimaan untuk ' . $line['nama'] . ' tidak valid. Sisa pesanan: ' . max(0, $remaining) . '.');
            }
            if ($quantity > 0) {
                $toReceive[] = array(
                    'idbarang' => $itemId,
                    'jumlah' => $quantity,
                    'harga' => (int) $line['harga_satuan']
                );
            }
        }
        if (count($toReceive) === 0) {
            throw new RuntimeException('Masukkan jumlah barang yang diterima.');
        }

        $status = 'P';
        $userId = (int) $_SESSION['iduser'];
        $createReceipt = $koneksi->prepare('INSERT INTO penerimaan (status, idpengadaan, iduser) VALUES (?, ?, ?)');
        $createReceipt->bind_param('sii', $status, $purchaseId, $userId);
        $createReceipt->execute();
        $receiptId = (int) $koneksi->insert_id;
        $createReceipt->close();

        foreach ($toReceive as $receivedItem) {
            $procedureParams = array($receiptId, $receivedItem['idbarang'], $receivedItem['jumlah'], $receivedItem['harga']);
            execute_procedure($koneksi, 'CALL proses_penerimaan(?, ?, ?, ?)', 'iiii', $procedureParams);
        }

        $koneksi->commit();
        $_SESSION['flash'] = 'Penerimaan berhasil diproses; prosedur mencatat detail penerimaan dan mutasi stok masuk.';
        header('Location: terima_barang.php?idpo=' . $purchaseId);
        exit;
    } catch (RuntimeException $exception) {
        $koneksi->rollback();
        $error = $exception->getMessage();
    } catch (mysqli_sql_exception $exception) {
        $koneksi->rollback();
        error_log($exception->getMessage());
        $error = 'Penerimaan gagal diproses. Pastikan prosedur proses_penerimaan sudah terpasang dan data PO masih valid.';
    }
}

$header = $koneksi->prepare('SELECT p.idpengadaan, p.status, v.nama_vendor FROM pengadaan p JOIN vendor v ON v.idvendor = p.vendor_idvendor WHERE p.idpengadaan = ?');
$header->bind_param('i', $purchaseId);
$header->execute();
$purchase = $header->get_result()->fetch_assoc();
$header->close();
if (!$purchase) {
    http_response_code(404);
    exit('Pengadaan tidak ditemukan.');
}

$lineQuery = $koneksi->prepare('SELECT dp.idbarang, dp.harga_satuan, dp.jumlah, b.nama, COALESCE((SELECT SUM(dpr.jumlah_terima) FROM detail_penerimaan dpr JOIN penerimaan pr ON pr.idpenerimaan = dpr.idpenerimaan WHERE pr.idpengadaan = dp.idpengadaan AND dpr.barang_idbarang = dp.idbarang), 0) AS sudah_diterima FROM detail_pengadaan dp JOIN barang b ON b.idbarang = dp.idbarang WHERE dp.idpengadaan = ? ORDER BY dp.iddetail_pengadaan');
$lineQuery->bind_param('i', $purchaseId);
$lineQuery->execute();
$lines = $lineQuery->get_result()->fetch_all(MYSQLI_ASSOC);
$lineQuery->close();

$success = isset($_SESSION['flash']) ? $_SESSION['flash'] : '';
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html>
<head><title>Penerimaan Barang</title></head>
<body>
    <h2>Penerimaan PO #<?php echo (int) $purchaseId; ?></h2>
    <p>Vendor: <?php echo h($purchase['nama_vendor']); ?> | Status PO: <?php echo h($purchase['status']); ?></p>
    <p><a href="dashboard.php">Kembali ke Dashboard</a> | <a href="detail_pengadaan.php?id=<?php echo (int) $purchaseId; ?>">Detail PO</a></p>
    <?php if ($error !== '') { ?><p style="color:red;"><?php echo h($error); ?></p><?php } ?>
    <?php if ($success !== '') { ?><p style="color:green;"><?php echo h($success); ?></p><?php } ?>

    <table border="1" cellpadding="6" cellspacing="0">
        <tr><th>Barang</th><th>Pesan</th><th>Sudah Diterima</th><th>Sisa</th><th>Harga Beli</th></tr>
        <?php foreach ($lines as $line) {
            $remaining = max(0, (int) $line['jumlah'] - (int) $line['sudah_diterima']);
        ?>
        <tr>
            <td><?php echo h($line['nama']); ?></td>
            <td><?php echo (int) $line['jumlah']; ?></td>
            <td><?php echo (int) $line['sudah_diterima']; ?></td>
            <td><?php echo $remaining; ?></td>
            <td>Rp <?php echo number_format((int) $line['harga_satuan'], 0, ',', '.'); ?></td>
        </tr>
        <?php } ?>
    </table>

    <?php if ($purchase['status'] === 'P' && count($lines) > 0) { ?>
    <h3>Catat Barang yang Datang</h3>
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo h(csrf_token()); ?>">
        <input type="hidden" name="idpo" value="<?php echo (int) $purchaseId; ?>">
        <?php foreach ($lines as $line) {
            $remaining = max(0, (int) $line['jumlah'] - (int) $line['sudah_diterima']);
        ?>
        <label><?php echo h($line['nama']); ?> (sisa <?php echo $remaining; ?>):</label>
        <input type="number" name="jumlah[<?php echo (int) $line['idbarang']; ?>]" min="0" max="<?php echo $remaining; ?>" value="0" required><br>
        <?php } ?>
        <button type="submit">Proses Penerimaan</button>
    </form>
    <?php } ?>

    <p><a href="kartu_stok.php">Lihat Kartu Stok</a></p>
</body>
</html>
