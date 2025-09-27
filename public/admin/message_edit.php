<?php

declare(strict_types=1);

use App\Admin\AdminApp;

[$kernel, $request] = require __DIR__ . '/../../bootstrap.php';

$id = (int) ($request->query->get('id') ?? 0);
if ($id <= 0) {
    throw new RuntimeException('Geçersiz ID parametresi.');
}

$admin = new AdminApp($kernel);
$controller = $admin->crudController('messages', 'messages', 'admin/messages/list.twig', 'admin/messages/form.twig', '/admin/messages.php');

$response = $controller->edit($id, $request);
$response->send();
