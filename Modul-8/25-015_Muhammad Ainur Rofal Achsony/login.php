<?php
require_once 'auth.php';

if (is_login()) {
    header("Location: index.php");
    exit;
}

$error = '';
if (isset($_GET['error'])) {
    $error = 'Username atau password salah.';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Toko Maju</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="login-page">
    <main class="login-box">
        <h1>Login</h1>
        <p class="subtitle">Silakan masuk dengan akun owner atau kasir.</p>

        <?php if ($error !== ''): ?>
            <p class="pesan error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="POST" action="proses_login.php">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" placeholder="owner atau kasir" required>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Masukkan password" required>

            <button type="submit">Login</button>
        </form>

        <p class="hint">Owner: owner / owner123 &nbsp;|&nbsp; Kasir: kasir / kasir123</p>
    </main>
</body>
</html>
