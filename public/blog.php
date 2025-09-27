<?php

declare(strict_types=1);

use App\Service\SiteService;

[$kernel, $request] = require __DIR__ . '/../bootstrap.php';

$service = new SiteService($kernel->repositories());
$twig = $kernel->twig();

$site = $service->siteSettings();
$posts = $service->published('blogs', 30);

echo $twig->render('public/blog.twig', [
    'site' => $site,
    'blog' => $posts,
]);
