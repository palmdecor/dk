<?php

declare(strict_types=1);

use App\Controllers\AdminFontsController;
use App\Controllers\AdminTemplatesController;
use App\Controllers\AuthController;
use App\Controllers\RenderController;
use App\Core\Auth;
use App\Core\Csrf;
use App\Core\DB;
use App\Core\Response;
use App\Core\Router;
use App\Core\Session;
use App\Core\View;
use App\Services\Renderer;
use App\Services\Storage;
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

$session = new Session();
$session->start();

$db = new DB($config['db']);
$auth = new Auth($session);
$csrf = new Csrf($session);
$response = new Response();
$view = new View($root . '/app/Views');
$view->share(['auth' => $auth, 'csrf' => $csrf]);

$storage = new Storage($config['storage']);
$storage->ensure();

$textLayout = new TextLayout();
$renderer = new Renderer($textLayout);

$authController = new AuthController($db, $auth, $csrf, $response, $view);
$fontsController = new AdminFontsController($db, $auth, $csrf, $response, $view, $storage, $config);
$templatesController = new AdminTemplatesController($db, $auth, $csrf, $response, $view, $storage, $renderer, $config);
$renderController = new RenderController($db, $auth, $csrf, $response, $view, $storage, $renderer, $config);

$router = new Router();

$router->get('/', fn () => $response->redirect('/login'));
$router->get('/login', fn () => $authController->showLogin());
$router->post('/login', fn () => $authController->login());
$router->post('/logout', fn () => $authController->logout());

$router->get('/admin/fonts', fn () => $fontsController->index());
$router->post('/admin/fonts/upload', fn () => $fontsController->upload());
$router->post('/admin/fonts/{id}/toggle', fn ($params) => $fontsController->toggle($params));

$router->get('/admin/templates', fn () => $templatesController->index());
$router->get('/admin/templates/create', fn () => $templatesController->createForm());
$router->post('/admin/templates', fn () => $templatesController->store());
$router->get('/admin/templates/{id}/edit', fn ($params) => $templatesController->edit($params));
$router->post('/admin/templates/{id}/overlay', fn ($params) => $templatesController->uploadOverlay($params));
$router->get('/admin/templates/{id}/overlay-view', fn ($params) => $templatesController->overlayView($params));
$router->post('/admin/templates/{id}/fields', fn ($params) => $templatesController->saveFields($params));
$router->post('/admin/templates/{id}/test-render', fn ($params) => $templatesController->testRender($params));

$router->get('/templates', fn () => $renderController->templates());
$router->get('/templates/{id}', fn ($params) => $renderController->templateDetail($params));
$router->get('/render', fn () => $renderController->renderForm());
$router->post('/render/image', fn () => $renderController->renderImage());
$router->get('/renders/{id}', fn ($params) => $renderController->renderStatus($params));
$router->get('/download/{id}', fn ($params) => $renderController->download($params));
$router->get('/preview/{id}', fn ($params) => $renderController->preview($params));

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$router->dispatch($method, $path);
