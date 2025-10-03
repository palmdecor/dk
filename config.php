<?php
return [
    'db' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'name' => 'kredi_portal',
        'user' => 'root',
        'pass' => '',
        'charset' => 'utf8mb4',
    ],
    'app' => [
        'name' => 'Finans Portal',
        'base_url' => '/',
        'default_lang' => 'tr',
        'supported_langs' => ['tr', 'en'],
        'interest_rate' => 0.019,
    ],
    'mail' => [
        'from' => 'no-reply@example.com',
        'to' => 'destek@example.com',
    ],
];
