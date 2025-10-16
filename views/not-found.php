<?php
if (!isset($translations, $config, $lang)) {
    throw new RuntimeException('Not found view requires context.');
}
$flash = $flash ?? get_flash_messages();
include __DIR__ . '/../partials/header.php';
include __DIR__ . '/../partials/flash.php';
?>
<section class="py-5">
    <div class="container">
        <div class="text-center">
            <div class="display-1 text-primary mb-4"><i class="bi bi-search"></i></div>
            <h1 class="fw-bold mb-3"><?= __t('errors.not_found.title', $translations) ?></h1>
            <p class="text-muted mb-4"><?= __t('errors.not_found.description', $translations) ?></p>
            <a href="<?= site_url() ?>" class="btn btn-primary"><?= __t('errors.not_found.back_home', $translations) ?></a>
        </div>
    </div>
</section>
<?php include __DIR__ . '/../partials/footer.php'; ?>
