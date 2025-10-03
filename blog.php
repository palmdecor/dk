<?php
require __DIR__ . '/includes/bootstrap.php';

$meta = seo_meta_tags($translations, 'meta.blog.title', 'meta.blog.description');

$stmt = $pdo->query("SELECT posts.*, (SELECT COUNT(*) FROM comments WHERE comments.post_id = posts.id AND comments.status = 'approved') AS comment_count FROM posts WHERE status = 'published' ORDER BY (published_at IS NULL), published_at DESC, created_at DESC");
$posts = $stmt->fetchAll();

include __DIR__ . '/partials/header.php';
?>
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h1 class="fw-bold mb-3"><?= __t('blog.title', $translations) ?></h1>
            <p class="text-muted lead mb-0"><?= __t('meta.blog.description', $translations) ?></p>
        </div>
        <div class="row g-4">
            <?php foreach ($posts as $post): ?>
                <div class="col-md-6 col-lg-4">
                    <article class="card h-100 shadow-sm border-0">
                        <div class="card-body d-flex flex-column">
                            <h2 class="h5 fw-bold"><a href="post.php?slug=<?= urlencode($post['slug']) ?>" class="stretched-link text-decoration-none"><?= htmlspecialchars($post['title']) ?></a></h2>
                            <div class="text-muted small mb-2">
                                <?= date('d.m.Y', strtotime($post['published_at'] ?? $post['created_at'])) ?> · <?= sprintf(__t('blog.comments', $translations), (int) $post['comment_count']) ?>
                            </div>
                            <p class="text-muted flex-grow-1">
                                <?= htmlspecialchars($post['excerpt'] ?: mb_substr(strip_tags($post['body']), 0, 120) . '...') ?>
                            </p>
                            <a href="post.php?slug=<?= urlencode($post['slug']) ?>" class="btn btn-outline-primary mt-3 align-self-start"><?= __t('blog.read_more', $translations) ?></a>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
            <?php if (empty($posts)): ?>
                <div class="col-12">
                    <div class="alert alert-info text-center mb-0"><?= __t('blog.empty', $translations) ?></div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php include __DIR__ . '/partials/footer.php'; ?>
