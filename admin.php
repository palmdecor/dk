<?php
require __DIR__ . '/includes/bootstrap.php';
require_admin();

$view = $_GET['view'] ?? 'applications';
$allowedViews = ['applications', 'pages', 'posts', 'comments', 'products', 'orders', 'reports', 'settings'];
if (!in_array($view, $allowedViews, true)) {
    $view = 'applications';
}

$meta = seo_meta_tags($translations, 'meta.admin.title', 'meta.admin.description');

function handle_admin_post(PDO $pdo, array $translations): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }

    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'application:status':
            $applicationId = filter_var($_POST['application_id'] ?? null, FILTER_VALIDATE_INT);
            $status = $_POST['status'] ?? '';
            $allowed = ['approve' => 'approved', 'reject' => 'rejected'];
            if ($applicationId && isset($allowed[$status])) {
                $stmt = $pdo->prepare('UPDATE applications SET status = :status, updated_at = NOW() WHERE id = :id');
                $stmt->execute(['status' => $allowed[$status], 'id' => $applicationId]);
                redirect_with_message(site_url('admin?view=applications'), 'success', __t('form.success', $translations));
            }
            break;

        case 'page:create':
        case 'page:update':
            $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
            $title = trim($_POST['title'] ?? '');
            $slugInput = trim($_POST['slug'] ?? '');
            $content = trim($_POST['content'] ?? '');
            $status = in_array($_POST['status'] ?? 'draft', ['draft', 'published'], true) ? $_POST['status'] : 'draft';
            $metaTitle = trim($_POST['meta_title'] ?? '');
            $metaDescription = trim($_POST['meta_description'] ?? '');
            if ($title === '' || $content === '') {
                redirect_with_message(site_url('admin?view=pages' . ($id ? '&edit=' . $id : '')), 'danger', __t('form.validation_error', $translations));
            }
            $slugBase = $slugInput !== '' ? slugify($slugInput) : slugify($title);
            $slug = ensure_unique_slug($pdo, 'pages', $slugBase, $id ?: null);
            if ($action === 'page:create') {
                $stmt = $pdo->prepare('INSERT INTO pages (title, slug, content, status, meta_title, meta_description, created_at) VALUES (:title, :slug, :content, :status, :meta_title, :meta_description, NOW())');
                $stmt->execute([
                    'title' => $title,
                    'slug' => $slug,
                    'content' => $content,
                    'status' => $status,
                    'meta_title' => $metaTitle !== '' ? $metaTitle : null,
                    'meta_description' => $metaDescription !== '' ? $metaDescription : null,
                ]);
            } else {
                if (!$id) {
                    redirect_with_message(site_url('admin?view=pages'), 'danger', __t('form.error', $translations));
                }
                $stmt = $pdo->prepare('UPDATE pages SET title = :title, slug = :slug, content = :content, status = :status, meta_title = :meta_title, meta_description = :meta_description, updated_at = NOW() WHERE id = :id');
                $stmt->execute([
                    'title' => $title,
                    'slug' => $slug,
                    'content' => $content,
                    'status' => $status,
                    'meta_title' => $metaTitle !== '' ? $metaTitle : null,
                    'meta_description' => $metaDescription !== '' ? $metaDescription : null,
                    'id' => $id,
                ]);
            }
            redirect_with_message(site_url('admin?view=pages'), 'success', __t('pages.updated', $translations));
            break;

        case 'page:delete':
            $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
            if ($id) {
                $stmt = $pdo->prepare('DELETE FROM pages WHERE id = :id');
                $stmt->execute(['id' => $id]);
            }
            redirect_with_message(site_url('admin?view=pages'), 'success', __t('pages.updated', $translations));
            break;

        case 'post:create':
        case 'post:update':
            $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
            $title = trim($_POST['title'] ?? '');
            $slugInput = trim($_POST['slug'] ?? '');
            $excerpt = trim($_POST['excerpt'] ?? '');
            $body = trim($_POST['body'] ?? '');
            $status = in_array($_POST['status'] ?? 'draft', ['draft', 'published'], true) ? $_POST['status'] : 'draft';
            $metaTitle = trim($_POST['meta_title'] ?? '');
            $metaDescription = trim($_POST['meta_description'] ?? '');
            if ($title === '' || $body === '') {
                redirect_with_message(site_url('admin?view=posts' . ($id ? '&edit=' . $id : '')), 'danger', __t('form.validation_error', $translations));
            }
            $slugBase = $slugInput !== '' ? slugify($slugInput) : slugify($title);
            $slug = ensure_unique_slug($pdo, 'posts', $slugBase, $id ?: null);
            $publishedAt = $status === 'published' ? date('Y-m-d H:i:s') : null;
            $imagePath = null;
            if ($action === 'post:update') {
                if (!$id) {
                    redirect_with_message(site_url('admin?view=posts'), 'danger', __t('form.error', $translations));
                }
                $stmt = $pdo->prepare('SELECT image_path FROM posts WHERE id = :id');
                $stmt->execute(['id' => $id]);
                $existing = $stmt->fetch();
                $imagePath = $existing['image_path'] ?? null;
            }

            if (isset($_FILES['image']) && !empty($_FILES['image']['name']) && is_uploaded_file($_FILES['image']['tmp_name'])) {
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                $extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                if (!in_array($extension, $allowedExtensions, true)) {
                    redirect_with_message(site_url('admin?view=posts' . ($id ? '&edit=' . $id : '')), 'danger', __t('admin.post.image_invalid', $translations));
                }
                $uploadDir = __DIR__ . '/storage/uploads';
                if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
                    redirect_with_message(site_url('admin?view=posts' . ($id ? '&edit=' . $id : '')), 'danger', __t('admin.post.image_failed', $translations));
                }
                $fileName = uniqid('post_', true) . '.' . $extension;
                $destination = $uploadDir . '/' . $fileName;
                if (!move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                    redirect_with_message(site_url('admin?view=posts' . ($id ? '&edit=' . $id : '')), 'danger', __t('admin.post.image_failed', $translations));
                }
                $imagePath = 'storage/uploads/' . $fileName;
            }

            if ($action === 'post:create') {
                $stmt = $pdo->prepare('INSERT INTO posts (title, slug, excerpt, body, status, published_at, image_path, meta_title, meta_description, created_at) VALUES (:title, :slug, :excerpt, :body, :status, :published_at, :image_path, :meta_title, :meta_description, NOW())');
                $stmt->execute([
                    'title' => $title,
                    'slug' => $slug,
                    'excerpt' => $excerpt,
                    'body' => $body,
                    'status' => $status,
                    'published_at' => $publishedAt,
                    'image_path' => $imagePath,
                    'meta_title' => $metaTitle !== '' ? $metaTitle : null,
                    'meta_description' => $metaDescription !== '' ? $metaDescription : null,
                ]);
            } else {
                if (!$id) {
                redirect_with_message(site_url('admin?view=posts'), 'danger', __t('form.error', $translations));
                }
                $stmt = $pdo->prepare('UPDATE posts SET title = :title, slug = :slug, excerpt = :excerpt, body = :body, status = :status, published_at = :published_at, image_path = :image_path, meta_title = :meta_title, meta_description = :meta_description, updated_at = NOW() WHERE id = :id');
                $stmt->execute([
                    'title' => $title,
                    'slug' => $slug,
                    'excerpt' => $excerpt,
                    'body' => $body,
                    'status' => $status,
                    'published_at' => $publishedAt,
                    'image_path' => $imagePath,
                    'meta_title' => $metaTitle !== '' ? $metaTitle : null,
                    'meta_description' => $metaDescription !== '' ? $metaDescription : null,
                    'id' => $id,
                ]);
            }
            redirect_with_message(site_url('admin?view=posts'), 'success', __t('posts.updated', $translations));
            break;

        case 'post:delete':
            $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
            if ($id) {
                $stmt = $pdo->prepare('DELETE FROM posts WHERE id = :id');
                $stmt->execute(['id' => $id]);
            }
            redirect_with_message(site_url('admin?view=posts'), 'success', __t('posts.updated', $translations));
            break;

        case 'comment:status':
            $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
            $status = $_POST['status'] ?? 'pending';
            if ($id && in_array($status, ['pending', 'approved', 'rejected'], true)) {
                $stmt = $pdo->prepare('UPDATE comments SET status = :status WHERE id = :id');
                $stmt->execute(['status' => $status, 'id' => $id]);
            }
            redirect_with_message(site_url('admin?view=comments'), 'success', __t('comments.updated', $translations));
            break;

        case 'comment:delete':
            $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
            if ($id) {
                $stmt = $pdo->prepare('DELETE FROM comments WHERE id = :id');
                $stmt->execute(['id' => $id]);
            }
            redirect_with_message(site_url('admin?view=comments'), 'success', __t('comments.updated', $translations));
            break;

        case 'settings:update':
            $sanitize = static function ($value): string {
                return trim(strip_tags((string) $value));
            };

            $interestInput = str_replace(',', '.', $_POST['interest_rate'] ?? '');
            $interestRate = filter_var($interestInput, FILTER_VALIDATE_FLOAT);
            if ($interestRate !== false && $interestRate >= 0) {
                set_setting($pdo, 'interest_rate', number_format($interestRate, 6, '.', ''));
            }

            $feeInput = str_replace(',', '.', $_POST['disbursement_fee_rate'] ?? '');
            $feeRate = filter_var($feeInput, FILTER_VALIDATE_FLOAT);
            if ($feeRate !== false && $feeRate >= 0) {
                set_setting($pdo, 'disbursement_fee_rate', number_format($feeRate, 6, '.', ''));
            }

            $homepagePayload = [];
            $metaTitle = $sanitize($_POST['meta_title'] ?? '');
            $metaDescription = $sanitize($_POST['meta_description'] ?? '');
            if ($metaTitle !== '') {
                $homepagePayload['meta_title'] = $metaTitle;
            }
            if ($metaDescription !== '') {
                $homepagePayload['meta_description'] = $metaDescription;
            }
            $homepagePayload['hero_title'] = $sanitize($_POST['hero_title'] ?? '');
            $homepagePayload['hero_subtitle'] = $sanitize($_POST['hero_subtitle'] ?? '');

            $advantagesPayload = [];
            $advantagesInput = $_POST['advantages'] ?? [];
            for ($i = 0; $i < 3; $i++) {
                $title = $sanitize($advantagesInput[$i]['title'] ?? '');
                $description = $sanitize($advantagesInput[$i]['description'] ?? '');
                $advantagesPayload[] = ['title' => $title, 'description' => $description];
            }
            $homepagePayload['advantages'] = $advantagesPayload;

            $testimonialsPayload = [];
            $testimonialsInput = $_POST['testimonials'] ?? [];
            for ($i = 0; $i < 3; $i++) {
                $name = $sanitize($testimonialsInput[$i]['name'] ?? '');
                $text = $sanitize($testimonialsInput[$i]['text'] ?? '');
                $testimonialsPayload[] = ['name' => $name, 'text' => $text];
            }
            $homepagePayload['testimonials'] = $testimonialsPayload;

            save_homepage_content($pdo, $homepagePayload);

            $termsContent = sanitize_policy_html($_POST['terms_content'] ?? '');
            $kvkkContent = sanitize_policy_html($_POST['kvkk_content'] ?? '');
            save_policy_content($pdo, 'terms_content', $termsContent);
            save_policy_content($pdo, 'kvkk_content', $kvkkContent);

            $aiTopics = trim($_POST['ai_blog_topics'] ?? '');
            set_setting($pdo, 'ai_blog_topics', $aiTopics);

            redirect_with_message(site_url('admin?view=settings'), 'success', __t('admin.settings.saved', $translations));
            break;

        case 'product:create':
        case 'product:update':
            $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
            $name = trim($_POST['name'] ?? '');
            $slugInput = trim($_POST['slug'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $price = (float) ($_POST['price'] ?? 0);
            $stock = (int) ($_POST['stock'] ?? 0);
            $status = in_array($_POST['status'] ?? 'draft', ['draft', 'published'], true) ? $_POST['status'] : 'draft';
            if ($name === '' || $price <= 0) {
                redirect_with_message(site_url('admin?view=products' . ($id ? '&edit=' . $id : '')), 'danger', __t('form.validation_error', $translations));
            }
            $slugBase = $slugInput !== '' ? slugify($slugInput) : slugify($name);
            $slug = ensure_unique_slug($pdo, 'products', $slugBase, $id ?: null);
            if ($action === 'product:create') {
                $stmt = $pdo->prepare('INSERT INTO products (name, slug, description, price, stock, status, created_at) VALUES (:name, :slug, :description, :price, :stock, :status, NOW())');
                $stmt->execute(['name' => $name, 'slug' => $slug, 'description' => $description, 'price' => $price, 'stock' => $stock, 'status' => $status]);
            } else {
                if (!$id) {
                    redirect_with_message(site_url('admin?view=products'), 'danger', __t('form.error', $translations));
                }
                $stmt = $pdo->prepare('UPDATE products SET name = :name, slug = :slug, description = :description, price = :price, stock = :stock, status = :status, updated_at = NOW() WHERE id = :id');
                $stmt->execute(['name' => $name, 'slug' => $slug, 'description' => $description, 'price' => $price, 'stock' => $stock, 'status' => $status, 'id' => $id]);
            }
            redirect_with_message(site_url('admin?view=products'), 'success', __t('products.updated', $translations));
            break;

        case 'product:delete':
            $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
            if ($id) {
                $stmt = $pdo->prepare('DELETE FROM products WHERE id = :id');
                $stmt->execute(['id' => $id]);
            }
            redirect_with_message(site_url('admin?view=products'), 'success', __t('products.updated', $translations));
            break;

        case 'order:update':
            $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
            $status = $_POST['payment_status'] ?? 'pending';
            $reference = trim($_POST['payment_reference'] ?? '');
            if ($id && in_array($status, ['pending', 'processing', 'success', 'failed'], true)) {
                $stmt = $pdo->prepare('UPDATE orders SET payment_status = :status, payment_reference = :reference, updated_at = NOW() WHERE id = :id');
                $stmt->execute(['status' => $status, 'reference' => $reference !== '' ? $reference : null, 'id' => $id]);
                $eventStmt = $pdo->prepare('INSERT INTO order_events (order_id, event, payload, created_at) VALUES (:order_id, :event, :payload, NOW())');
                $eventStmt->execute([
                    'order_id' => $id,
                    'event' => 'status_changed',
                    'payload' => json_encode(['status' => $status, 'reference' => $reference]),
                ]);
            }
            redirect_with_message(site_url('admin?view=orders'), 'success', __t('orders.updated', $translations));
            break;
    }

    redirect_with_message(site_url('admin'), 'danger', __t('form.error', $translations));
}

