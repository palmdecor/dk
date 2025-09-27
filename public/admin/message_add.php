<?php

declare(strict_types=1);

use App\Admin\AdminApp;

[$kernel, $request] = require __DIR__ . '/../../bootstrap.php';

$admin = new AdminApp($kernel);
$controller = $admin->crudController('messages', 'messages', 'admin/messages/list.twig', 'admin/messages/form.twig', '/admin/messages.php');

$response = $controller->create($request);
$response->send();
