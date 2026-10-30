<?php
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
header('Cache-Control: no-store, private');

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/Controllers/AuthController.php';
require_once __DIR__ . '/../src/Controllers/ApiProxyController.php';
SessionAuth::start('REFORMA_BACKOFFICE');

$router = new Router();

$expCtrl = new ExpensesController();
$supCtrl = new SuppliersController();
$tagCtrl = new TagsController();
$authCtrl = new AuthController();
$apiProxy = new ApiProxyController();

$method = $_SERVER['REQUEST_METHOD'];
$uriPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$router->add('GET', '/login', [$authCtrl, 'showLogin']);
$router->add('POST', '/login', [$authCtrl, 'login']);
$router->add('POST', '/logout', [$authCtrl, 'logout']);

foreach (['GET', 'POST', 'PUT', 'DELETE'] as $apiMethod) {
    $router->add($apiMethod, '/api-proxy/(.+)', [$apiProxy, 'proxy']);
}

if (in_array($uriPath, ['/login'], true) && in_array($method, ['GET', 'POST'], true)) {
    $router->dispatch($method, $uriPath);
    exit;
}

if (!SessionAuth::isAuthenticated()) {
    if (str_starts_with($uriPath, '/api-proxy/')) {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => 'Autenticação necessária']);
        exit;
    }

    header('Location: /login', true, 302);
    exit;
}

// Home → despesas
$router->add('GET', '/', [$expCtrl, 'index']);
$router->add('GET', '/expenses', [$expCtrl, 'index']);
$router->add('GET', '/expenses/create', [$expCtrl, 'create']);
$router->add('GET', '/expenses/([0-9]+)/edit', [$expCtrl, 'edit']);

// Fornecedores
$router->add('GET', '/suppliers', [$supCtrl, 'index']);
$router->add('GET', '/suppliers/create', [$supCtrl, 'create']);
$router->add('GET', '/suppliers/([0-9]+)/edit', [$supCtrl, 'edit']);

// Tags
$router->add('GET', '/tags', [$tagCtrl, 'index']);
$router->add('GET', '/tags/create', [$tagCtrl, 'create']);
$router->add('GET', '/tags/([0-9]+)/edit', [$tagCtrl, 'edit']);

$router->dispatch($method, $uriPath);
