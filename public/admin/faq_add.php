<?php

declare(strict_types=1);

use App\Admin\AdminApp;

[$kernel, $request] = require __DIR__ . '/../../bootstrap.php';

$admin = new AdminApp($kernel);
$controller = $admin->crudController('faq', 'faq', 'admin/faq/list.twig', 'admin/faq/form.twig', '/admin/faq.php');

$response = $controller->create($request);
$response->send();
