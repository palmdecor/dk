<?php

declare(strict_types=1);

require_once __DIR__ . '/core/functions.php';

require_login();

$pdo = Database::getConnection();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validate_csrf();

    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';

    $stmt = $pdo->prepare('SELECT id, password FROM users WHERE id = :id');
    $stmt->execute(['id' => $_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($current, $user['password'])) {
        $error = 'Mevcut şifre hatalı.';
    } elseif (strlen($new) < 6) {
        $error = 'Yeni şifre en az 6 karakter olmalı.';
    } else {
        $stmt = $pdo->prepare('UPDATE users SET password = :password WHERE id = :id');
        $stmt->execute([
            'password' => password_hash($new, PASSWORD_DEFAULT),
            'id' => $user['id'],
        ]);
        flash_message('Şifre güncellendi.');
        redirect('change_password.php');
    }
}

require_once __DIR__ . '/views/header.php';
require_once __DIR__ . '/views/sidebar.php';
?>
<main class="content">
    <div class="page-title">Şifre Değiştir</div>

    <?php if (!empty($flash)) : ?>
        <div class="flash <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
    <?php endif; ?>

    <?php if ($error) : ?>
        <div class="flash error"><?= e($error) ?></div>
    <?php endif; ?>

    <div class="section">
        <form method="post" class="form-grid">
            <?= csrf_field() ?>
            <label>Mevcut Şifre
                <input type="password" name="current_password" required>
            </label>
            <label>Yeni Şifre
                <input type="password" name="new_password" required>
            </label>
            <div>
                <label>&nbsp;</label>
                <button type="submit">Güncelle</button>
            </div>
        </form>
    </div>
</main>
<?php require_once __DIR__ . '/views/footer.php'; ?>
