<?php

declare(strict_types=1);

use App\Admin\AdminApp;
use App\Kernel;
use App\Service\SiteService;

[$kernel, $request] = require __DIR__ . '/../bootstrap.php';

$service = new SiteService($kernel->repositories());
$twig = $kernel->twig();

$site = $service->siteSettings();

$sliders = $service->published('sliders', 5);
$services = $service->published('services', 6);
$faq = $kernel->repositories()->forEntity('faq')->findAll();
$blog = $service->published('blogs', 3);

echo $twig->render('public/home.twig', [
    'site' => $site,
    'sliders' => $sliders,
    'services' => $services,
    'faq' => $faq,
    'blog' => $blog,
]);
