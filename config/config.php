<?php

declare(strict_types=1);

use Dotenv\Dotenv;

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
if (file_exists(dirname(__DIR__) . '/.env')) {
    $dotenv->load();
} else {
    $dotenv->load();
}

return [
    'env' => $_ENV['APP_ENV'] ?? 'prod',
    'debug' => (bool) ($_ENV['APP_DEBUG'] ?? false),
    'url' => $_ENV['APP_URL'] ?? 'http://localhost',
    'db' => [
        'dsn' => $_ENV['DB_DSN'] ?? '',
        'user' => $_ENV['DB_USER'] ?? '',
        'password' => $_ENV['DB_PASSWORD'] ?? '',
    ],
    'mailer_dsn' => $_ENV['MAILER_DSN'] ?? '',
    'recaptcha' => [
        'site_key' => $_ENV['RECAPTCHA_SITE_KEY'] ?? '',
        'secret_key' => $_ENV['RECAPTCHA_SECRET_KEY'] ?? '',
    ],
    'paytr' => [
        'merchant_id' => $_ENV['PAYTR_MERCHANT_ID'] ?? '',
        'merchant_key' => $_ENV['PAYTR_MERCHANT_KEY'] ?? '',
        'merchant_salt' => $_ENV['PAYTR_MERCHANT_SALT'] ?? '',
    ],
    'shopier' => [
        'api_key' => $_ENV['SHOPIER_API_KEY'] ?? '',
        'api_secret' => $_ENV['SHOPIER_API_SECRET'] ?? '',
    ],
    'kobikom' => [
        'user' => $_ENV['KOBIKOM_USER'] ?? '',
        'password' => $_ENV['KOBIKOM_PASSWORD'] ?? '',
        'header' => $_ENV['KOBIKOM_HEADER'] ?? '',
    ],
    'netgsm' => [
        'user' => $_ENV['NETGSM_USER'] ?? '',
        'password' => $_ENV['NETGSM_PASSWORD'] ?? '',
        'header' => $_ENV['NETGSM_HEADER'] ?? '',
    ],
    'contact_email' => $_ENV['CONTACT_EMAIL'] ?? '',
];
