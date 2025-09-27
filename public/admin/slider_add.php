<?php

declare(strict_types=1);

use App\Admin\AdminApp;

[$kernel, $request] = require __DIR__ . '/../../bootstrap.php';

$admin = new AdminApp($kernel);
$controller = $admin->crudController('sliders', 'sliders', 'admin/slider/list.twig', 'admin/slider/form.twig', '/admin/slider.php');

$response = $controller->create($request);
$response->send();
