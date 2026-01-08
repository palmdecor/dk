<?php

declare(strict_types=1);

require_once __DIR__ . '/core/functions.php';

require_login();

$pdo = Database::getConnection();
$settings = get_settings($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validate_csrf();

    $name = trim($_POST['restaurant_name'] ?? '');
    $logo = trim($_POST['logo'] ?? '');
    $menuLink = trim($_POST['menu_link'] ?? '');

    if (!empty($settings['id'])) {
        $stmt = $pdo->prepare('UPDATE settings SET restaurant_name = :name, logo = :logo, menu_link = :menu_link WHERE id = :id');
        $stmt->execute([
            'name' => $name,
            'logo' => $logo,
            'menu_link' => $menuLink,
            'id' => $settings['id'],
        ]);
    } else {
        $stmt = $pdo->prepare('INSERT INTO settings (restaurant_name, logo, menu_link) VALUES (:name, :logo, :menu_link)');
        $stmt->execute([
            'name' => $name,
            'logo' => $logo,
            'menu_link' => $menuLink,
        ]);
    }

    flash_message('Restoran ayarları kaydedildi.');
    redirect('settings.php');
}

require_once __DIR__ . '/views/header.php';
require_once __DIR__ . '/views/sidebar.php';
?>
<main class="content">
    <div class="page-title">Restoran Ayarları</div>

    <?php if (!empty($flash)) : ?>
        <div class="flash <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
    <?php endif; ?>

    <div class="section">
        <form method="post" class="form-grid">
            <?= csrf_field() ?>
            <label>Restoran Adı
                <input type="text" name="restaurant_name" required value="<?= e($settings['restaurant_name']) ?>">
            </label>
            <label>Logo URL
                <input type="text" name="logo" value="<?= e($settings['logo']) ?>">
            </label>
            <label>Menü Linki
                <input type="url" name="menu_link" required value="<?= e($settings['menu_link']) ?>">
            </label>
            <div>
                <label>&nbsp;</label>
                <button type="submit">Kaydet</button>
            </div>
        </form>
    </div>
</main>
<?php require_once __DIR__ . '/views/footer.php'; ?>
