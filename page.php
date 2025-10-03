<?php
require __DIR__ . '/includes/bootstrap.php';

$slug = trim($_GET['slug'] ?? '');
$page = $slug !== '' ? find_page_by_slug($pdo, $slug) : null;

if ($slug === '' || !$page) {
    http_response_code(404);
    $meta = ['title' => '404', 'description' => __t('errors.not_found', $translations)];
    include __DIR__ . '/partials/header.php';
    ?>
    <section class="py-5">
        <div class="container text-center">
            <h1 class="display-4 fw-bold">404</h1>
            <p class="lead text-muted"><?= __t('errors.not_found', $translations) ?></p>
            <a href="index.php" class="btn btn-primary mt-3"><?= __t('nav.home', $translations) ?></a>
        </div>
    </section>
    <?php
    include __DIR__ . '/partials/footer.php';
    exit;
}

$meta = [
    'title' => sprintf(__t('meta.page.title', $translations), $page['title']),
    'description' => __t('meta.page.description', $translations),
];

include __DIR__ . '/partials/header.php';
?>
<section class="py-5 bg-light">
    <div class="container">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php"><?= __t('nav.home', $translations) ?></a></li>
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
