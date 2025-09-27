<?php

declare(strict_types=1);

use App\Admin\AdminApp;

[$kernel, $request] = require __DIR__ . '/../../bootstrap.php';

$id = (int) ($request->query->get('id') ?? 0);
if ($id <= 0) {
    throw new RuntimeException('Geçersiz ID parametresi.');
}

$admin = new AdminApp($kernel);
$controller = $admin->crudController('sliders', 'sliders', 'admin/slider/list.twig', 'admin/slider/form.twig', '/admin/slider.php');

$response = $controller->delete($id, $request);
$response->send();
