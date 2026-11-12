<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$host = getenv('DB_HOST') ?: '127.0.0.1';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';
$db = getenv('DB_NAME') ?: 'deklarasi_stok';
$port = getenv('DB_PORT') ?: '3306';

$port = filter_var($port, FILTER_VALIDATE_INT);
if ($port === false || $port < 1 || $port > 65535) {
    throw new RuntimeException('DB_PORT harus berupa port MySQL yang valid.');
}

$koneksi = new mysqli($host, $user, $pass, $db, $port);
$koneksi->set_charset('utf8mb4');
?>
