<?php

declare(strict_types=1);

use App\Service\ContactService;
use App\Service\SiteService;
use Symfony\Component\HttpFoundation\Response;

[$kernel, $request] = require __DIR__ . '/../bootstrap.php';

$siteService = new SiteService($kernel->repositories());
$contactService = new ContactService($kernel->repositories(), $kernel->csrf(), $kernel->config());
$twig = $kernel->twig();

if ($request->isMethod('POST')) {
    try {
        $contactService->handle($request);
        $response = new Response($twig->render('public/contact.twig', [
            'site' => $siteService->siteSettings(),
            'csrf_token' => $contactService->generateToken(),
            'success' => 'Mesajınız alınmıştır.',
        ]));
        $response->send();
        return;
    } catch (Throwable $exception) {
        $response = new Response($twig->render('public/contact.twig', [
            'site' => $siteService->siteSettings(),
            'csrf_token' => $contactService->generateToken(),
            'error' => $exception->getMessage(),
        ]), 400);
        $response->send();
        return;
    }
}

echo $twig->render('public/contact.twig', [
    'site' => $siteService->siteSettings(),
    'csrf_token' => $contactService->generateToken(),
]);
