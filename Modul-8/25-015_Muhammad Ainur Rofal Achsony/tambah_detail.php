<?php
require_once 'auth.php';
require_login();
require_once 'koneksi.php';

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: transaksi.php");
    exit;
}

$transaksi_id = isset($_POST["transaksi_id"]) ? (int) $_POST["transaksi_id"] : 0;
$barang_id = isset($_POST["barang_id"]) ? (int) $_POST["barang_id"] : 0;
$qty = isset($_POST["qty"]) ? (int) $_POST["qty"] : 0;

if ($transaksi_id <= 0 || $barang_id <= 0 || $qty <= 0) {
    header("Location: transaksi.php?id=$transaksi_id&pesan=gagal");
    exit;
}

$stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS jumlah FROM transaksi_detail WHERE transaksi_id = ? AND barang_id = ?");
mysqli_stmt_bind_param($stmt, "ii", $transaksi_id, $barang_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$cek = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

if ($cek["jumlah"] > 0) {
    header("Location: transaksi.php?id=$transaksi_id&pesan=dobel");
    exit;
}

$stmt = mysqli_prepare($conn, "SELECT harga FROM barang WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $barang_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$barang = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

if (!$barang) {
    header("Location: transaksi.php?id=$transaksi_id&pesan=gagal");
    exit;
}

$harga = (int) $barang["harga"];

$stmt = mysqli_prepare($conn, "INSERT INTO transaksi_detail (transaksi_id, barang_id, harga, qty) VALUES (?, ?, ?, ?)");
mysqli_stmt_bind_param($stmt, "iiii", $transaksi_id, $barang_id, $harga, $qty);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

mysqli_query($conn, "UPDATE transaksi
                     SET total = (SELECT SUM(harga * qty) FROM transaksi_detail WHERE transaksi_id = $transaksi_id)
                     WHERE id = $transaksi_id");

header("Location: transaksi.php?id=$transaksi_id&pesan=tambah");
exit;
