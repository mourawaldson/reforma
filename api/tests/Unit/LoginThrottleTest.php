<?php
declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../backoffice/src/Auth.php';

class LoginThrottleTest extends TestCase
{
    public function testBlocksClientAfterFiveFailedLoginAttemptsAndCanResetAfterSuccess(): void
    {
        $originalIp = $_SERVER['REMOTE_ADDR'] ?? null;
        $_SERVER['REMOTE_ADDR'] = 'test-' . bin2hex(random_bytes(8));

        try {
            \SessionAuth::clearLoginThrottle();
            self::assertFalse(\SessionAuth::isLoginThrottled());

            for ($attempt = 0; $attempt < 5; $attempt++) {
                \SessionAuth::recordFailedLogin();
            }

            self::assertTrue(\SessionAuth::isLoginThrottled());

            \SessionAuth::clearLoginThrottle();
            self::assertFalse(\SessionAuth::isLoginThrottled());
        } finally {
            if ($originalIp === null) {
                unset($_SERVER['REMOTE_ADDR']);
            } else {
                $_SERVER['REMOTE_ADDR'] = $originalIp;
            }
        }
    }
}
