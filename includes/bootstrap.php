<?php
session_start();
date_default_timezone_set('Europe/Istanbul');

$config = require __DIR__ . '/../config.php';

$dsn = sprintf(
    'mysql:host=%s;port=%d;dbname=%s;charset=%s',
    $config['db']['host'],
    $config['db']['port'],
    $config['db']['name'],
    $config['db']['charset']
);

try {
    $pdo = new PDO($dsn, $config['db']['user'], $config['db']['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

require_once __DIR__ . '/functions.php';

$lang = determine_language($config['app']);
$translations = load_translations($lang);
$config['app']['interest_rate'] = get_interest_rate($pdo, (float) $config['app']['interest_rate']);
