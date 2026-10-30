<?php
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
header('Cache-Control: no-store, private');

require __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

require_once __DIR__ . '/../src/Auth.php';
SessionAuth::start('REFORMA_DASHBOARD');

$router = new Router();
$ctrl = new DashboardPageController();

$router->add('GET', '/', [$ctrl, 'index']);
$router->add('GET', '/dashboard', [$ctrl, 'index']);
$router->add('GET', '/dashboard/expenses', [$ctrl, 'expenses']);
$router->add('GET', '/dashboard/tags', [$ctrl, 'tags']);
$router->add('GET', '/dashboard/suppliers', [$ctrl, 'suppliers']);

$method = $_SERVER['REQUEST_METHOD'];
$uriPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

/**
 * Normaliza URL:
 * - remove barra final (exceto "/")
 */
if ($uriPath !== '/' && str_ends_with($uriPath, '/')) {
    $uriPath = rtrim($uriPath, '/');
}

if ($uriPath === '/login') {
    if ($method === 'GET') {
        if (SessionAuth::isAuthenticated()) {
            header('Location: /', true, 302);
            exit;
        }
        $errorMessage = null;
        include __DIR__ . '/../views/auth/login.php';
        exit;
    }

    if ($method === 'POST') {
        if (!SessionAuth::verifyCsrf($_POST['csrf_token'] ?? null)) {
            http_response_code(403);
            echo 'Solicitação inválida. Atualize a página e tente novamente.';
            exit;
        }

        if (SessionAuth::isLoginThrottled()) {
            http_response_code(429);
            header('Retry-After: 900');
            $errorMessage = 'Muitas tentativas. Aguarde 15 minutos antes de tentar novamente.';
            include __DIR__ . '/../views/auth/login.php';
            exit;
        }

        $username = is_string($_POST['username'] ?? null) ? $_POST['username'] : '';
        $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
        if (!SessionAuth::login($username, $password)) {
            SessionAuth::recordFailedLogin();
            http_response_code(401);
            $errorMessage = 'Usuário ou senha incorretos.';
            include __DIR__ . '/../views/auth/login.php';
            exit;
        }

        SessionAuth::clearLoginThrottle();
        header('Location: /', true, 303);
        exit;
    }
}

if ($uriPath === '/logout' && $method === 'POST') {
    if (!SessionAuth::verifyCsrf($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        echo 'Solicitação inválida.';
        exit;
    }
    SessionAuth::logout();
    header('Location: /login', true, 303);
    exit;
}

if (!SessionAuth::isAuthenticated()) {
    header('Location: /login', true, 302);
    exit;
}

$router->dispatch($method, $uriPath);
