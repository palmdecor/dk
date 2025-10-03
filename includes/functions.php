<?php
function determine_language(array $appConfig): string
{
    if (isset($_GET['lang']) && in_array($_GET['lang'], $appConfig['supported_langs'], true)) {
        $_SESSION['lang'] = $_GET['lang'];
    }

    if (!empty($_SESSION['lang']) && in_array($_SESSION['lang'], $appConfig['supported_langs'], true)) {
        return $_SESSION['lang'];
    }

    return $appConfig['default_lang'];
}

function load_translations(string $lang): array
{
    $file = __DIR__ . '/../languages/' . $lang . '.php';
    if (file_exists($file)) {
        return require $file;
    }

    return require __DIR__ . '/../languages/tr.php';
}

function __t(string $key, array $translations): string
{
    return $translations[$key] ?? $key;
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function require_login(): void
{
    if (!current_user()) {
        header('Location: login.php');
        exit;
    }
}

function require_admin(): void
{
    $user = current_user();
    if (!$user || ($user['role'] ?? 'user') !== 'admin') {
        header('Location: login.php');
        exit;
    }
}

function sanitize(string $value): string
{
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}

function calculate_monthly_payment(float $principal, int $months, float $monthlyRate): float
{
    if ($months <= 0) {
        return 0.0;
    }

    // Simple interest calculation (principal + interest) / months
    $total = $principal * (1 + $monthlyRate * $months);
    return $total / $months;
}

function seo_meta_tags(array $translations, string $titleKey, string $descriptionKey): array
{
    return [
        'title' => __t($titleKey, $translations),
        'description' => __t($descriptionKey, $translations),
    ];
}

function redirect_with_message(string $url, string $type, string $message): void
{
    $_SESSION['flash'][$type][] = $message;
    header('Location: ' . $url);
    exit;
}

function get_flash_messages(): array
{
    $flash = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flash;
}

function slugify(string $value): string
{
    $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
    $value = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $value));
    return trim($value, '-') ?: uniqid();
}

function find_page_by_slug(PDO $pdo, string $slug): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM pages WHERE slug = :slug AND status = "published"');
    $stmt->execute(['slug' => $slug]);
    $page = $stmt->fetch();
    return $page ?: null;
}

function published_pages(PDO $pdo): array
{
    $stmt = $pdo->query('SELECT * FROM pages WHERE status = "published" ORDER BY created_at DESC');
    return $stmt->fetchAll();
}

function latest_posts(PDO $pdo, int $limit = 5): array
{
    $stmt = $pdo->prepare('SELECT * FROM posts WHERE status = "published" ORDER BY published_at DESC LIMIT :limit');
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function published_products(PDO $pdo): array
{
    $stmt = $pdo->query('SELECT * FROM products WHERE status = "published" ORDER BY created_at DESC');
    return $stmt->fetchAll();
}

function format_price(float $amount): string
{
    return number_format($amount, 2, ',', '.') . ' ₺';
}

function ensure_unique_slug(PDO $pdo, string $table, string $slug, ?int $ignoreId = null): string
{
    $allowedTables = ['pages', 'posts', 'products'];
    if (!in_array($table, $allowedTables, true)) {
        return $slug;
    }

    $baseSlug = $slug;
    $suffix = 1;
    while (true) {
        $sql = "SELECT COUNT(*) FROM {$table} WHERE slug = :slug";
        $params = ['slug' => $slug];
        if ($ignoreId) {
            $sql .= ' AND id != :id';
            $params['id'] = $ignoreId;
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $exists = (int) $stmt->fetchColumn() > 0;
        if (!$exists) {
            break;
        }
        $slug = $baseSlug . '-' . $suffix;
        $suffix++;
    }

    return $slug;
}
