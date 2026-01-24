<?php

declare(strict_types=1);

use App\Controllers\AdminDashboardController;
use App\Controllers\AdminFontsController;
use App\Controllers\AdminSettingsController;
use App\Controllers\AdminTemplatesController;
use App\Controllers\AdminUsersController;
use App\Controllers\AuthController;
use App\Controllers\DownloadController;
use App\Controllers\RenderController;
use App\Core\Auth;
use App\Core\Csrf;
use App\Core\DB;
use App\Core\ErrorHandler;
use App\Core\Response;
use App\Core\Router;
use App\Core\Session;
use App\Core\View;
use App\Services\ImageRenderer;
use App\Services\Storage;
use App\Services\TelegramNotifier;
use App\Services\TextLayout;

$root = dirname(__DIR__);
$autoload = $root . '/vendor/autoload.php';
if (file_exists($autoload)) {
    require $autoload;
} else {
    spl_autoload_register(function (string $class) use ($root) {
        $prefix = 'App\\';
        if (!str_starts_with($class, $prefix)) {
            return;
        }
        $path = $root . '/app/' . str_replace('App\\', '', $class) . '.php';
        $path = str_replace('\\', '/', $path);
        if (file_exists($path)) {
            require $path;
        }
    });
}

$config = require $root . '/config/config.php';

$errorHandler = new ErrorHandler((bool)($config['app']['debug'] ?? false));
$errorHandler->register();

$session = new Session();
$session->start();

$db = new DB($config['db']);
$auth = new Auth($session);
$csrf = new Csrf($session);
$response = new Response();
$view = new View($root . '/app/Views');
$view->share(['auth' => $auth, 'csrf' => $csrf, 'session' => $session]);

$storage = new Storage($config['storage']);
$storage->ensure();

$textLayout = new TextLayout();
$imageRenderer = new ImageRenderer($textLayout);
$notifier = new TelegramNotifier($config['telegram']['notify_url'] ?? '');

$authController = new AuthController($db, $auth, $csrf, $response, $view, $session);
$adminDashboard = new AdminDashboardController($db, $auth, $response, $view, $session);
$adminUsers = new AdminUsersController($db, $auth, $csrf, $response, $view, $session);
$adminFonts = new AdminFontsController($db, $auth, $csrf, $response, $view, $storage, $session, $config);
$adminTemplates = new AdminTemplatesController($db, $auth, $csrf, $response, $view, $storage, $imageRenderer, $session, $config);
$adminSettings = new AdminSettingsController($db, $auth, $csrf, $response, $view, $session);
$renderController = new RenderController($db, $auth, $csrf, $response, $view, $storage, $imageRenderer, $notifier, $session, $config);
$downloadController = new DownloadController($db, $auth, $response, $storage);

$router = new Router();

$router->get('/', fn () => $response->redirect('/login'));
$router->get('/login', fn () => $authController->showLogin());
$router->post('/login', fn () => $authController->login());
$router->post('/logout', fn () => $authController->logout());

$router->get('/admin/dashboard', fn () => $adminDashboard->index());
$router->get('/admin/users', fn () => $adminUsers->index());
$router->post('/admin/users/create', fn () => $adminUsers->create());
$router->post('/admin/users/delete/{id}', fn ($params) => $adminUsers->delete($params));
$router->post('/admin/users/toggle/{id}', fn ($params) => $adminUsers->toggle($params));

$router->get('/admin/fonts', fn () => $adminFonts->index());
$router->post('/admin/fonts/upload', fn () => $adminFonts->upload());
$router->post('/admin/fonts/toggle/{id}', fn ($params) => $adminFonts->toggle($params));
$router->post('/admin/fonts/test/{id}', fn ($params) => $adminFonts->test($params));

$router->get('/admin/templates', fn () => $adminTemplates->index());
$router->get('/admin/templates/create', fn () => $adminTemplates->createForm());
$router->post('/admin/templates', fn () => $adminTemplates->store());
$router->get('/admin/templates/{id}/edit', fn ($params) => $adminTemplates->edit($params));
$router->post('/admin/templates/{id}/overlay', fn ($params) => $adminTemplates->uploadOverlay($params));
$router->post('/admin/templates/{id}/fields', fn ($params) => $adminTemplates->saveFields($params));
$router->post('/admin/templates/{id}/test-render', fn ($params) => $adminTemplates->testRender($params));

$router->post('/admin/renders/delete/{id}', fn ($params) => $adminDashboard->deleteRender($params));

$router->get('/admin/settings', fn () => $adminSettings->index());
$router->post('/admin/settings/save', fn () => $adminSettings->save());

$router->get('/render', fn () => $renderController->form());
$router->post('/render/image', fn () => $renderController->renderImage());
$router->get('/renders/{id}', fn ($params) => $renderController->status($params));
$router->get('/templates/{id}', fn ($params) => $renderController->templateInfo($params));
$router->get('/download/{id}', fn ($params) => $downloadController->download($params));

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$router->dispatch($method, $path);
