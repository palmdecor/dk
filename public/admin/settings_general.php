<?php

declare(strict_types=1);

use App\Admin\AdminApp;

[$kernel, $request] = require __DIR__ . '/../../bootstrap.php';

$controller = (new AdminApp($kernel))->settingsController('general');
$response = $controller->form($request, 'admin/settings/general.twig', '/admin/settings_general.php');
$response->send();
