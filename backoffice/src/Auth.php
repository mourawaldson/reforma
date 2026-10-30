<?php
declare(strict_types=1);

class SessionAuth
{
    public static function start(string $sessionName): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        session_name($sessionName);
        session_set_cookie_params([
            'httponly' => true,
            'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'samesite' => 'Lax',
            'path' => '/',
        ]);
        session_start();
        $_SESSION['csrf_token'] ??= bin2hex(random_bytes(32));
    }

    public static function isAuthenticated(): bool
    {
        return isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;
    }

    public static function csrfToken(): string
    {
        return (string) ($_SESSION['csrf_token'] ?? '');
    }

    public static function verifyCsrf(?string $token): bool
    {
        return $token !== null && self::csrfToken() !== '' && hash_equals(self::csrfToken(), $token);
    }

    public static function login(string $username, string $password): bool
    {
        $expectedUsername = getenv('APP_AUTH_USERNAME');
        $expectedPassword = getenv('APP_AUTH_PASSWORD');

        if (!is_string($expectedUsername) || !is_string($expectedPassword)) {
            return false;
        }

        if (!hash_equals($expectedUsername, $username) || !hash_equals($expectedPassword, $password)) {
            return false;
        }

        session_regenerate_id(true);
        $_SESSION['authenticated'] = true;
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        return true;
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, [
                'path' => $params['path'],
                'domain' => $params['domain'],
                'secure' => $params['secure'],
                'httponly' => $params['httponly'],
                'samesite' => $params['samesite'] ?? 'Lax',
            ]);
        }
        session_destroy();
    }
}
