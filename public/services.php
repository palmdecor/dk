<?php

declare(strict_types=1);

use App\Service\SiteService;

[$kernel, $request] = require __DIR__ . '/../bootstrap.php';

$service = new SiteService($kernel->repositories());
$twig = $kernel->twig();

$site = $service->siteSettings();
$services = $service->published('services', 50);

echo $twig->render('public/services.twig', [
    'site' => $site,
    'services' => $services,
]);
