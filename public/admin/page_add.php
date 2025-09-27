<?php

declare(strict_types=1);

use App\Admin\AdminApp;

[$kernel, $request] = require __DIR__ . '/../../bootstrap.php';

$admin = new AdminApp($kernel);
$controller = $admin->crudController('pages', 'pages', 'admin/pages/list.twig', 'admin/pages/form.twig', '/admin/pages.php');

$response = $controller->create($request);
$response->send();
