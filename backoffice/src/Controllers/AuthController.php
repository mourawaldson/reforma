<?php
declare(strict_types=1);

require_once __DIR__ . '/../Auth.php';

class AuthController
{
    public function showLogin(): void
    {
        if (SessionAuth::isAuthenticated()) {
            header('Location: /expenses', true, 302);
            return;
        }

        $errorMessage = null;
        include __DIR__ . '/../../views/auth/login.php';
    }

    public function login(): void
    {
        if (!SessionAuth::verifyCsrf($_POST['csrf_token'] ?? null)) {
            http_response_code(403);
            echo 'Solicitação inválida. Atualize a página e tente novamente.';
            return;
        }

        if (SessionAuth::isLoginThrottled()) {
            http_response_code(429);
            header('Retry-After: 900');
            $errorMessage = 'Muitas tentativas. Aguarde 15 minutos antes de tentar novamente.';
            include __DIR__ . '/../../views/auth/login.php';
            return;
        }

        $username = is_string($_POST['username'] ?? null) ? $_POST['username'] : '';
        $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';

        if (!SessionAuth::login($username, $password)) {
            SessionAuth::recordFailedLogin();
            http_response_code(401);
            $errorMessage = 'Usuário ou senha incorretos.';
            include __DIR__ . '/../../views/auth/login.php';
            return;
        }

        SessionAuth::clearLoginThrottle();
        header('Location: /expenses', true, 303);
    }

    public function logout(): void
    {
        if (!SessionAuth::verifyCsrf($_POST['csrf_token'] ?? null)) {
            http_response_code(403);
            echo 'Solicitação inválida.';
            return;
        }

        SessionAuth::logout();
        header('Location: /login', true, 303);
    }
}
