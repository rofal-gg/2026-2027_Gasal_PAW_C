<?php
session_start();

function is_login()
{
    return isset($_SESSION['login']);
}

function level_user()
{
    return $_SESSION['level'];
}

function nama_user()
{
    return $_SESSION['nama'];
}

function require_login()
{
    if (!isset($_SESSION['login'])) {
        header('Location: login.php');
        exit;
    }
}

function require_owner()
{
    require_login();
    if ($_SESSION['level'] != 1) {
        header('Location: index.php?pesan=akses');
        exit;
    }
}
