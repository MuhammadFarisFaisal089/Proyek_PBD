<?php
require_once 'auth.php';
require_roles(array(1, 3));
require_once 'koneksi.php';

$error = '';
$vendorsResult = $koneksi->query("SELECT idvendor, nama_vendor FROM vendor WHERE status = 'A' ORDER BY nama_vendor");
$vendors = $vendorsResult->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $vendorId = filter_var(isset($_POST['idvendor']) ? $_POST['idvendor'] : null, FILTER_VALIDATE_INT);

    if ($vendorId === false || $vendorId < 1) {
        $error = 'Pilih vendor yang valid.';
    } else {
        try {
            $statement = $koneksi->prepare("INSERT INTO pengadaan (user_iduser, status, vendor_idvendor, subtotal_nilai, ppn, total_nilai) SELECT ?, 'P', idvendor, 0, 0, 0 FROM vendor WHERE idvendor = ? AND status = 'A'");
            $userId = (int) $_SESSION['iduser'];
            $statement->bind_param('ii', $userId, $vendorId);
            $statement->execute();
            if ($statement->affected_rows !== 1) {
                $statement->close();
                $error = 'Vendor tidak ditemukan atau sudah nonaktif.';
            } else {
                $purchaseId = (int) $koneksi->insert_id;
                $statement->close();
                header('Location: detail_pengadaan.php?id=' . $purchaseId);
                exit;
            }
        } catch (mysqli_sql_exception $exception) {
            error_log($exception->getMessage());
            $error = 'Dokumen pengadaan gagal dibuat.';
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Buat Pengadaan</title></head>
<body>
    <h2>Buat Pengadaan Baru</h2>
    <p><a href="dashboard.php">Kembali ke Dashboard</a></p>
    <?php if ($error !== '') { ?><p style="color:red;"><?php echo h($error); ?></p><?php } ?>
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo h(csrf_token()); ?>">
        <label>Vendor:</label>
        <select name="idvendor" required>
            <option value="">-- Pilih vendor --</option>
            <?php foreach ($vendors as $vendor) { ?>
                <option value="<?php echo (int) $vendor['idvendor']; ?>"><?php echo h($vendor['nama_vendor']); ?></option>
            <?php } ?>
        </select>
        <button type="submit">Buat PO</button>
    </form>
</body>
</html>