handle_admin_post($pdo, $translations);

$flash = get_flash_messages();

$pageToEdit = null;
$postToEdit = null;
$productToEdit = null;

if ($view === 'pages' && isset($_GET['edit'])) {
    $id = filter_var($_GET['edit'], FILTER_VALIDATE_INT);
    if ($id) {
        $stmt = $pdo->prepare('SELECT * FROM pages WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $pageToEdit = $stmt->fetch();
    }
}

if ($view === 'posts' && isset($_GET['edit'])) {
    $id = filter_var($_GET['edit'], FILTER_VALIDATE_INT);
    if ($id) {
        $stmt = $pdo->prepare('SELECT * FROM posts WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $postToEdit = $stmt->fetch();
    }
}

if ($view === 'products' && isset($_GET['edit'])) {
    $id = filter_var($_GET['edit'], FILTER_VALIDATE_INT);
    if ($id) {
        $stmt = $pdo->prepare('SELECT * FROM products WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $productToEdit = $stmt->fetch();
    }
}

$applications = [];
$pages = [];
$posts = [];
$comments = [];
$products = [];
$orders = [];
$orderEvents = [];
$reportData = [];
$homepageContent = get_homepage_content($pdo, $translations);
$currentInterestRate = get_interest_rate($pdo, (float) $config['app']['interest_rate']);
$currentFeeRate = get_disbursement_rate($pdo);
$termsSetting = get_policy_content($pdo, 'terms_content', default_terms_content());
$kvkkSetting = get_policy_content($pdo, 'kvkk_content', default_kvkk_content());
$aiBlogTopics = get_setting($pdo, 'ai_blog_topics', 'kredi notu, finansal planlama, faiz oranları');

if ($view === 'applications') {
    $stmt = $pdo->query('SELECT applications.*, users.name, users.email FROM applications JOIN users ON users.id = applications.user_id ORDER BY applications.created_at DESC');
    $applications = $stmt->fetchAll();
} elseif ($view === 'pages') {
    $stmt = $pdo->query('SELECT * FROM pages ORDER BY created_at DESC');
    $pages = $stmt->fetchAll();
} elseif ($view === 'posts') {
    $stmt = $pdo->query('SELECT * FROM posts ORDER BY created_at DESC');
    $posts = $stmt->fetchAll();
} elseif ($view === 'comments') {
    $stmt = $pdo->query('SELECT comments.*, posts.title as post_title FROM comments JOIN posts ON posts.id = comments.post_id ORDER BY comments.created_at DESC');
    $comments = $stmt->fetchAll();
} elseif ($view === 'products') {
    $stmt = $pdo->query('SELECT * FROM products ORDER BY created_at DESC');
    $products = $stmt->fetchAll();
} elseif ($view === 'orders') {
    $stmt = $pdo->query('SELECT orders.*, users.name as user_name, users.email as user_email, products.name as product_name FROM orders JOIN products ON products.id = orders.product_id LEFT JOIN users ON users.id = orders.user_id ORDER BY orders.created_at DESC');
    $orders = $stmt->fetchAll();
    $eventStmt = $pdo->prepare('SELECT * FROM order_events WHERE order_id = :order_id ORDER BY created_at DESC');
    foreach ($orders as $order) {
        $eventStmt->execute(['order_id' => $order['id']]);
        $orderEvents[$order['id']] = $eventStmt->fetchAll();
    }
} elseif ($view === 'reports') {
    $totalApplications = (int) $pdo->query('SELECT COUNT(*) FROM applications')->fetchColumn();
    $monthlyApplications = $pdo->query("SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, COUNT(*) AS total FROM applications GROUP BY month ORDER BY month")->fetchAll();
    $productsSold = (int) $pdo->query("SELECT IFNULL(SUM(quantity), 0) FROM orders WHERE payment_status IN ('processing','success')")->fetchColumn();
    $totalRevenue = (float) $pdo->query("SELECT IFNULL(SUM(total_amount), 0) FROM orders WHERE payment_status = 'success'")->fetchColumn();
    $ordersByMonth = $pdo->query("SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, SUM(total_amount) AS total FROM orders WHERE payment_status = 'success' GROUP BY month ORDER BY month")->fetchAll();

    $reportData = [
        'totalApplications' => $totalApplications,
        'monthlyApplications' => $monthlyApplications,
        'productsSold' => $productsSold,
        'totalRevenue' => $totalRevenue,
        'ordersByMonth' => $ordersByMonth,
    ];
}

include __DIR__ . '/partials/header.php';
include __DIR__ . '/partials/flash.php';
?>
<section class="py-5">
    <div class="container">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center mb-4">
            <div>
                <h2 class="fw-bold mb-1"><?= __t('admin.title', $translations) ?></h2>
                <p class="text-muted mb-0"><?= __t('admin.subtitle', $translations) ?></p>
            </div>
            <div class="mt-3 mt-lg-0">
                <a href="<?= site_url() ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> <?= __t('nav.home', $translations) ?></a>
            </div>
        </div>
        <ul class="nav nav-pills mb-4 flex-wrap gap-2">
            <li class="nav-item"><a class="nav-link <?= $view === 'applications' ? 'active' : '' ?>" href="<?= site_url('admin?view=applications') ?>"><?= __t('admin.tab.applications', $translations) ?></a></li>
            <li class="nav-item"><a class="nav-link <?= $view === 'pages' ? 'active' : '' ?>" href="<?= site_url('admin?view=pages') ?>"><?= __t('admin.tab.pages', $translations) ?></a></li>
            <li class="nav-item"><a class="nav-link <?= $view === 'posts' ? 'active' : '' ?>" href="<?= site_url('admin?view=posts') ?>"><?= __t('admin.tab.posts', $translations) ?></a></li>
            <li class="nav-item"><a class="nav-link <?= $view === 'comments' ? 'active' : '' ?>" href="<?= site_url('admin?view=comments') ?>"><?= __t('admin.tab.comments', $translations) ?></a></li>
            <li class="nav-item"><a class="nav-link <?= $view === 'products' ? 'active' : '' ?>" href="<?= site_url('admin?view=products') ?>"><?= __t('admin.tab.products', $translations) ?></a></li>
            <li class="nav-item"><a class="nav-link <?= $view === 'orders' ? 'active' : '' ?>" href="<?= site_url('admin?view=orders') ?>"><?= __t('admin.tab.orders', $translations) ?></a></li>
            <li class="nav-item"><a class="nav-link <?= $view === 'reports' ? 'active' : '' ?>" href="<?= site_url('admin?view=reports') ?>"><?= __t('admin.tab.reports', $translations) ?></a></li>
            <li class="nav-item"><a class="nav-link <?= $view === 'settings' ? 'active' : '' ?>" href="<?= site_url('admin?view=settings') ?>"><?= __t('admin.tab.settings', $translations) ?></a></li>
        </ul>

        <?php if ($view === 'applications'): ?>
            <div class="table-responsive shadow-sm">
                <table class="table table-hover bg-white mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th><?= __t('admin.applicant', $translations) ?></th>
                            <th><?= __t('apply.loan_amount', $translations) ?></th>
                            <th><?= __t('apply.loan_term', $translations) ?></th>
                            <th><?= __t('admin.status', $translations) ?></th>
                            <th><?= __t('admin.created_at', $translations) ?></th>
                            <th><?= __t('admin.actions', $translations) ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($applications as $application): ?>
                            <tr>
                                <td>#<?= htmlspecialchars($application['id']) ?></td>
                                <td>
                                    <div class="fw-semibold"><?= htmlspecialchars($application['name']) ?></div>
                                    <div class="text-muted small"><?= htmlspecialchars($application['email']) ?></div>
                                </td>
                                <td><?= format_price((float) $application['loan_amount']) ?></td>
                                <td><?= htmlspecialchars($application['loan_term']) ?></td>
                                <td><span class="badge badge-status <?= htmlspecialchars($application['status']) ?>"><?= __t('status.' . $application['status'], $translations) ?></span></td>
                                <td><?= date('d.m.Y H:i', strtotime($application['created_at'])) ?></td>
                                <td>
                                    <form method="post" class="d-flex gap-2">
                                        <input type="hidden" name="action" value="application:status">
                                        <input type="hidden" name="application_id" value="<?= (int) $application['id'] ?>">
                                        <button type="submit" name="status" value="approve" class="btn btn-sm btn-success" <?= $application['status'] === 'approved' ? 'disabled' : '' ?>><?= __t('admin.approve', $translations) ?></button>
                                        <button type="submit" name="status" value="reject" class="btn btn-sm btn-danger" <?= $application['status'] === 'rejected' ? 'disabled' : '' ?>><?= __t('admin.reject', $translations) ?></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($applications)): ?>
                            <tr><td colspan="7" class="text-center text-muted py-4"><?= __t('admin.empty', $translations) ?></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php elseif ($view === 'pages'): ?>
            <div class="row g-4">
                <div class="col-lg-5">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <h5 class="card-title mb-3"><?= $pageToEdit ? __t('admin.page.update', $translations) : __t('admin.page.create', $translations) ?></h5>
                            <form method="post">
                                <input type="hidden" name="action" value="<?= $pageToEdit ? 'page:update' : 'page:create' ?>">
                                <?php if ($pageToEdit): ?><input type="hidden" name="id" value="<?= (int) $pageToEdit['id'] ?>"><?php endif; ?>
                                <div class="mb-3">
                                    <label class="form-label"><?= __t('admin.page.title', $translations) ?></label>
                                    <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($pageToEdit['title'] ?? '') ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label"><?= __t('admin.page.slug', $translations) ?></label>
                                    <input type="text" name="slug" class="form-control" value="<?= htmlspecialchars($pageToEdit['slug'] ?? '') ?>" placeholder="hakkimizda">
                                    <div class="form-text"><?= __t('admin.slug.auto', $translations) ?></div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label"><?= __t('admin.meta.title', $translations) ?></label>
                                    <input type="text" name="meta_title" class="form-control" value="<?= htmlspecialchars($pageToEdit['meta_title'] ?? '') ?>" placeholder="<?= htmlspecialchars(__t('admin.meta.title_placeholder', $translations)) ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label"><?= __t('admin.meta.description', $translations) ?></label>
                                    <textarea name="meta_description" rows="2" class="form-control" placeholder="<?= htmlspecialchars(__t('admin.meta.description_placeholder', $translations)) ?>"><?= htmlspecialchars($pageToEdit['meta_description'] ?? '') ?></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label"><?= __t('admin.page.content', $translations) ?></label>
                                    <textarea name="content" rows="6" class="form-control" required><?= htmlspecialchars($pageToEdit['content'] ?? '') ?></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label"><?= __t('admin.page.status', $translations) ?></label>
                                    <select name="status" class="form-select">
                                        <option value="draft" <?= (isset($pageToEdit['status']) && $pageToEdit['status'] === 'draft') ? 'selected' : '' ?>><?= __t('status.draft', $translations) ?></option>
                                        <option value="published" <?= (isset($pageToEdit['status']) && $pageToEdit['status'] === 'published') ? 'selected' : '' ?>><?= __t('status.published', $translations) ?></option>
                                    </select>
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary"><?= __t('admin.save', $translations) ?></button>
                                    <?php if ($pageToEdit): ?><a href="<?= site_url('admin?view=pages') ?>" class="btn btn-outline-secondary"><?= __t('admin.reset', $translations) ?></a><?php endif; ?>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <h5 class="card-title mb-3"><?= __t('admin.tab.pages', $translations) ?></h5>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th><?= __t('admin.page.title', $translations) ?></th>
                                            <th><?= __t('admin.page.status', $translations) ?></th>
                                            <th><?= __t('admin.created_at', $translations) ?></th>
                                            <th><?= __t('admin.actions', $translations) ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($pages as $page): ?>
                                            <tr>
                                                <td>#<?= (int) $page['id'] ?></td>
                                                <td><?= htmlspecialchars($page['title']) ?></td>
                                                <td><span class="badge bg-<?= $page['status'] === 'published' ? 'success' : 'secondary' ?>"><?= __t('status.' . $page['status'], $translations) ?></span></td>
                                                <td><?= date('d.m.Y H:i', strtotime($page['created_at'])) ?></td>
                                                <td class="d-flex gap-2">
                                                    <a href="<?= site_url('admin?view=pages&edit=' . (int) $page['id']) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                                    <form method="post" onsubmit="return confirm('<?= __t('form.delete_confirm', $translations) ?>');">
                                                        <input type="hidden" name="action" value="page:delete">
                                                        <input type="hidden" name="id" value="<?= (int) $page['id'] ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($pages)): ?>
                                            <tr><td colspan="5" class="text-center text-muted py-4"><?= __t('admin.empty', $translations) ?></td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php elseif ($view === 'posts'): ?>
            <div class="row g-4">
                <div class="col-lg-5">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <h5 class="card-title mb-3"><?= $postToEdit ? __t('admin.post.update', $translations) : __t('admin.post.create', $translations) ?></h5>
                            <form method="post" enctype="multipart/form-data">
                                <input type="hidden" name="action" value="<?= $postToEdit ? 'post:update' : 'post:create' ?>">
                                <?php if ($postToEdit): ?><input type="hidden" name="id" value="<?= (int) $postToEdit['id'] ?>"><?php endif; ?>
                                <div class="mb-3">
                                    <label class="form-label"><?= __t('admin.post.title', $translations) ?></label>
                                    <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($postToEdit['title'] ?? '') ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Slug</label>
                                    <input type="text" name="slug" class="form-control" value="<?= htmlspecialchars($postToEdit['slug'] ?? '') ?>" placeholder="kredi-rehberi">
                                    <div class="form-text"><?= __t('admin.slug.auto', $translations) ?></div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label"><?= __t('admin.post.excerpt', $translations) ?></label>
                                    <textarea name="excerpt" class="form-control" rows="3"><?= htmlspecialchars($postToEdit['excerpt'] ?? '') ?></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label"><?= __t('admin.post.image', $translations) ?></label>
                                    <?php if (!empty($postToEdit['image_path'] ?? '')): ?>
                                        <div class="mb-2">
                                            <img src="<?= htmlspecialchars($postToEdit['image_path']) ?>" alt="<?= htmlspecialchars($postToEdit['title'] ?? '') ?>" class="img-fluid rounded shadow-sm">
                                        </div>
                                    <?php endif; ?>
                                    <input type="file" name="image" class="form-control" accept="image/*">
                                    <div class="form-text"><?= __t('admin.post.image_help', $translations) ?></div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label"><?= __t('admin.meta.title', $translations) ?></label>
                                    <input type="text" name="meta_title" class="form-control" value="<?= htmlspecialchars($postToEdit['meta_title'] ?? '') ?>" placeholder="<?= htmlspecialchars(__t('admin.meta.title_placeholder', $translations)) ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label"><?= __t('admin.meta.description', $translations) ?></label>
                                    <textarea name="meta_description" class="form-control" rows="2" placeholder="<?= htmlspecialchars(__t('admin.meta.description_placeholder', $translations)) ?>"><?= htmlspecialchars($postToEdit['meta_description'] ?? '') ?></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label"><?= __t('admin.post.body', $translations) ?></label>
                                    <textarea name="body" class="form-control" rows="6" required><?= htmlspecialchars($postToEdit['body'] ?? '') ?></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label"><?= __t('admin.post.status', $translations) ?></label>
                                    <select name="status" class="form-select">
                                        <option value="draft" <?= (isset($postToEdit['status']) && $postToEdit['status'] === 'draft') ? 'selected' : '' ?>><?= __t('status.draft', $translations) ?></option>
                                        <option value="published" <?= (isset($postToEdit['status']) && $postToEdit['status'] === 'published') ? 'selected' : '' ?>><?= __t('status.published', $translations) ?></option>
                                    </select>
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary"><?= __t('admin.save', $translations) ?></button>
                                    <?php if ($postToEdit): ?><a href="<?= site_url('admin?view=posts') ?>" class="btn btn-outline-secondary"><?= __t('admin.reset', $translations) ?></a><?php endif; ?>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <h5 class="card-title mb-3"><?= __t('admin.tab.posts', $translations) ?></h5>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th><?= __t('admin.post.title', $translations) ?></th>
                                            <th><?= __t('admin.post.status', $translations) ?></th>
                                            <th><?= __t('admin.created_at', $translations) ?></th>
                                            <th><?= __t('admin.actions', $translations) ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($posts as $post): ?>
                                            <tr>
                                                <td>#<?= (int) $post['id'] ?></td>
                                                <td><?= htmlspecialchars($post['title']) ?></td>
                                                <td><span class="badge bg-<?= $post['status'] === 'published' ? 'success' : 'secondary' ?>"><?= __t('status.' . $post['status'], $translations) ?></span></td>
                                                <td><?= date('d.m.Y H:i', strtotime($post['created_at'])) ?></td>
                                                <td class="d-flex gap-2">
                                                    <a href="<?= site_url('admin?view=posts&edit=' . (int) $post['id']) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                                    <form method="post" onsubmit="return confirm('<?= __t('form.delete_confirm', $translations) ?>');">
                                                        <input type="hidden" name="action" value="post:delete">
                                                        <input type="hidden" name="id" value="<?= (int) $post['id'] ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($posts)): ?>
                                            <tr><td colspan="5" class="text-center text-muted py-4"><?= __t('admin.empty', $translations) ?></td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php elseif ($view === 'comments'): ?>
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5 class="card-title mb-3"><?= __t('admin.tab.comments', $translations) ?></h5>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th><?= __t('admin.comment.author', $translations) ?></th>
                                    <th><?= __t('admin.comment.body', $translations) ?></th>
                                    <th><?= __t('nav.blog', $translations) ?></th>
                                    <th><?= __t('admin.status', $translations) ?></th>
                                    <th><?= __t('admin.actions', $translations) ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($comments as $comment): ?>
                                    <tr>
                                        <td>#<?= (int) $comment['id'] ?></td>
                                        <td>
                                            <div class="fw-semibold"><?= htmlspecialchars($comment['author_name']) ?></div>
                                            <div class="text-muted small"><?= htmlspecialchars($comment['author_email']) ?></div>
                                        </td>
                                        <td><?= nl2br(htmlspecialchars($comment['body'])) ?></td>
                                        <td><?= htmlspecialchars($comment['post_title']) ?></td>
                                        <td><span class="badge bg-<?= $comment['status'] === 'approved' ? 'success' : ($comment['status'] === 'rejected' ? 'danger' : 'secondary') ?>"><?= __t('status.' . $comment['status'], $translations) ?></span></td>
                                        <td class="d-flex gap-2">
                                            <form method="post">
                                                <input type="hidden" name="action" value="comment:status">
                                                <input type="hidden" name="id" value="<?= (int) $comment['id'] ?>">
                                                <button type="submit" name="status" value="approved" class="btn btn-sm btn-outline-success" <?= $comment['status'] === 'approved' ? 'disabled' : '' ?>><?= __t('admin.comment.approve', $translations) ?></button>
                                                <button type="submit" name="status" value="rejected" class="btn btn-sm btn-outline-danger" <?= $comment['status'] === 'rejected' ? 'disabled' : '' ?>><?= __t('admin.comment.reject', $translations) ?></button>
                                            </form>
                                            <form method="post" onsubmit="return confirm('<?= __t('form.delete_confirm', $translations) ?>');">
                                                <input type="hidden" name="action" value="comment:delete">
                                                <input type="hidden" name="id" value="<?= (int) $comment['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($comments)): ?>
                                    <tr><td colspan="6" class="text-center text-muted py-4"><?= __t('admin.empty', $translations) ?></td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php elseif ($view === 'products'): ?>
            <div class="row g-4">
                <div class="col-lg-5">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <h5 class="card-title mb-3"><?= $productToEdit ? __t('admin.product.update', $translations) : __t('admin.product.create', $translations) ?></h5>
                            <form method="post">
                                <input type="hidden" name="action" value="<?= $productToEdit ? 'product:update' : 'product:create' ?>">
                                <?php if ($productToEdit): ?><input type="hidden" name="id" value="<?= (int) $productToEdit['id'] ?>"><?php endif; ?>
                                <div class="mb-3">
                                    <label class="form-label"><?= __t('admin.product.name', $translations) ?></label>
                                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($productToEdit['name'] ?? '') ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Slug</label>
                                    <input type="text" name="slug" class="form-control" value="<?= htmlspecialchars($productToEdit['slug'] ?? '') ?>" placeholder="findeks-raporu">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label"><?= __t('admin.product.description', $translations) ?></label>
                                    <textarea name="description" rows="4" class="form-control" required><?= htmlspecialchars($productToEdit['description'] ?? '') ?></textarea>
                                </div>
                                <div class="row g-3">
                                    <div class="col">
                                        <label class="form-label"><?= __t('admin.product.price', $translations) ?></label>
                                        <input type="number" step="0.01" name="price" class="form-control" value="<?= htmlspecialchars($productToEdit['price'] ?? '') ?>" required>
                                    </div>
                                    <div class="col">
                                        <label class="form-label"><?= __t('admin.product.stock', $translations) ?></label>
                                        <input type="number" name="stock" class="form-control" value="<?= htmlspecialchars($productToEdit['stock'] ?? '') ?>" required>
                                    </div>
                                </div>
                                <div class="mb-3 mt-3">
                                    <label class="form-label"><?= __t('admin.product.status', $translations) ?></label>
                                    <select name="status" class="form-select">
                                        <option value="draft" <?= (isset($productToEdit['status']) && $productToEdit['status'] === 'draft') ? 'selected' : '' ?>><?= __t('status.draft', $translations) ?></option>
                                        <option value="published" <?= (isset($productToEdit['status']) && $productToEdit['status'] === 'published') ? 'selected' : '' ?>><?= __t('status.published', $translations) ?></option>
                                    </select>
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary"><?= __t('admin.save', $translations) ?></button>
                                    <?php if ($productToEdit): ?><a href="<?= site_url('admin?view=products') ?>" class="btn btn-outline-secondary"><?= __t('admin.reset', $translations) ?></a><?php endif; ?>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <h5 class="card-title mb-3"><?= __t('admin.tab.products', $translations) ?></h5>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th><?= __t('admin.product.name', $translations) ?></th>
                                            <th><?= __t('admin.product.price', $translations) ?></th>
                                            <th><?= __t('admin.product.stock', $translations) ?></th>
                                            <th><?= __t('admin.product.status', $translations) ?></th>
                                            <th><?= __t('admin.actions', $translations) ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($products as $product): ?>
                                            <tr>
                                                <td>#<?= (int) $product['id'] ?></td>
                                                <td><?= htmlspecialchars($product['name']) ?></td>
                                                <td><?= format_price((float) $product['price']) ?></td>
                                                <td><?= (int) $product['stock'] ?></td>
                                                <td><span class="badge bg-<?= $product['status'] === 'published' ? 'success' : 'secondary' ?>"><?= __t('status.' . $product['status'], $translations) ?></span></td>
                                                <td class="d-flex gap-2">
                                                    <a href="<?= site_url('admin?view=products&edit=' . (int) $product['id']) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                                    <form method="post" onsubmit="return confirm('<?= __t('form.delete_confirm', $translations) ?>');">
                                                        <input type="hidden" name="action" value="product:delete">
                                                        <input type="hidden" name="id" value="<?= (int) $product['id'] ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($products)): ?>
                                            <tr><td colspan="6" class="text-center text-muted py-4"><?= __t('admin.empty', $translations) ?></td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php elseif ($view === 'orders'): ?>
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5 class="card-title mb-3"><?= __t('admin.tab.orders', $translations) ?></h5>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th><?= __t('admin.order.customer', $translations) ?></th>
                                    <th><?= __t('admin.order.product', $translations) ?></th>
                                    <th><?= __t('admin.order.total', $translations) ?></th>
                                    <th><?= __t('admin.order.payment_status', $translations) ?></th>
                                    <th><?= __t('admin.order.provider', $translations) ?></th>
                                    <th><?= __t('admin.order.reference', $translations) ?></th>
                                    <th><?= __t('admin.actions', $translations) ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td>#<?= (int) $order['id'] ?></td>
                                        <td>
                                            <?php if ($order['user_name']): ?>
                                                <div class="fw-semibold"><?= htmlspecialchars($order['user_name']) ?></div>
                                                <div class="text-muted small"><?= htmlspecialchars($order['user_email']) ?></div>
                                            <?php else: ?>
                                                <span class="text-muted d-block mb-1"><?= __t('orders.guest', $translations) ?></span>
                                                <?php if (!empty($order['notes'])): ?>
                                                    <?php $guest = json_decode($order['notes'], true); ?>
                                                    <?php if (is_array($guest)): ?>
                                                        <?php if (!empty($guest['name'])): ?><div class="small fw-semibold"><?= htmlspecialchars($guest['name']) ?></div><?php endif; ?>
                                                        <?php if (!empty($guest['email'])): ?><div class="small text-muted"><?= htmlspecialchars($guest['email']) ?></div><?php endif; ?>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($order['product_name']) ?></td>
                                        <td><?= format_price((float) $order['total_amount']) ?></td>
                                        <td><span class="badge bg-<?= $order['payment_status'] === 'success' ? 'success' : ($order['payment_status'] === 'failed' ? 'danger' : 'warning') ?>"><?= __t('orders.status.' . $order['payment_status'], $translations) ?></span></td>
                                        <td><?= strtoupper(htmlspecialchars($order['payment_provider'])) ?></td>
                                        <td><?= htmlspecialchars($order['payment_reference'] ?? '-') ?></td>
                                        <td>
                                            <form method="post" class="row g-2 align-items-center">
                                                <input type="hidden" name="action" value="order:update">
                                                <input type="hidden" name="id" value="<?= (int) $order['id'] ?>">
                                                <div class="col-12 col-xl-4">
                                                    <select name="payment_status" class="form-select form-select-sm">
                                                        <option value="pending" <?= $order['payment_status'] === 'pending' ? 'selected' : '' ?>><?= __t('orders.status.pending', $translations) ?></option>
                                                        <option value="processing" <?= $order['payment_status'] === 'processing' ? 'selected' : '' ?>><?= __t('orders.status.processing', $translations) ?></option>
                                                        <option value="success" <?= $order['payment_status'] === 'success' ? 'selected' : '' ?>><?= __t('orders.status.success', $translations) ?></option>
                                                        <option value="failed" <?= $order['payment_status'] === 'failed' ? 'selected' : '' ?>><?= __t('orders.status.failed', $translations) ?></option>
                                                    </select>
                                                </div>
                                                <div class="col-12 col-xl-4">
                                                    <input type="text" name="payment_reference" class="form-control form-control-sm" value="<?= htmlspecialchars($order['payment_reference'] ?? '') ?>" placeholder="REF-123456">
                                                </div>
                                                <div class="col-12 col-xl-4">
                                                    <button type="submit" class="btn btn-sm btn-primary w-100"><?= __t('admin.order.update', $translations) ?></button>
                                                </div>
                                            </form>
                                            <?php if (!empty($orderEvents[$order['id']])): ?>
                                                <details class="mt-2">
                                                    <summary class="small text-muted"><?= __t('admin.order.history', $translations) ?></summary>
                                                    <ul class="list-unstyled small mt-2">
                                                        <?php foreach ($orderEvents[$order['id']] as $event): ?>
                                                            <li class="border rounded p-2 mb-2">
                                                                <div class="fw-semibold text-uppercase"><?= htmlspecialchars($event['event']) ?></div>
                                                                <div><?= date('d.m.Y H:i', strtotime($event['created_at'])) ?></div>
                                                                <?php if ($event['payload']): ?>
                                                                    <pre class="bg-light p-2 mt-2 mb-0 small"><?= htmlspecialchars($event['payload']) ?></pre>
                                                                <?php endif; ?>
                                                            </li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                </details>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($orders)): ?>
                                    <tr><td colspan="8" class="text-center text-muted py-4"><?= __t('orders.table.empty', $translations) ?></td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php elseif ($view === 'reports'): ?>
            <div class="row g-4">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="text-muted text-uppercase small mb-2"><?= __t('admin.reports.applications_total', $translations) ?></div>
                            <div class="display-6 fw-bold"><?= $reportData['totalApplications'] ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="text-muted text-uppercase small mb-2"><?= __t('admin.reports.products_sold', $translations) ?></div>
                            <div class="display-6 fw-bold"><?= $reportData['productsSold'] ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="text-muted text-uppercase small mb-2"><?= __t('admin.reports.revenue', $translations) ?></div>
                            <div class="display-6 fw-bold"><?= format_price((float) $reportData['totalRevenue']) ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="text-muted text-uppercase small mb-2"><?= __t('admin.reports.applications_monthly', $translations) ?></div>
                            <div class="display-6 fw-bold"><?= count($reportData['monthlyApplications']) ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card shadow-sm border-0 mt-4">
                <div class="card-body">
                    <h5 class="card-title"><?= __t('admin.reports.orders_chart', $translations) ?></h5>
                    <canvas id="ordersChart" height="120"></canvas>
                </div>
            </div>
            <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const ctx = document.getElementById('ordersChart');
                    if (!ctx) return;
                    const labels = <?= json_encode(array_column($reportData['ordersByMonth'], 'month')) ?>;
                    const values = <?= json_encode(array_map('floatval', array_column($reportData['ordersByMonth'], 'total'))) ?>;
                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels,
                            datasets: [{
                                label: '<?= addslashes(__t('admin.reports.orders_chart', $translations)) ?>',
                                data: values,
                                borderColor: '#0d6efd',
                                backgroundColor: 'rgba(13, 110, 253, 0.2)',
                                tension: 0.3,
                                fill: true,
                            }]
                        },
                        options: {
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });
                });
            </script>
        <?php elseif ($view === 'settings'): ?>
            <?php
            $interestValue = rtrim(rtrim(number_format((float) $currentInterestRate, 6, '.', ''), '0'), '.');
            $feeValue = rtrim(rtrim(number_format((float) $currentFeeRate, 6, '.', ''), '0'), '.');
            ?>
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5 class="card-title mb-2"><?= __t('admin.settings.heading', $translations) ?></h5>
                    <p class="text-muted small mb-4"><?= __t('admin.settings.subheading', $translations) ?></p>
                    <form method="post" class="row g-4">
                        <input type="hidden" name="action" value="settings:update">
                        <div class="col-md-4">
                            <label class="form-label"><?= __t('admin.settings.interest_rate', $translations) ?></label>
                            <div class="input-group">
                                <input type="number" step="0.000001" min="0" name="interest_rate" class="form-control" value="<?= htmlspecialchars($interestValue !== '' ? $interestValue : '0') ?>">
                                <span class="input-group-text">%</span>
                            </div>
                            <div class="form-text"><?= __t('admin.settings.interest_help', $translations) ?></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label"><?= __t('admin.settings.disbursement_fee', $translations) ?></label>
                            <div class="input-group">
                                <input type="number" step="0.000001" min="0" name="disbursement_fee_rate" class="form-control" value="<?= htmlspecialchars($feeValue !== '' ? $feeValue : '0') ?>">
                                <span class="input-group-text">%</span>
                            </div>
                            <div class="form-text"><?= __t('admin.settings.disbursement_help', $translations) ?></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label"><?= __t('admin.meta.title', $translations) ?></label>
                            <input type="text" name="meta_title" class="form-control" value="<?= htmlspecialchars($homepageContent['meta_title'] ?? '') ?>" placeholder="<?= htmlspecialchars(__t('admin.meta.title_placeholder', $translations)) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label"><?= __t('admin.meta.description', $translations) ?></label>
                            <input type="text" name="meta_description" class="form-control" value="<?= htmlspecialchars($homepageContent['meta_description'] ?? '') ?>" placeholder="<?= htmlspecialchars(__t('admin.meta.description_placeholder', $translations)) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><?= __t('admin.settings.hero_title', $translations) ?></label>
                            <input type="text" name="hero_title" class="form-control" value="<?= htmlspecialchars($homepageContent['hero_title'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><?= __t('admin.settings.hero_subtitle', $translations) ?></label>
                            <input type="text" name="hero_subtitle" class="form-control" value="<?= htmlspecialchars($homepageContent['hero_subtitle'] ?? '') ?>">
                        </div>
                        <div class="col-12">
                            <h6 class="text-uppercase text-muted small mb-3"><?= __t('admin.settings.advantages', $translations) ?></h6>
                        </div>
                        <?php foreach ($homepageContent['advantages'] as $index => $advantage): ?>
                            <div class="col-md-4">
                                <div class="border rounded p-3 h-100">
                                    <h6 class="fw-semibold small mb-3 text-primary"><?= sprintf(__t('admin.settings.advantage_label', $translations), $index + 1) ?></h6>
                                    <div class="mb-3">
                                        <label class="form-label"><?= __t('admin.settings.block_title', $translations) ?></label>
                                        <input type="text" class="form-control" name="advantages[<?= $index ?>][title]" value="<?= htmlspecialchars($advantage['title'] ?? '') ?>">
                                    </div>
                                    <div class="mb-0">
                                        <label class="form-label"><?= __t('admin.settings.block_description', $translations) ?></label>
                                        <textarea class="form-control" rows="2" name="advantages[<?= $index ?>][description]"><?= htmlspecialchars($advantage['description'] ?? '') ?></textarea>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <div class="col-12">
                            <h6 class="text-uppercase text-muted small mb-3"><?= __t('admin.settings.testimonials', $translations) ?></h6>
                        </div>
                        <?php foreach ($homepageContent['testimonials'] as $index => $testimonial): ?>
                            <div class="col-md-4">
                                <div class="border rounded p-3 h-100">
                                    <h6 class="fw-semibold small mb-3 text-primary"><?= sprintf(__t('admin.settings.testimonial_label', $translations), $index + 1) ?></h6>
                                    <div class="mb-3">
                                        <label class="form-label"><?= __t('admin.settings.testimonial_name', $translations) ?></label>
                                        <input type="text" class="form-control" name="testimonials[<?= $index ?>][name]" value="<?= htmlspecialchars($testimonial['name'] ?? '') ?>">
                                    </div>
                                    <div class="mb-0">
                                        <label class="form-label"><?= __t('admin.settings.testimonial_text', $translations) ?></label>
                                        <textarea class="form-control" rows="2" name="testimonials[<?= $index ?>][text]"><?= htmlspecialchars($testimonial['text'] ?? '') ?></textarea>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <div class="col-12">
                            <h6 class="text-uppercase text-muted small mb-3"><?= __t('admin.settings.policies', $translations) ?></h6>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><?= __t('admin.settings.terms_label', $translations) ?></label>
                            <textarea name="terms_content" class="form-control" rows="6"><?= htmlspecialchars($termsSetting) ?></textarea>
                            <div class="form-text"><?= __t('admin.settings.terms_help', $translations) ?></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><?= __t('admin.settings.kvkk_label', $translations) ?></label>
                            <textarea name="kvkk_content" class="form-control" rows="6"><?= htmlspecialchars($kvkkSetting) ?></textarea>
                            <div class="form-text"><?= __t('admin.settings.kvkk_help', $translations) ?></div>
                        </div>
                        <div class="col-12">
                            <label class="form-label"><?= __t('admin.settings.ai_blog_topics', $translations) ?></label>
                            <textarea name="ai_blog_topics" class="form-control" rows="3" placeholder="kredi notu, finansal planlama, faiz oranları"><?= htmlspecialchars($aiBlogTopics) ?></textarea>
                            <div class="form-text"><?= __t('admin.settings.ai_blog_help', $translations) ?></div>
                        </div>
                        <div class="col-12 text-end">
                            <button type="submit" class="btn btn-primary"><?= __t('admin.save', $translations) ?></button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php include __DIR__ . '/partials/footer.php'; ?>
