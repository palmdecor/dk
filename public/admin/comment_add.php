<?php

declare(strict_types=1);

use App\Admin\AdminApp;

[$kernel, $request] = require __DIR__ . '/../../bootstrap.php';

$admin = new AdminApp($kernel);
$controller = $admin->crudController('comments', 'comments', 'admin/comments/list.twig', 'admin/comments/form.twig', '/admin/comments.php');

$response = $controller->create($request);
$response->send();
