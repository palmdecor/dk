<?php

declare(strict_types=1);

use App\Admin\AdminApp;

[$kernel, $request] = require __DIR__ . '/../../bootstrap.php';

$admin = new AdminApp($kernel);
$controller = $admin->crudController('services', 'services', 'admin/services/list.twig', 'admin/services/form.twig', '/admin/services.php');

$response = $controller->index();
$response->send();
