<?php
require_once 'auth.php';
require_login();
require_owner();
require_once 'koneksi.php';

$config = array(
    'barang' => array(
        'label' => 'Data Barang',
        'pk' => 'id',
        'headers' => array('ID', 'Kode Barang', 'Nama Barang', 'Harga', 'Stok', 'Supplier'),
        'keys' => array('id', 'kode_barang', 'nama_barang', 'harga', 'stok', 'supplier'),
        'select' => "SELECT b.id, b.kode_barang, b.nama_barang, b.harga, b.stok, s.nama AS supplier
                     FROM barang b LEFT JOIN supplier s ON s.id = b.supplier_id ORDER BY b.id ASC",
        'fields' => array(
            array('kode_barang', 'Kode Barang', 'text'),
            array('nama_barang', 'Nama Barang', 'text'),
            array('harga', 'Harga', 'number'),
            array('stok', 'Stok', 'number'),
            array('supplier_id', 'Supplier', 'supplier'),
        ),
    ),
    'supplier' => array(
        'label' => 'Data Supplier',
        'pk' => 'id',
        'headers' => array('ID', 'Nama Supplier', 'Telp', 'Alamat'),
        'keys' => array('id', 'nama', 'telp', 'alamat'),
        'select' => "SELECT id, nama, telp, alamat FROM supplier ORDER BY id ASC",
        'fields' => array(
            array('nama', 'Nama Supplier', 'text'),
            array('telp', 'Telp', 'text'),
            array('alamat', 'Alamat', 'text'),
        ),
    ),
    'pelanggan' => array(
        'label' => 'Data Pelanggan',
        'pk' => 'id',
        'headers' => array('ID', 'Nama', 'Jenis Kelamin', 'Telp', 'Alamat'),
        'keys' => array('id', 'nama', 'jenis_kelamin', 'telp', 'alamat'),
        'select' => "SELECT id, nama, jenis_kelamin, telp, alamat FROM pelanggan ORDER BY id ASC",
        'fields' => array(
            array('id', 'ID Pelanggan', 'text'),
            array('nama', 'Nama', 'text'),
            array('jenis_kelamin', 'Jenis Kelamin', 'gender'),
            array('telp', 'Telp', 'text'),
            array('alamat', 'Alamat', 'text'),
        ),
    ),
    'user' => array(
        'label' => 'Data User',
        'pk' => 'id_user',
        'headers' => array('ID', 'Username', 'Nama', 'Level', 'Telp'),
        'keys' => array('id_user', 'username', 'nama', 'level_text', 'hp'),
        'select' => "SELECT id_user, username, nama, hp, CASE level WHEN 1 THEN 'Owner' ELSE 'Kasir' END AS level_text
                     FROM `user` ORDER BY id_user ASC",
        'fields' => array(
            array('username', 'Username', 'text'),
            array('password', 'Password', 'password'),
            array('nama', 'Nama', 'text'),
            array('alamat', 'Alamat', 'text'),
            array('hp', 'Telp', 'text'),
            array('level', 'Level', 'level'),
        ),
    ),
);

$t = isset($_GET['t']) ? $_GET['t'] : 'barang';
if (!isset($config[$t])) {
    $t = 'barang';
}
$cfg = $config[$t];
$pk = $cfg['pk'];
$a = isset($_GET['a']) ? $_GET['a'] : '';
$pesan = isset($_GET['pesan']) ? $_GET['pesan'] : '';
$id = isset($_GET['id']) ? $_GET['id'] : '';

function pilihan($type, $selected)
{
    global $conn;
    if ($type === 'supplier') {
        $html = '<option value="">-- Pilih Supplier --</option>';
        $q = mysqli_query($conn, "SELECT id, nama FROM supplier ORDER BY nama ASC");
        while ($r = mysqli_fetch_assoc($q)) {
            $s = ((string) $r['id'] === (string) $selected) ? ' selected' : '';
            $html .= '<option value="' . htmlspecialchars($r['id']) . '"' . $s . '>' . htmlspecialchars($r['nama']) . '</option>';
        }
        return $html;
    }
    if ($type === 'gender') {
        $ops = array('L' => 'Laki-laki', 'P' => 'Perempuan');
    } elseif ($type === 'level') {
        $ops = array('1' => 'Owner (level 1)', '2' => 'Kasir (level 2)');
    } else {
        return '';
    }
    $html = '';
    foreach ($ops as $v => $label) {
        $s = ((string) $v === (string) $selected) ? ' selected' : '';
        $html .= '<option value="' . htmlspecialchars($v) . '"' . $s . '>' . htmlspecialchars($label) . '</option>';
    }
    return $html;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($a === 'simpan' || $a === 'update')) {
    $values = array();
    foreach ($cfg['fields'] as $field) {
        $name = $field[0];
        $type = $field[2];
        $value = isset($_POST[$name]) ? trim($_POST[$name]) : '';
        if ($name === 'password') {
            if ($value === '' && $a === 'update') {
                continue;
            }
            $value = password_hash($value, PASSWORD_DEFAULT);
        }
        $values[$name] = $value;
    }

    if ($a === 'simpan') {
        $names = array_keys($values);
        $cols = implode(', ', array_map(function ($n) { return '`' . $n . '`'; }, $names));
        $marks = implode(', ', array_fill(0, count($names), '?'));
        $sql = "INSERT INTO `$t` ($cols) VALUES ($marks)";
    } else {
        $sets = array();
        foreach (array_keys($values) as $n) {
            $sets[] = '`' . $n . '` = ?';
        }
        $sql = "UPDATE `$t` SET " . implode(', ', $sets) . " WHERE `$pk` = ?";
        $values[$pk] = $_POST[$pk];
    }

    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        $params = array_values($values);
        $types = implode('', array_fill(0, count($params), 's'));
        $refs = array();
        foreach ($params as $key => $value) {
            $refs[$key] = &$params[$key];
        }
        call_user_func_array('mysqli_stmt_bind_param', array_merge(array($stmt, $types), $refs));
        $berhasil = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        header("Location: master.php?t=$t&pesan=" . ($berhasil ? 'sukses' : 'gagal'));
        exit;
    }
    header("Location: master.php?t=$t&pesan=gagal");
    exit;
}

