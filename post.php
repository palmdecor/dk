<?php
if (!defined('APP_ENTRY')) {
    require __DIR__ . '/includes/bootstrap.php';
}

$slug = trim($_GET['slug'] ?? '');
$post = null;
if ($slug !== '') {
    $stmt = $pdo->prepare("SELECT * FROM posts WHERE slug = :slug AND status = 'published'");
    $stmt->execute(['slug' => $slug]);
    $post = $stmt->fetch();
}

if ($slug === '' || !$post) {
    http_response_code(404);
    $meta = seo_meta_tags($translations, 'meta.404.title', 'meta.404.description');
    $flash = get_flash_messages();
    include __DIR__ . '/views/not-found.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $message = trim($_POST['message'] ?? '');
    $honeypot = trim($_POST['website'] ?? '');

    if ($honeypot !== '') {
        redirect_with_message(site_url('blog/' . $slug), 'danger', __t('form.error', $translations));
    }

    if ($name === '' || !$email || $message === '') {
        redirect_with_message(site_url('blog/' . $slug), 'danger', __t('form.validation_error', $translations));
    }

    $stmt = $pdo->prepare('INSERT INTO comments (post_id, author_name, author_email, body, status, created_at) VALUES (:post_id, :name, :email, :body, :status, NOW())');
    $stmt->execute([
        'post_id' => $post['id'],
        'name' => $name,
        'email' => $email,
        'body' => $message,
        'status' => 'pending',
    ]);

    redirect_with_message(site_url('blog/' . $slug), 'success', __t('blog.comment.awaiting', $translations));
}

$meta = [
    'title' => !empty($post['meta_title']) ? $post['meta_title'] : sprintf(__t('meta.blog_post.title', $translations), $post['title']),
    'description' => !empty($post['meta_description']) ? $post['meta_description'] : mb_substr(strip_tags($post['excerpt'] ?: $post['body']), 0, 150),
];

$commentStmt = $pdo->prepare("SELECT * FROM comments WHERE post_id = :post_id AND status = 'approved' ORDER BY created_at DESC");
$commentStmt->execute(['post_id' => $post['id']]);
$comments = $commentStmt->fetchAll();

$flash = get_flash_messages();
include __DIR__ . '/partials/header.php';
include __DIR__ . '/partials/flash.php';
?>
<section class="py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <article class="bg-white shadow-sm rounded p-4 mb-4">
                    <h1 class="fw-bold mb-3"><?= htmlspecialchars($post['title']) ?></h1>
                    <div class="text-muted small mb-4">
                        <?= date('d.m.Y', strtotime($post['published_at'] ?? $post['created_at'])) ?>
                    </div>
                    <?php if (!empty($post['image_path'])): ?>
                        <figure class="mb-4">
                            <img src="<?= htmlspecialchars($post['image_path']) ?>" alt="<?= htmlspecialchars($post['title']) ?>" class="img-fluid rounded shadow-sm w-100">
                        </figure>
                    <?php endif; ?>
                    <div class="cms-content">
                        <?= $post['body'] ?>
                    </div>
                </article>
                <section class="bg-white shadow-sm rounded p-4">
                    <h2 class="h4 fw-bold mb-4"><?= sprintf(__t('blog.comments', $translations), count($comments)) ?></h2>
                    <?php foreach ($comments as $comment): ?>
                        <div class="border rounded p-3 mb-3">
                            <div class="fw-semibold"><?= htmlspecialchars($comment['author_name']) ?></div>
                            <div class="text-muted small mb-2"><?= date('d.m.Y H:i', strtotime($comment['created_at'])) ?></div>
                            <p class="mb-0"><?= nl2br(htmlspecialchars($comment['body'])) ?></p>
                        </div>
                    <?php endforeach; ?>
                    <?php if (empty($comments)): ?>
                        <p class="text-muted mb-0"><?= __t('blog.comments.empty', $translations) ?></p>
                    <?php endif; ?>
                </section>
                <section class="bg-white shadow-sm rounded p-4 mt-4">
                    <h3 class="h5 fw-bold mb-3"><?= __t('blog.leave_comment', $translations) ?></h3>
                    <form method="post" class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label"><?= __t('blog.comment.name', $translations) ?></label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><?= __t('blog.comment.email', $translations) ?></label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="col-12 d-none">
                            <label class="form-label">Website</label>
                            <input type="text" name="website" class="form-control" autocomplete="off">
                        </div>
                        <div class="col-12">
                            <label class="form-label"><?= __t('blog.comment.message', $translations) ?></label>
                            <textarea name="message" rows="4" class="form-control" required></textarea>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary"><?= __t('blog.comment.submit', $translations) ?></button>
                        </div>
                    </form>
                </section>
            </div>
            <div class="col-lg-4">
                <aside class="ps-lg-4 mt-4 mt-lg-0">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-body">
                            <h3 class="h6 text-uppercase text-muted mb-3">Son Yazılar</h3>
                            <ul class="list-unstyled mb-0">
                                <?php foreach (latest_posts($pdo, 5) as $recent): ?>
                                    <li class="mb-2"><a href="<?= site_url('blog/' . $recent['slug']) ?>" class="text-decoration-none"><?= htmlspecialchars($recent['title']) ?></a></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <h3 class="h6 text-uppercase text-muted mb-3">Finans Araçları</h3>
                            <p class="small text-muted">Kredi ihtiyacınızı hesaplamak için ana sayfadaki hesaplama aracını kullanın.</p>
                            <a href="<?= site_url('basvuru') ?>" class="btn btn-outline-primary btn-sm">Kredi Başvurusu</a>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </div>
</section>
<?php include __DIR__ . '/partials/footer.php'; ?>
