<?php
if (!defined('APP_ENTRY')) {
    require __DIR__ . '/includes/bootstrap.php';
}
session_unset();
session_destroy();
header('Location: ' . site_url());
exit;
