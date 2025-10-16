<?php
if (!defined('APP_ENTRY')) {
    require __DIR__ . '/includes/bootstrap.php';
}

$slug = trim($_GET['slug'] ?? '');
$page = $slug !== '' ? find_page_by_slug($pdo, $slug) : null;

if ($slug === '' || !$page) {
    http_response_code(404);
    $meta = seo_meta_tags($translations, 'meta.404.title', 'meta.404.description');
    $flash = get_flash_messages();
    include __DIR__ . '/views/not-found.php';
    exit;
}

$meta = [
    'title' => !empty($page['meta_title']) ? $page['meta_title'] : sprintf(__t('meta.page.title', $translations), $page['title']),
    'description' => !empty($page['meta_description']) ? $page['meta_description'] : __t('meta.page.description', $translations),
];

include __DIR__ . '/partials/header.php';
?>
<section class="py-5 bg-light">
    <div class="container">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= site_url() ?>"><?= __t('nav.home', $translations) ?></a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($page['title']) ?></li>
            </ol>
        </nav>
        <div class="bg-white shadow-sm rounded p-4">
            <h1 class="fw-bold mb-3"><?= htmlspecialchars($page['title']) ?></h1>
            <div class="text-muted small mb-4">
                <?= date('d.m.Y', strtotime($page['created_at'])) ?>
            </div>
            <article class="cms-content">
                <?= $page['content'] ?>
            </article>
        </div>
    </div>
</section>
<?php include __DIR__ . '/partials/footer.php'; ?>
