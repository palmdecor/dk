<?php

declare(strict_types=1);

require_once __DIR__ . '/core/functions.php';

session_destroy();
redirect('login.php');
