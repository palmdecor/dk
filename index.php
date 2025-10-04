<?php
define('APP_ENTRY', true);
require __DIR__ . '/includes/bootstrap.php';

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
$path = trim($path, '/');
$path = $path === '' ? '' : urldecode($path);

switch (true) {
    case $path === '' || $path === 'index.php':
        $homepageContent = get_homepage_content($pdo, $translations);
        $meta = [
            'title' => !empty($homepageContent['meta_title']) ? $homepageContent['meta_title'] : __t('meta.home.title', $translations),
            'description' => !empty($homepageContent['meta_description']) ? $homepageContent['meta_description'] : __t('meta.home.description', $translations),
        ];
        $featuredPosts = latest_posts($pdo, 3);
        $featuredProducts = array_slice(published_products($pdo), 0, 3);
        $interestRate = (float) $config['app']['interest_rate'];
        $tahsisRate = get_disbursement_rate($pdo);
        $advantageIcons = ['bi-lightning-fill', 'bi-headset', 'bi-shield-lock'];
        $flash = get_flash_messages();
        include __DIR__ . '/views/home.php';
        break;

    case $path === 'blog':
        require __DIR__ . '/blog.php';
        break;

    case preg_match('~^blog/([a-z0-9\-]+)$~i', $path, $matches):
        $_GET['slug'] = $matches[1];
        require __DIR__ . '/post.php';
        break;

    case $path === 'urunler':
        require __DIR__ . '/products.php';
        break;

    case preg_match('~^urun/([a-z0-9\-]+)$~i', $path, $matches):
        $_GET['slug'] = $matches[1];
        require __DIR__ . '/purchase.php';
        break;

    case $path === 'basvuru':
        require __DIR__ . '/apply.php';
        break;

    case $path === 'iletisim':
        require __DIR__ . '/contact.php';
        break;

    case $path === 'dashboard':
        require __DIR__ . '/dashboard.php';
        break;

    case $path === 'admin':
        require __DIR__ . '/admin.php';
        break;

    case $path === 'giris':
        require __DIR__ . '/login.php';
        break;

    case $path === 'kayit':
        require __DIR__ . '/register.php';
        break;

    case $path === 'cikis':
        require __DIR__ . '/logout.php';
        break;

    case $path === 'kvkk':
        require __DIR__ . '/kvkk.php';
        break;

    case $path === 'kullanici-sozlesmesi':
        require __DIR__ . '/terms.php';
        break;

    case preg_match('~^sayfa/([a-z0-9\-]+)$~i', $path, $matches):
        $_GET['slug'] = $matches[1];
        require __DIR__ . '/page.php';
        break;

    case $path === 'odeme/callback':
        require __DIR__ . '/payment_callback.php';
        break;

    case $path === 'sitemap.xml' || $path === 'sitemap':
        require __DIR__ . '/sitemap.php';
        break;

    case preg_match('~^api/(.+)$~', $path, $matches):
        $apiPath = __DIR__ . '/' . $matches[0] . '.php';
        if (is_file($apiPath)) {
            require $apiPath;
            break;
        }
        // fallthrough to 404 if API file not found

    default:
        http_response_code(404);
        $meta = seo_meta_tags($translations, 'meta.404.title', 'meta.404.description');
        include __DIR__ . '/views/not-found.php';
        break;
}
