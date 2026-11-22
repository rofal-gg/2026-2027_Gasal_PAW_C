<?php
require_once 'auth.php';
require_login();

$level = level_user();
$pesan = isset($_GET['pesan']) ? $_GET['pesan'] : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Toko Maju</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <main class="content">
        <h1>Selamat Datang, <?= htmlspecialchars(nama_user()) ?></h1>
        <p>Anda login sebagai <strong><?= $level === 1 ? 'Owner (level 1)' : 'Kasir (level 2)' ?></strong>.</p>

        <?php if ($pesan === 'akses'): ?>
            <p class="pesan error">Anda tidak berhak mengakses halaman tersebut.</p>
        <?php endif; ?>

        <h2>Menu yang Dapat Diakses</h2>
        <div class="cards">
            <a class="card" href="index.php"><strong>Home</strong><span>Halaman utama</span></a>
            <?php if ($level === 1): ?>
                <a class="card" href="master.php?t=barang"><strong>Data Master</strong><span>Barang, Supplier, Pelanggan, User</span></a>
            <?php endif; ?>
            <a class="card" href="transaksi.php"><strong>Transaksi</strong><span>Data transaksi dan detail</span></a>
            <a class="card" href="laporan.php"><strong>Laporan</strong><span>Rekapitulasi penjualan</span></a>
        </div>
    </main>
</body>
</html>
