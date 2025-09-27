<?php

declare(strict_types=1);

use App\Kernel;
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ . '/vendor/autoload.php';

$kernel = new Kernel();
$request = Request::createFromGlobals();

return [$kernel, $request];
