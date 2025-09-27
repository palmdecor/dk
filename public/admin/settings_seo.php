<?php

declare(strict_types=1);

use App\Admin\AdminApp;

[$kernel, $request] = require __DIR__ . '/../../bootstrap.php';

$controller = (new AdminApp($kernel))->settingsController('seo');
$response = $controller->form($request, 'admin/settings/seo.twig', '/admin/settings_seo.php');
$response->send();
