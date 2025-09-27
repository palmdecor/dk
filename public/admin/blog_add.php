<?php

declare(strict_types=1);

use App\Admin\AdminApp;

[$kernel, $request] = require __DIR__ . '/../../bootstrap.php';

$admin = new AdminApp($kernel);
$controller = $admin->crudController('blogs', 'blogs', 'admin/blogs/list.twig', 'admin/blogs/form.twig', '/admin/blogs.php');

$response = $controller->create($request);
$response->send();
