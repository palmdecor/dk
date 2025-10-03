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

function get_setting(PDO $pdo, string $key, $default = null)
{
    if (!isset($GLOBALS['__settings_cache']) || !is_array($GLOBALS['__settings_cache'])) {
        $GLOBALS['__settings_cache'] = [];
    }

    if (array_key_exists($key, $GLOBALS['__settings_cache'])) {
        return $GLOBALS['__settings_cache'][$key];
    }

    $stmt = $pdo->prepare('SELECT `value` FROM settings WHERE `key` = :key LIMIT 1');
    $stmt->execute(['key' => $key]);
    $value = $stmt->fetchColumn();

    if ($value === false) {
        $GLOBALS['__settings_cache'][$key] = $default;
        return $default;
    }

    $GLOBALS['__settings_cache'][$key] = $value;
    return $value;
}

function set_setting(PDO $pdo, string $key, string $value): void
{
    $stmt = $pdo->prepare('INSERT INTO settings (`key`, `value`) VALUES (:key, :value)
        ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), updated_at = CURRENT_TIMESTAMP');
    $stmt->execute(['key' => $key, 'value' => $value]);

    if (!isset($GLOBALS['__settings_cache']) || !is_array($GLOBALS['__settings_cache'])) {
        $GLOBALS['__settings_cache'] = [];
    }
    $GLOBALS['__settings_cache'][$key] = $value;
}

function get_interest_rate(PDO $pdo, float $default): float
{
    $stored = get_setting($pdo, 'interest_rate');
    if ($stored !== null && $stored !== '' && is_numeric($stored)) {
        return (float) $stored;
    }

    return $default;
}

function get_homepage_content(PDO $pdo, array $translations): array
{
    $defaults = [
        'meta_title' => __t('meta.home.title', $translations),
        'meta_description' => __t('meta.home.description', $translations),
        'hero_title' => __t('hero.title', $translations),
        'hero_subtitle' => __t('hero.subtitle', $translations),
        'advantages' => [
            ['title' => __t('advantages.fast', $translations), 'description' => __t('advantages.fast.desc', $translations)],
            ['title' => __t('advantages.support', $translations), 'description' => __t('advantages.support.desc', $translations)],
            ['title' => __t('advantages.secure', $translations), 'description' => __t('advantages.secure.desc', $translations)],
        ],
        'testimonials' => [
            ['name' => __t('testimonials.1.name', $translations), 'text' => __t('testimonials.1', $translations)],
            ['name' => __t('testimonials.2.name', $translations), 'text' => __t('testimonials.2', $translations)],
            ['name' => __t('testimonials.3.name', $translations), 'text' => __t('testimonials.3', $translations)],
        ],
    ];

    $stored = get_setting($pdo, 'homepage_content');
    if (is_string($stored) && $stored !== '') {
        $decoded = json_decode($stored, true);
        if (is_array($decoded)) {
            $defaults = array_replace_recursive($defaults, $decoded);
        }
    }

    return $defaults;
}

function save_homepage_content(PDO $pdo, array $content): void
{
    set_setting($pdo, 'homepage_content', json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
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