if ($a === 'hapus' && $id !== '') {
    $stmt = mysqli_prepare($conn, "DELETE FROM `$t` WHERE `$pk` = ?");
    mysqli_stmt_bind_param($stmt, "s", $id);
    $berhasil = mysqli_stmt_execute($stmt);
    $terhapus = mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);
    if ($berhasil && $terhapus > 0) {
        header("Location: master.php?t=$t&pesan=hapus");
    } else {
        header("Location: master.php?t=$t&pesan=gagal_hapus");
    }
    exit;
}

$rows = mysqli_query($conn, $cfg['select']);
$editRow = null;
if (($a === 'edit') && $id !== '') {
    $stmt = mysqli_prepare($conn, "SELECT * FROM `$t` WHERE `$pk` = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "s", $id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $editRow = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($cfg['label']) ?> - Toko Maju</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <main class="content">
        <h1><?= htmlspecialchars($cfg['label']) ?></h1>

        <nav class="subnav">
            <a href="master.php?t=barang" class="<?= $t === 'barang' ? 'aktif' : '' ?>">Data Barang</a>
            <a href="master.php?t=supplier" class="<?= $t === 'supplier' ? 'aktif' : '' ?>">Data Supplier</a>
            <a href="master.php?t=pelanggan" class="<?= $t === 'pelanggan' ? 'aktif' : '' ?>">Data Pelanggan</a>
            <a href="master.php?t=user" class="<?= $t === 'user' ? 'aktif' : '' ?>">Data User</a>
        </nav>

        <?php if ($pesan === 'sukses'): ?>
            <p class="pesan sukses">Data berhasil disimpan.</p>
        <?php elseif ($pesan === 'hapus'): ?>
            <p class="pesan sukses">Data berhasil dihapus.</p>
        <?php elseif ($pesan === 'gagal'): ?>
            <p class="pesan error">Data gagal disimpan.</p>
        <?php elseif ($pesan === 'gagal_hapus'): ?>
            <p class="pesan error">Data tidak dapat dihapus karena masih digunakan oleh data lain.</p>
        <?php endif; ?>

        <a class="btn" href="master.php?t=<?= $t ?>&a=add">+ Tambah Data</a>

        <table>
            <thead>
                <tr>
                    <?php foreach ($cfg['headers'] as $h): ?>
                        <th><?= htmlspecialchars($h) ?></th>
                    <?php endforeach; ?>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($rows)): ?>
                    <tr>
                        <?php foreach ($cfg['keys'] as $k): ?>
                            <td><?= htmlspecialchars($row[$k]) ?></td>
                        <?php endforeach; ?>
                        <td>
                            <a href="master.php?t=<?= $t ?>&a=edit&id=<?= urlencode($row[$pk]) ?>">Edit</a>
                            <a class="hapus" href="master.php?t=<?= $t ?>&a=hapus&id=<?= urlencode($row[$pk]) ?>" onclick="return confirm('Yakin hapus data ini?')">Hapus</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <?php if ($a === 'add' || $a === 'edit'): ?>
            <h2><?= $a === 'add' ? 'Tambah' : 'Ubah' ?> <?= htmlspecialchars($cfg['label']) ?></h2>
            <form class="form-master" method="POST" action="master.php?t=<?= $t ?>&a=<?= $a === 'add' ? 'simpan' : 'update' ?>">
                <?php if ($a === 'edit'): ?>
                    <input type="hidden" name="<?= htmlspecialchars($pk) ?>" value="<?= htmlspecialchars($editRow[$pk]) ?>">
                <?php endif; ?>

                <?php foreach ($cfg['fields'] as $field): ?>
                    <?php
                    $name = $field[0];
                    $label = $field[1];
                    $type = $field[2];
                    $value = ($editRow !== null && $name !== 'password') ? $editRow[$name] : '';
                    $extra = '';
                    if ($name === 'password') {
                        $extra = $a === 'add' ? 'required' : 'placeholder="Kosongkan jika tidak diubah"';
                    }
                    ?>
                    <label for="<?= htmlspecialchars($name) ?>"><?= htmlspecialchars($label) ?></label>
                    <?php if (in_array($type, array('supplier', 'gender', 'level'))): ?>
                        <select id="<?= htmlspecialchars($name) ?>" name="<?= htmlspecialchars($name) ?>">
                            <?= pilihan($type, $value) ?>
                        </select>
                    <?php else: ?>
                        <input type="<?= $type === 'password' ? 'password' : $type ?>" id="<?= htmlspecialchars($name) ?>" name="<?= htmlspecialchars($name) ?>" value="<?= htmlspecialchars($value) ?>" <?= $extra ?>>
                    <?php endif; ?>
                <?php endforeach; ?>

                <button type="submit">Simpan</button>
                <a class="btn-batal" href="master.php?t=<?= $t ?>">Batal</a>
            </form>
        <?php endif; ?>
    </main>
</body>
</html>
