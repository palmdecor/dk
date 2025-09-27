<?php

declare(strict_types=1);


[$kernel, $request] = require __DIR__ . '/../../bootstrap.php';

$metrics = [
    'services' => count($kernel->repositories()->forEntity('services')->findAll()),
    'blogs' => count($kernel->repositories()->forEntity('blogs')->findAll()),
    'messages' => count($kernel->repositories()->forEntity('messages')->findAll()),
];

echo $kernel->twig()->render('admin/dashboard.twig', [
    'metrics' => $metrics,
    'currentUser' => $kernel->auth()->user(),
    'csrf' => $kernel->csrf(),
]);
