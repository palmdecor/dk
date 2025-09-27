<?php

declare(strict_types=1);

use App\Admin\AdminApp;

[$kernel, $request] = require __DIR__ . '/../../bootstrap.php';

$id = (int) ($request->query->get('id') ?? 0);
if ($id <= 0) {
    throw new RuntimeException('Geçersiz ID parametresi.');
}

$admin = new AdminApp($kernel);
$controller = $admin->crudController('pages', 'pages', 'admin/pages/list.twig', 'admin/pages/form.twig', '/admin/pages.php');

$response = $controller->edit($id, $request);
$response->send();
