<?php

declare(strict_types=1);

use App\Service\SiteService;
use Symfony\Component\HttpFoundation\Response;

[$kernel, $request] = require __DIR__ . '/../bootstrap.php';

$service = new SiteService($kernel->repositories());
$twig = $kernel->twig();

$slug = (string) $request->query->get('slug');
$post = $service->findBySlug('blogs', $slug);

if ($post === null) {
    (new Response('Yazı bulunamadı', 404))->send();
    return;
}

$site = $service->siteSettings();
$comments = $service->commentsFor('blog', (int) $post['id']);

echo $twig->render('public/blog_detail.twig', [
    'site' => $site,
    'post' => $post,
    'comments' => $comments,
]);
