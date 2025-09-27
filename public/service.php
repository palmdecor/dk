<?php

declare(strict_types=1);

use App\Service\SiteService;
use Symfony\Component\HttpFoundation\Response;

[$kernel, $request] = require __DIR__ . '/../bootstrap.php';

$service = new SiteService($kernel->repositories());
$twig = $kernel->twig();

$slug = (string) $request->query->get('slug');
$item = $service->findBySlug('services', $slug);

if ($item === null) {
    (new Response('Hizmet bulunamadı', 404))->send();
    return;
}

$site = $service->siteSettings();

echo $twig->render('public/service_detail.twig', [
    'site' => $site,
    'service' => $item,
]);
