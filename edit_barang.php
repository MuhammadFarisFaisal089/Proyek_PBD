<?php
require_once 'auth.php';
require_roles(array(1));
require_once 'koneksi.php';

$id = filter_var(isset($_GET['id']) ? $_GET['id'] : null, FILTER_VALIDATE_INT);
if ($id === false || $id < 1) {
    http_response_code(400);
    exit('ID barang tidak valid.');
}

$statement = $koneksi->prepare('SELECT idbarang, nama, jenis, harga FROM barang WHERE idbarang = ? AND status = 1');
$statement->bind_param('i', $id);
$statement->execute();
$result = $statement->get_result();
$barang = $result->fetch_assoc();
$statement->close();

if (!$barang) {
    http_response_code(404);
    exit('Barang tidak ditemukan atau sudah nonaktif.');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim(isset($_POST['nama']) ? $_POST['nama'] : '');
    $type = isset($_POST['jenis']) ? $_POST['jenis'] : '';
    $price = filter_var(isset($_POST['harga']) ? $_POST['harga'] : null, FILTER_VALIDATE_INT);

    if ($name === '' || mb_strlen($name, 'UTF-8') > 45 || !in_array($type, array('A', 'B'), true) || $price === false || $price < 0) {
        $error = 'Data barang tidak valid.';
    } else {
        $update = $koneksi->prepare('UPDATE barang SET nama = ?, jenis = ?, harga = ? WHERE idbarang = ? AND status = 1');
        $update->bind_param('ssii', $name, $type, $price, $id);
        $update->execute();
        $update->close();
        header('Location: katalog.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Edit Barang</title></head>
<body>
    <h2>Edit Data Barang</h2>
    <p><a href="katalog.php">Kembali ke Katalog</a></p>
    <?php if ($error !== '') { ?><p style="color:red;"><?php echo h($error); ?></p><?php } ?>
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo h(csrf_token()); ?>">
        <label>Nama:</label>
        <input type="text" name="nama" maxlength="45" value="<?php echo h($barang['nama']); ?>" required><br>
        <label>Jenis:</label>
        <select name="jenis" required>
            <option value="A" <?php echo $barang['jenis'] === 'A' ? 'selected' : ''; ?>>Tipe A</option>
            <option value="B" <?php echo $barang['jenis'] === 'B' ? 'selected' : ''; ?>>Tipe B</option>
        </select><br>
        <label>Harga dasar:</label>
        <input type="number" name="harga" min="0" step="1" value="<?php echo (int) $barang['harga']; ?>" required><br>
        <button type="submit">Update Data</button>
    </form>
</body>
</html>
