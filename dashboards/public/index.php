<?php
require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/Router.php';
require_once __DIR__ . '/../src/Controllers/DashboardPageController.php';

$router = new Router();
$ctrl = new DashboardPageController();

$router->add('GET', '/', [$ctrl, 'index']);
$router->add('GET', '/dashboard', [$ctrl, 'index']);
$router->add('GET', '/dashboard/expenses', [$ctrl, 'expenses']);
$router->add('GET', '/dashboard/tags', [$ctrl, 'tags']);
$router->add('GET', '/dashboard/suppliers', [$ctrl, 'suppliers']);

$method  = $_SERVER['REQUEST_METHOD'];
$uriPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

/**
 * Normaliza URL:
 * - remove barra final (exceto "/")
 */
if ($uriPath !== '/' && str_ends_with($uriPath, '/')) {
    $uriPath = rtrim($uriPath, '/');
}

$router->dispatch($method, $uriPath);
