<?php
require_once __DIR__ . '/../src/Router.php';
require_once __DIR__ . '/../src/Controllers/ExpensesController.php';
require_once __DIR__ . '/../src/Controllers/SuppliersController.php';
require_once __DIR__ . '/../src/Controllers/TagsController.php';

$router = new Router();

$expCtrl  = new ExpensesController();
$supCtrl  = new SuppliersController();
$tagCtrl  = new TagsController();

// Home → despesas
$router->add('GET', '/',                [$expCtrl, 'index']);
$router->add('GET', '/expenses',        [$expCtrl, 'index']);
$router->add('GET', '/expenses/create', [$expCtrl, 'create']);
$router->add('GET', '/expenses/([0-9]+)/edit', [$expCtrl, 'edit']);

// Fornecedores
$router->add('GET', '/suppliers',              [$supCtrl, 'index']);
$router->add('GET', '/suppliers/create',       [$supCtrl, 'create']);
$router->add('GET', '/suppliers/([0-9]+)/edit', [$supCtrl, 'edit']);

// Tags
$router->add('GET', '/tags',                   [$tagCtrl, 'index']);
$router->add('GET', '/tags/create',            [$tagCtrl, 'create']);
$router->add('GET', '/tags/([0-9]+)/edit',     [$tagCtrl, 'edit']);

$method = $_SERVER['REQUEST_METHOD'];
$uriPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$router->dispatch($method, $uriPath);
