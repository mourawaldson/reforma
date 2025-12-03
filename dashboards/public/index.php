<?php
require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/Router.php';
require_once __DIR__ . '/../src/Controllers/CategoryDashboardController.php';

$router = new Router();
$ctrl = new CategoryDashboardController();

// Rota raiz → dashboard
$router->add('GET', '/', [$ctrl, 'index']);

// /dashboard → mesma página da categoria
$router->add('GET', '/dashboard', [$ctrl, 'index']);

// /dashboard/category → mesma coisa (pode ser útil pra links futuros)
$router->add('GET', '/dashboard/category', [$ctrl, 'index']);

$method  = $_SERVER['REQUEST_METHOD'];
$uriPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$router->dispatch($method, $uriPath);
