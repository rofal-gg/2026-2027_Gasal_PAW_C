<?php
$level = level_user();
$nama = htmlspecialchars(nama_user());
?>
<header class="header">
    <div class="header-inner">
        <a class="brand" href="index.php">Toko Maju</a>
        <nav class="nav">
            <a href="index.php">Home</a>
            <?php if ($level === 1): ?>
                <div class="dropdown">
                    <span>Data Master</span>
                    <div class="dropdown-menu">
                        <a href="master.php?t=barang">Data Barang</a>
                        <a href="master.php?t=supplier">Data Supplier</a>
                        <a href="master.php?t=pelanggan">Data Pelanggan</a>
                        <a href="master.php?t=user">Data User</a>
                    </div>
                </div>
            <?php endif; ?>
            <a href="transaksi.php">Transaksi</a>
            <a href="laporan.php">Laporan</a>
        </nav>
        <div class="user-area">
            <span class="user-name">Halo, <?= $nama ?></span>
            <a class="btn-logout" href="logout.php">Logout</a>
        </div>
    </div>
</header>
