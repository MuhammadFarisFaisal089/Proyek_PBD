<?php
require_once 'auth.php';
require_roles(array(2));
require_once 'koneksi.php';

$error = '';
$saleSummary = isset($_SESSION['sale_summary']) ? $_SESSION['sale_summary'] : null;
unset($_SESSION['sale_summary']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $itemId = filter_var(isset($_POST['idbarang']) ? $_POST['idbarang'] : null, FILTER_VALIDATE_INT);
    $quantity = filter_var(isset($_POST['qty']) ? $_POST['qty'] : null, FILTER_VALIDATE_INT);

    if ($itemId === false || $itemId < 1 || $quantity === false || $quantity < 1) {
        $error = 'Pilih barang dan masukkan jumlah positif.';
    } else {
        try {
            $koneksi->begin_transaction();

            $item = $koneksi->prepare('SELECT idbarang, nama, harga FROM barang WHERE idbarang = ? AND status = 1 FOR UPDATE');
            $item->bind_param('i', $itemId);
            $item->execute();
            $barang = $item->get_result()->fetch_assoc();
            $item->close();
            if (!$barang) {
                throw new RuntimeException('Barang tidak ditemukan atau nonaktif.');
            }

            $marginResult = $koneksi->query('SELECT idmargin_penjualan, persen FROM margin_penjualan WHERE status = 1 ORDER BY idmargin_penjualan DESC LIMIT 1');
            $margin = $marginResult->fetch_assoc();
            if (!$margin) {
                throw new RuntimeException('Tidak ada margin penjualan aktif.');
            }

            $stockCheck = $koneksi->prepare('SELECT cek_stok(?) AS sisa_stok');
            $stockCheck->bind_param('i', $itemId);
            $stockCheck->execute();
            $stock = (int) $stockCheck->get_result()->fetch_assoc()['sisa_stok'];
            $stockCheck->close();
            if ($stock < $quantity) {
                throw new RuntimeException('Stok tidak cukup. Stok tersedia: ' . $stock . '.');
            }

            $basePrice = (int) $barang['harga'];
            $unitPrice = (int) round($basePrice * (1 + ((float) $margin['persen'] / 100)));
            if ($unitPrice < 1 || $unitPrice > floor(2147483647 / $quantity)) {
                throw new RuntimeException('Nilai transaksi melebihi batas yang didukung database.');
            }
            $subtotal = $unitPrice * $quantity;
            $ppn = 0;
            $total = $subtotal;
            $userId = (int) $_SESSION['iduser'];
            $marginId = (int) $margin['idmargin_penjualan'];

            $sale = $koneksi->prepare('INSERT INTO penjualan (subtotal_nilai, ppn, total_nilai, iduser, idmargin_penjualan) VALUES (?, ?, ?, ?, ?)');
            $sale->bind_param('iiiii', $subtotal, $ppn, $total, $userId, $marginId);
            $sale->execute();
            $saleId = (int) $koneksi->insert_id;
            $sale->close();

            $detail = $koneksi->prepare('INSERT INTO detail_penjualan (harga_satuan, jumlah, subtotal, penjualan_idpenjualan, idbarang) VALUES (?, ?, ?, ?, ?)');
            $detail->bind_param('iiiii', $unitPrice, $quantity, $subtotal, $saleId, $itemId);
            $detail->execute();
            $detail->close();

            $koneksi->commit();
            $_SESSION['sale_summary'] = array(
                'id' => $saleId,
                'nama' => $barang['nama'],
                'jumlah' => $quantity,
                'total' => $total,
                'stok' => $stock - $quantity
            );
            header('Location: penjualan.php');
            exit;
        } catch (RuntimeException $exception) {
            $koneksi->rollback();
            $error = $exception->getMessage();
        } catch (mysqli_sql_exception $exception) {
            $koneksi->rollback();
            error_log($exception->getMessage());
            $error = 'Transaksi gagal disimpan. Periksa stok dan konfigurasi function/trigger database.';
        }
    }
}

$items = $koneksi->query('SELECT idbarang, nama, harga FROM barang WHERE status = 1 ORDER BY nama')->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html>
<head><title>Kasir POS</title></head>
<body>
    <h2>Transaksi Kasir</h2>
    <p><a href="dashboard.php">Kembali ke Dashboard</a></p>
    <?php if ($error !== '') { ?><p style="color:red;"><?php echo h($error); ?></p><?php } ?>
    <?php if ($saleSummary) { ?>
        <p style="color:green;">Transaksi #<?php echo (int) $saleSummary['id']; ?> tersimpan. <?php echo h($saleSummary['nama']); ?> x <?php echo (int) $saleSummary['jumlah']; ?>; total Rp <?php echo number_format((int) $saleSummary['total'], 0, ',', '.'); ?>; sisa stok <?php echo (int) $saleSummary['stok']; ?>. Trigger detail_penjualan telah mencatat stok keluar.</p>
    <?php } ?>
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo h(csrf_token()); ?>">
        <label>Barang:</label>
        <select name="idbarang" required>
            <option value="">-- Pilih barang --</option>
            <?php foreach ($items as $item) { ?>
                <option value="<?php echo (int) $item['idbarang']; ?>"><?php echo h($item['nama']); ?> (harga dasar Rp <?php echo number_format((int) $item['harga'], 0, ',', '.'); ?>)</option>
            <?php } ?>
        </select><br><br>
        <label>Jumlah:</label>
        <input type="number" name="qty" min="1" step="1" required><br><br>
        <button type="submit">Checkout</button>
    </form>
    <p>Harga jual mockup dihitung dari harga dasar ditambah margin aktif terbaru; PPN diset 0 karena SQL belum menetapkan tarif PPN.</p>
</body>
</html>
