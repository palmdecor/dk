<?php
if (!defined('APP_ENTRY')) {
    require __DIR__ . '/includes/bootstrap.php';
}
$meta = seo_meta_tags($translations, 'meta.kvkk.title', 'meta.kvkk.description');
$kvkkContent = get_policy_content($pdo, 'kvkk_content', default_kvkk_content());
$flash = get_flash_messages();
include __DIR__ . '/partials/header.php';
include __DIR__ . '/partials/flash.php';
?>
<section class="py-5">
    <div class="container">
        <h1 class="fw-bold mb-4"><?= __t('footer.kvkk', $translations) ?></h1>
        <div class="cms-content">
            <?= $kvkkContent ?>
        </div>
    </div>
</section>
<?php include __DIR__ . '/partials/footer.php'; ?>
