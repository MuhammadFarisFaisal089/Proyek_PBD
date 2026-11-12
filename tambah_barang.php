<?php
require_once 'auth.php';
require_roles(array(1));
require_once 'koneksi.php';

$error = '';
$success = '';
$unitsResult = $koneksi->query('SELECT idsatuan, nama_satuan FROM satuan WHERE status = 1 ORDER BY nama_satuan');
$units = $unitsResult->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    if (isset($_POST['add_unit'])) {
        $unitName = trim(isset($_POST['nama_satuan']) ? $_POST['nama_satuan'] : '');
        if ($unitName === '' || mb_strlen($unitName, 'UTF-8') > 45) {
            $error = 'Nama satuan wajib diisi dan maksimal 45 karakter.';
        } else {
            try {
                $unitStatus = 1;
                $procedureParams = array($unitName, $unitStatus);
                execute_procedure($koneksi, 'CALL tambah_satuan(?, ?)', 'si', $procedureParams);
                header('Location: tambah_barang.php?unit_added=1');
                exit;
            } catch (mysqli_sql_exception $exception) {
                error_log($exception->getMessage());
                $error = 'Satuan gagal ditambahkan. Periksa data dan coba lagi.';
            }
        }
    } elseif (isset($_POST['add_barang'])) {
        $name = trim(isset($_POST['nama']) ? $_POST['nama'] : '');
        $type = isset($_POST['jenis']) ? $_POST['jenis'] : '';
        $unitId = filter_var(isset($_POST['idsatuan']) ? $_POST['idsatuan'] : null, FILTER_VALIDATE_INT);
        $price = filter_var(isset($_POST['harga']) ? $_POST['harga'] : null, FILTER_VALIDATE_INT);

        if ($name === '' || mb_strlen($name, 'UTF-8') > 45 || !in_array($type, array('A', 'B'), true) || $unitId === false || $unitId < 1 || $price === false || $price < 0) {
            $error = 'Data barang tidak valid. Periksa nama, jenis, satuan, dan harga.';
        } else {
            try {
                $statement = $koneksi->prepare('INSERT INTO barang (jenis, nama, idsatuan, harga, status) VALUES (?, ?, ?, ?, 1)');
                $statement->bind_param('ssii', $type, $name, $unitId, $price);
                $statement->execute();
                $statement->close();
                header('Location: katalog.php');
                exit;
            } catch (mysqli_sql_exception $exception) {
                error_log($exception->getMessage());
                $error = 'Barang gagal disimpan. Pastikan satuan masih aktif.';
            }
        }
    }
}

if (isset($_GET['unit_added'])) {
    $success = 'Satuan berhasil ditambahkan.';
    $unitsResult = $koneksi->query('SELECT idsatuan, nama_satuan FROM satuan WHERE status = 1 ORDER BY nama_satuan');
    $units = $unitsResult->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html>
<head><title>Kelola Barang</title></head>
<body>
    <h2>Kelola Master Barang</h2>
    <p><a href="dashboard.php">Kembali ke Dashboard</a> | <a href="katalog.php">Katalog</a></p>
    <?php if ($error !== '') { ?><p style="color:red;"><?php echo h($error); ?></p><?php } ?>
    <?php if ($success !== '') { ?><p style="color:green;"><?php echo h($success); ?></p><?php } ?>

    <h3>Tambah Barang</h3>
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo h(csrf_token()); ?>">
        <label>Nama:</label>
        <input type="text" name="nama" maxlength="45" required><br>
        <label>Jenis:</label>
        <select name="jenis" required>
            <option value="A">Tipe A</option>
            <option value="B">Tipe B</option>
        </select><br>
        <label>Satuan:</label>
        <select name="idsatuan" required>
            <?php foreach ($units as $unit) { ?>
                <option value="<?php echo (int) $unit['idsatuan']; ?>"><?php echo h($unit['nama_satuan']); ?></option>
            <?php } ?>
        </select><br>
        <label>Harga dasar:</label>
        <input type="number" name="harga" min="0" step="1" required><br>
        <button type="submit" name="add_barang">Simpan Barang</button>
    </form>

    <h3>Tambah Satuan (Stored Procedure)</h3>
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo h(csrf_token()); ?>">
        <label>Nama satuan:</label>
        <input type="text" name="nama_satuan" maxlength="45" required>
        <button type="submit" name="add_unit">Tambah Satuan</button>
    </form>
</body>
</html>
