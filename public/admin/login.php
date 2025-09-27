<?php

declare(strict_types=1);

use App\Admin\AdminApp;

[$kernel, $request] = require __DIR__ . '/../../bootstrap.php';

$controller = (new AdminApp($kernel))->authController();
$response = $controller->login($request);
$response->send();
