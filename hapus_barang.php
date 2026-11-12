<?php
require_once 'auth.php';
require_roles(array(1));
require_once 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Metode tidak diizinkan.');
}

verify_csrf();
$id = filter_var(isset($_POST['id']) ? $_POST['id'] : null, FILTER_VALIDATE_INT);
if ($id === false || $id < 1) {
    http_response_code(400);
    exit('ID barang tidak valid.');
}

$statement = $koneksi->prepare('UPDATE barang SET status = 0 WHERE idbarang = ? AND status = 1');
$statement->bind_param('i', $id);
$statement->execute();
$statement->close();

header('Location: katalog.php');
exit;
