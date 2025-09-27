<?php

declare(strict_types=1);

use App\Admin\AdminApp;

[$kernel, $request] = require __DIR__ . '/../../bootstrap.php';

$controller = (new AdminApp($kernel))->settingsController('payment');
$response = $controller->form($request, 'admin/settings/payment.twig', '/admin/settings_payment.php');
$response->send();
