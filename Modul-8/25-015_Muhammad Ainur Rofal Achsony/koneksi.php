<?php
$host = "localhost";
$user = "root";
$pass = "";
$database = "penjualan";

mysqli_report(MYSQLI_REPORT_OFF);

$conn = mysqli_connect($host, $user, $pass, $database);
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error() . ". Jalankan database.php terlebih dahulu.");
}
mysqli_set_charset($conn, "utf8mb4");
