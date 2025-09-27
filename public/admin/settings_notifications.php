<?php

declare(strict_types=1);

use App\Admin\AdminApp;

[$kernel, $request] = require __DIR__ . '/../../bootstrap.php';

$controller = (new AdminApp($kernel))->settingsController('notifications');
$response = $controller->form($request, 'admin/settings/notifications.twig', '/admin/settings_notifications.php');
$response->send();
