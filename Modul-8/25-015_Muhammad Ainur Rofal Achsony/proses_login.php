<?php
require_once 'auth.php';
require_once 'koneksi.php';

$username = isset($_POST['username']) ? mysqli_real_escape_string($conn, $_POST['username']) : '';
$password = isset($_POST['password']) ? md5($_POST['password']) : '';

$query = mysqli_query($conn, "SELECT * FROM `user` WHERE username = '$username' AND password = '$password'");

if (mysqli_num_rows($query) > 0) {
    $data = mysqli_fetch_assoc($query);
    $_SESSION['login'] = true;
    $_SESSION['id_user'] = $data['id_user'];
    $_SESSION['username'] = $data['username'];
    $_SESSION['nama'] = $data['nama'];
    $_SESSION['level'] = (int) $data['level'];
    header('Location: index.php');
    exit;
} else {
    header('Location: login.php?error=1');
    exit;
}
