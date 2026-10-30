<?php
declare(strict_types=1);

class SessionAuth
{
    private const LOGIN_MAX_FAILURES = 5;
    private const LOGIN_WINDOW_SECONDS = 900;

    public static function start(string $sessionName): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        session_name($sessionName);
        session_set_cookie_params([
            'httponly' => true,
            'secure' => getenv('SESSION_COOKIE_SECURE') === 'true'
                || (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
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

    public static function isLoginThrottled(): bool
    {
        $result = self::updateLoginThrottle(static function (array &$state, int $now): bool {
            self::resetExpiredLoginWindow($state, $now);
            return (int)($state['blocked_until'] ?? 0) > $now
                || (int)($state['failures'] ?? 0) >= self::LOGIN_MAX_FAILURES;
        });

        return $result !== false;
    }

    public static function recordFailedLogin(): void
    {
        self::updateLoginThrottle(static function (array &$state, int $now): bool {
            self::resetExpiredLoginWindow($state, $now);
            $state['failures'] = (int)($state['failures'] ?? 0) + 1;
            if ($state['failures'] >= self::LOGIN_MAX_FAILURES) {
                $state['blocked_until'] = $now + self::LOGIN_WINDOW_SECONDS;
            }
            return true;
        });
    }

    public static function clearLoginThrottle(): void
    {
        self::updateLoginThrottle(static function (array &$state, int $now): bool {
            $state = ['window_started_at' => $now, 'failures' => 0, 'blocked_until' => 0];
            return true;
        });
    }

    private static function resetExpiredLoginWindow(array &$state, int $now): void
    {
        if ((int)($state['window_started_at'] ?? 0) + self::LOGIN_WINDOW_SECONDS <= $now) {
            $state = ['window_started_at' => $now, 'failures' => 0, 'blocked_until' => 0];
        }
    }

    private static function updateLoginThrottle(callable $callback): mixed
    {
        $clientIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $filePath = sys_get_temp_dir() . '/reforma-login-' . hash('sha256', (string)$clientIp) . '.json';
        $file = @fopen($filePath, 'c+');
        if ($file === false || !flock($file, LOCK_EX)) {
            if (is_resource($file)) {
                fclose($file);
            }
            return null;
        }

        $savedState = json_decode(stream_get_contents($file) ?: '', true);
        $state = is_array($savedState)
            ? $savedState
            : ['window_started_at' => time(), 'failures' => 0, 'blocked_until' => 0];
        $result = $callback($state, time());

        ftruncate($file, 0);
        rewind($file);
        fwrite($file, json_encode($state, JSON_THROW_ON_ERROR));
        fflush($file);
        flock($file, LOCK_UN);
        fclose($file);

        return $result;
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
