<?php
require_once 'auth.php';
require_login();
require_once 'koneksi.php';

$id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;
$pesan = isset($_GET["pesan"]) ? $_GET["pesan"] : "";

$pesanSukses = "";
$pesanError = "";

if ($pesan == "tambah") {
    $pesanSukses = "Detail transaksi berhasil disimpan. Total transaksi sudah diperbarui.";
} elseif ($pesan == "dobel") {
    $pesanError = "Barang tersebut sudah ada di detail transaksi ini, jadi tidak boleh dipilih lagi.";
} elseif ($pesan == "gagal") {
    $pesanError = "Detail transaksi gagal disimpan. Periksa kembali data yang diinput.";
}

$dataTransaksi = mysqli_query($conn, "SELECT t.id, t.waktu_transaksi, t.keterangan, t.total, p.nama AS nama_pelanggan
                                       FROM transaksi t
                                       JOIN pelanggan p ON p.id = t.pelanggan_id
                                       ORDER BY t.id ASC");

if ($id == 0) {
    $ambilPertama = mysqli_query($conn, "SELECT id FROM transaksi ORDER BY id ASC LIMIT 1");
    $barisPertama = mysqli_fetch_assoc($ambilPertama);
    if ($barisPertama != null) {
        $id = (int) $barisPertama["id"];
    }
}

$transaksi = null;
if ($id > 0) {
    $stmt = mysqli_prepare($conn, "SELECT t.id, t.waktu_transaksi, t.keterangan, t.total, p.nama AS nama_pelanggan
                                   FROM transaksi t JOIN pelanggan p ON p.id = t.pelanggan_id
                                   WHERE t.id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $transaksi = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);
}

$dataDetail = array();
$idTerpakai = array();

if ($transaksi != null) {
    $stmt = mysqli_prepare($conn, "SELECT td.barang_id, td.harga, td.qty, b.nama_barang
                                   FROM transaksi_detail td JOIN barang b ON b.id = td.barang_id
                                   WHERE td.transaksi_id = ? ORDER BY b.nama_barang ASC");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($res)) {
        $dataDetail[] = $row;
        $idTerpakai[] = (int) $row["barang_id"];
    }
    mysqli_stmt_close($stmt);
}

$dataBarang = mysqli_query($conn, "SELECT id, nama_barang, harga FROM barang ORDER BY nama_barang ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi - Toko Maju</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <main class="content">
        <h1>Transaksi</h1>

        <?php if ($pesanSukses != ""): ?>
            <p class="pesan sukses"><?= htmlspecialchars($pesanSukses) ?></p>
        <?php endif; ?>
        <?php if ($pesanError != ""): ?>
            <p class="pesan error"><?= htmlspecialchars($pesanError) ?></p>
        <?php endif; ?>

        <h2>Data Transaksi</h2>
        <table>
            <thead>
                <tr>
                    <th>No</th><th>ID</th><th>Tanggal</th><th>Pelanggan</th><th>Keterangan</th><th>Total</th><th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $no = 1;
                while ($row = mysqli_fetch_assoc($dataTransaksi)):
                ?>
                    <tr class="<?= ((int) $row["id"] == $id) ? "aktif" : "" ?>">
                        <td><?= $no ?></td>
                        <td><?= $row["id"] ?></td>
                        <td><?= htmlspecialchars($row["waktu_transaksi"]) ?></td>
                        <td><?= htmlspecialchars($row["nama_pelanggan"]) ?></td>
                        <td><?= htmlspecialchars($row["keterangan"]) ?></td>
                        <td>Rp <?= number_format($row["total"], 0, ",", ".") ?></td>
                        <td><a class="btn" href="transaksi.php?id=<?= $row["id"] ?>">Pilih</a></td>
                    </tr>
                <?php
                    $no++;
                endwhile;
                ?>
            </tbody>
        </table>

        <?php if ($transaksi != null): ?>
            <h2>Detail Transaksi #<?= $transaksi["id"] ?></h2>
            <table class="info">
                <tr><th>Tanggal</th><td><?= htmlspecialchars($transaksi["waktu_transaksi"]) ?></td></tr>
                <tr><th>Pelanggan</th><td><?= htmlspecialchars($transaksi["nama_pelanggan"]) ?></td></tr>
                <tr><th>Keterangan</th><td><?= htmlspecialchars($transaksi["keterangan"]) ?></td></tr>
                <tr><th>Total</th><td>Rp <?= number_format($transaksi["total"], 0, ",", ".") ?></td></tr>
            </table>

            <table>
                <thead>
                    <tr><th>No</th><th>Nama Barang</th><th>Harga</th><th>Qty</th><th>Subtotal</th></tr>
                </thead>
                <tbody>
                    <?php if (count($dataDetail) == 0): ?>
                        <tr><td colspan="5">Belum ada detail untuk transaksi ini.</td></tr>
                    <?php else: ?>
                        <?php
                        $noDetail = 1;
                        foreach ($dataDetail as $detail):
                        ?>
                            <tr>
                                <td><?= $noDetail ?></td>
                                <td><?= htmlspecialchars($detail["nama_barang"]) ?></td>
                                <td>Rp <?= number_format($detail["harga"], 0, ",", ".") ?></td>
                                <td><?= $detail["qty"] ?></td>
                                <td>Rp <?= number_format($detail["harga"] * $detail["qty"], 0, ",", ".") ?></td>
                            </tr>
                        <?php
                            $noDetail++;
                        endforeach;
                        ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <h2>Tambah Detail Transaksi</h2>
            <form class="form-master" method="POST" action="tambah_detail.php">
                <input type="hidden" name="transaksi_id" value="<?= $transaksi["id"] ?>">
                <label for="barang_id">Barang</label>
                <select id="barang_id" name="barang_id" required>
                    <option value="">-- Pilih Barang --</option>
                    <?php while ($barang = mysqli_fetch_assoc($dataBarang)): ?>
                        <option value="<?= $barang["id"] ?>" <?= in_array((int) $barang["id"], $idTerpakai) ? "disabled" : "" ?>>
                            <?= htmlspecialchars($barang["nama_barang"]) ?> - Rp <?= number_format($barang["harga"], 0, ",", ".") ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <label for="qty">Qty</label>
                <input type="number" id="qty" name="qty" min="1" value="1" required>
                <button type="submit">Tambah Detail</button>
            </form>
        <?php elseif ($id > 0): ?>
            <p class="pesan error">Transaksi tidak ditemukan.</p>
        <?php endif; ?>
    </main>
</body>
</html>
