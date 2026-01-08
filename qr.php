<?php

declare(strict_types=1);

require_once __DIR__ . '/core/functions.php';

require_login();

$pdo = Database::getConnection();
$settings = get_settings($pdo);
$qrUrl = 'https://chart.googleapis.com/chart?chs=280x280&cht=qr&chl=' . urlencode($settings['menu_link']);

require_once __DIR__ . '/views/header.php';
require_once __DIR__ . '/views/sidebar.php';
?>
<main class="content">
    <div class="page-title">QR Kod</div>
    <div class="section">
        <p>Menü linki:</p>
        <strong><?= e($settings['menu_link']) ?></strong>
        <div style="margin-top: 16px;">
            <img src="<?= e($qrUrl) ?>" alt="QR Kod">
        </div>
    </div>
</main>
<?php require_once __DIR__ . '/views/footer.php'; ?>
