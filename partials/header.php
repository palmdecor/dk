<?php
if (!isset($translations, $config, $lang)) {
    throw new RuntimeException('Header requires translations and config.');
}
$meta = $meta ?? ['title' => $config['app']['name'], 'description' => $config['app']['name']];
$currentUrl = rtrim($config['app']['base_url'], '/') . strtok($_SERVER['REQUEST_URI'], '?');
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang) ?>">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($meta['title']) ?></title>
    <meta name="description" content="<?= htmlspecialchars($meta['description']) ?>">
    <link rel="canonical" href="<?= htmlspecialchars($currentUrl) ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold text-primary" href="index.php">Finans Portal</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-lg-center">
                <li class="nav-item"><a class="nav-link" href="index.php"><?= __t('nav.home', $translations) ?></a></li>
                <li class="nav-item"><a class="nav-link" href="apply.php"><?= __t('nav.apply', $translations) ?></a></li>
                <?php if (current_user()): ?>
                    <li class="nav-item"><a class="nav-link" href="dashboard.php"><?= __t('nav.dashboard', $translations) ?></a></li>
                <?php endif; ?>
                <?php if (current_user() && (current_user()['role'] ?? 'user') === 'admin'): ?>
                    <li class="nav-item"><a class="nav-link" href="admin.php"><?= __t('nav.admin', $translations) ?></a></li>
                <?php endif; ?>
                <li class="nav-item"><a class="nav-link" href="contact.php"><?= __t('nav.contact', $translations) ?></a></li>
                <?php if (!current_user()): ?>
                    <li class="nav-item"><a class="nav-link" href="login.php"><?= __t('nav.login', $translations) ?></a></li>
                    <li class="nav-item"><a class="btn btn-primary ms-lg-2" href="register.php"><?= __t('nav.register', $translations) ?></a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="btn btn-outline-primary ms-lg-2" href="logout.php"><?= __t('nav.logout', $translations) ?></a></li>
                <?php endif; ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="langDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <?= strtoupper($lang) ?>
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="langDropdown">
                        <?php foreach ($config['app']['supported_langs'] as $supportedLang): ?>
                            <?php $path = basename($_SERVER['PHP_SELF']); ?>
                            <li><a class="dropdown-item" href="<?= $path ?>?lang=<?= $supportedLang ?>"><?= strtoupper($supportedLang) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
<main class="flex-grow-1">
