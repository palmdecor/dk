<?php

declare(strict_types=1);

use App\Admin\AdminApp;

[$kernel, $request] = require __DIR__ . '/../../bootstrap.php';

$admin = new AdminApp($kernel);
$controller = $admin->crudController('users', 'users', 'admin/users/list.twig', 'admin/users/form.twig', '/admin/users.php');

$response = $controller->index();
$response->send();
