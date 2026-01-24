<?php

declare(strict_types=1);

return [
    'db' => [
        'dsn' => getenv('DB_DSN') ?: 'mysql:host=localhost;dbname=invenio;charset=utf8mb4',
        'user' => getenv('DB_USER') ?: 'root',
        'pass' => getenv('DB_PASS') ?: '',
    ],
    'app' => [
        'base_url' => rtrim(getenv('BASE_URL') ?: '', '/'),
        'upload_max_size' => 10 * 1024 * 1024,
        'debug' => getenv('APP_DEBUG') === 'true',
    ],
    'storage' => [
        'root' => dirname(__DIR__) . '/storage',
        'uploads' => 'uploads',
        'overlays' => 'overlays',
        'fonts' => 'fonts',
        'renders' => 'renders',
        'previews' => 'previews',
    ],
];
