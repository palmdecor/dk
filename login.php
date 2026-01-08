<?php

declare(strict_types=1);

require_once __DIR__ . '/core/functions.php';

if (!empty($_SESSION['user_id'])) {
    redirect('index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validate_csrf();

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $pdo = Database::getConnection();
    $stmt = $pdo->prepare('SELECT id, username, password FROM users WHERE username = :username LIMIT 1');
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        redirect('index.php');
    }

    $error = 'Kullanıcı adı veya şifre hatalı.';
}
?>
<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Giriş Yap</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="login">
    <h2>Yönetim Paneli Girişi</h2>
    <?php if ($error) : ?>
        <div class="flash error"><?= e($error) ?></div>
    <?php endif; ?>
    <form method="post">
        <?= csrf_field() ?>
        <label>Kullanıcı Adı
            <input type="text" name="username" required>
        </label>
        <label>Şifre
            <input type="password" name="password" required>
        </label>
        <button type="submit">Giriş Yap</button>
    </form>
</div>
</body>
</html>
