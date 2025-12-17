<?php
require_once __DIR__ . '/../src/Router.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Controllers/ExpensesController.php';
require_once __DIR__ . '/../src/Controllers/DashboardController.php';
require_once __DIR__ . '/../src/Controllers/SuppliersController.php';
require_once __DIR__ . '/../src/Controllers/TagsController.php';

// CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$uriPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uriPath = '/' . ltrim($uriPath, '/');

if ($uriPath !== '/' && str_ends_with($uriPath, '/')) {
    $uriPath = rtrim($uriPath, '/');
}

$router = new Router();

$expenses   = new ExpensesController();
$dashboard  = new DashboardController();
$suppliers  = new SuppliersController();
$tags       = new TagsController();

// =====================
// Expenses
// =====================
$router->add('GET',    '/expenses',             [$expenses, 'index']);
$router->add('GET',    '/expenses/([0-9]+)',    [$expenses, 'show']);
$router->add('POST',   '/expenses',             [$expenses, 'store']);
$router->add('PUT',    '/expenses/([0-9]+)',    [$expenses, 'update']);
$router->add('DELETE', '/expenses/([0-9]+)',    [$expenses, 'destroy']);
$router->add('POST',   '/expenses/([0-9]+)/confirm', [$expenses, 'confirm']);

// =====================
// Suppliers
// =====================
$router->add('GET',    '/suppliers',            [$suppliers, 'index']);
$router->add('GET',    '/suppliers/([0-9]+)',   [$suppliers, 'show']);
$router->add('POST',   '/suppliers',            [$suppliers, 'store']);
$router->add('PUT',    '/suppliers/([0-9]+)',   [$suppliers, 'update']);
$router->add('DELETE', '/suppliers/([0-9]+)',   [$suppliers, 'destroy']);

// =====================
// Tags
// =====================
$router->add('GET',    '/tags',                 [$tags, 'index']);
$router->add('GET',    '/tags/([0-9]+)',        [$tags, 'show']);
$router->add('POST',   '/tags',                 [$tags, 'store']);
$router->add('PUT',    '/tags/([0-9]+)',        [$tags, 'update']);
$router->add('DELETE', '/tags/([0-9]+)',        [$tags, 'destroy']);

// =====================
// Dashboard
// =====================
$router->add('GET', '/dashboard/expenses', [$dashboard, 'expenses']);
$router->add('GET', '/dashboard/discounts', [$dashboard, 'discounts']);
$router->add('GET', '/dashboard/tags', [$dashboard, 'tags']);
$router->add('GET', '/dashboard/suppliers', [$dashboard, 'suppliers']);

$router->dispatch($_SERVER['REQUEST_METHOD'], $uriPath);
