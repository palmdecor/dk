<?php

declare(strict_types=1);

use App\Admin\AdminApp;

[$kernel, $request] = require __DIR__ . '/../../bootstrap.php';

$id = (int) ($request->query->get('id') ?? 0);
if ($id <= 0) {
    throw new RuntimeException('Geçersiz ID parametresi.');
}

$admin = new AdminApp($kernel);
$controller = $admin->crudController('comments', 'comments', 'admin/comments/list.twig', 'admin/comments/form.twig', '/admin/comments.php');

$response = $controller->delete($id, $request);
$response->send();
