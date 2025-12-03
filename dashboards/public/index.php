<?php
require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/Router.php';
require_once __DIR__ . '/../src/Controllers/CategoryDashboardController.php';

$router = new Router();
$ctrl = new CategoryDashboardController();

$router->add('GET', '/', [$ctrl, 'index']);
$router->add('GET', '/dashboard/category', [$ctrl, 'index']);

$method = $_SERVER['REQUEST_METHOD'];
$uriPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$router->dispatch($method, $uriPath);
