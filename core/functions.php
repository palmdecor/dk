<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/status.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function validate_csrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        exit('Geçersiz CSRF token.');
    }
}

function require_login(): void
{
    if (empty($_SESSION['user_id'])) {
        redirect('login.php');
    }
}

function get_settings(PDO $pdo): array
{
    $stmt = $pdo->query('SELECT id, restaurant_name, logo, menu_link FROM settings LIMIT 1');
    $settings = $stmt->fetch();

    if (!$settings) {
        return [
            'id' => null,
            'restaurant_name' => 'Restoranınız',
            'logo' => '',
            'menu_link' => 'https://example.com/menu',
        ];
    }

    return $settings;
}

function dashboard_counts(PDO $pdo): array
{
    $categoryCount = (int) $pdo->query('SELECT COUNT(*) FROM categories')->fetchColumn();
    $productCount = (int) $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
    $activeProductCount = (int) $pdo->query('SELECT COUNT(*) FROM products WHERE status = 1')->fetchColumn();

    return [
        'categories' => $categoryCount,
        'products' => $productCount,
        'active_products' => $activeProductCount,
    ];
}

function flash_message(string $message, string $type = 'success'): void
{
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

function get_flash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);

    return $flash;
}
