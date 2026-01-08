<?php

declare(strict_types=1);

require_once __DIR__ . '/core/functions.php';

require_login();

$pdo = Database::getConnection();
$settings = get_settings($pdo);
$counts = dashboard_counts($pdo);

require_once __DIR__ . '/views/header.php';
require_once __DIR__ . '/views/sidebar.php';
?>
<main class="content">
    <div class="page-title">Dashboard</div>

    <?php if (!empty($flash)) : ?>
        <div class="flash <?= e($flash['type']) ?>">
            <?= e($flash['message']) ?>
        </div>
    <?php endif; ?>

    <div class="cards">
        <div class="card">
            <div class="card__label">Toplam Kategori</div>
            <div class="card__value"><?= e((string) $counts['categories']) ?></div>
        </div>
        <div class="card">
            <div class="card__label">Toplam Ürün</div>
            <div class="card__value"><?= e((string) $counts['products']) ?></div>
        </div>
        <div class="card">
            <div class="card__label">Aktif Ürün</div>
            <div class="card__value"><?= e((string) $counts['active_products']) ?></div>
        </div>
    </div>

    <div class="section">
        <h3>Menü Linki</h3>
        <p><?= e($settings['menu_link']) ?></p>
    </div>

    <div class="section">
        <h3>QR Kod</h3>
        <?php $qrUrl = 'https://chart.googleapis.com/chart?chs=200x200&cht=qr&chl=' . urlencode($settings['menu_link']); ?>
        <img src="<?= e($qrUrl) ?>" alt="QR Kod">
    </div>
</main>
<?php require_once __DIR__ . '/views/footer.php'; ?>
