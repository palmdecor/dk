<?php
if (!defined('APP_ENTRY')) {
    require __DIR__ . '/includes/bootstrap.php';
}

header('Content-Type: application/xml; charset=utf-8');

$baseUrl = rtrim($config['app']['base_url'], '/');
$urls = [];

$urls[] = [
    'loc' => $baseUrl ?: '/',
    'changefreq' => 'daily',
    'priority' => '1.0',
];
$staticRoutes = [
    ['path' => 'basvuru', 'changefreq' => 'weekly', 'priority' => '0.8'],
    ['path' => 'blog', 'changefreq' => 'daily', 'priority' => '0.9'],
    ['path' => 'urunler', 'changefreq' => 'weekly', 'priority' => '0.7'],
    ['path' => 'iletisim', 'changefreq' => 'monthly', 'priority' => '0.5'],
    ['path' => 'kullanici-sozlesmesi', 'changefreq' => 'yearly', 'priority' => '0.3'],
    ['path' => 'kvkk', 'changefreq' => 'yearly', 'priority' => '0.3'],
];

foreach ($staticRoutes as $route) {
    $urls[] = [
        'loc' => site_url($route['path']),
        'changefreq' => $route['changefreq'],
        'priority' => $route['priority'],
    ];
}

$pageStmt = $pdo->query("SELECT slug, updated_at, created_at FROM pages WHERE status = 'published'");
foreach ($pageStmt->fetchAll() as $page) {
    $urls[] = [
        'loc' => site_url('sayfa/' . $page['slug']),
        'changefreq' => 'monthly',
        'priority' => '0.6',
        'lastmod' => date('c', strtotime($page['updated_at'] ?? $page['created_at'])),
    ];
}

$postStmt = $pdo->query("SELECT slug, updated_at, published_at, created_at FROM posts WHERE status = 'published'");
foreach ($postStmt->fetchAll() as $post) {
    $urls[] = [
        'loc' => site_url('blog/' . $post['slug']),
        'changefreq' => 'weekly',
        'priority' => '0.8',
        'lastmod' => date('c', strtotime($post['updated_at'] ?? $post['published_at'] ?? $post['created_at'])),
    ];
}

echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
echo "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
foreach ($urls as $url) {
    $location = $url['loc'];
    if ($baseUrl && strpos($location, 'http') !== 0) {
        $location = $baseUrl . '/' . ltrim($location, '/');
    }
    if (!$baseUrl && strpos($location, 'http') !== 0) {
        $location = '/' . ltrim($location, '/');
    }
    echo "  <url>\n";
    echo '    <loc>' . htmlspecialchars($location, ENT_XML1) . "</loc>\n";
    if (isset($url['lastmod'])) {
        echo '    <lastmod>' . htmlspecialchars($url['lastmod'], ENT_XML1) . "</lastmod>\n";
    }
    echo '    <changefreq>' . htmlspecialchars($url['changefreq'], ENT_XML1) . "</changefreq>\n";
    echo '    <priority>' . htmlspecialchars($url['priority'], ENT_XML1) . "</priority>\n";
    echo "  </url>\n";
}
echo "</urlset>";
