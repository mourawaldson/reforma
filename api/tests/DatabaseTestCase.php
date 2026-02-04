<?php
declare(strict_types=1);

namespace Tests;

use Database;
use PDO;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/Database.php';

abstract class DatabaseTestCase extends TestCase
{
    protected PDO $pdo;

    protected function setUp(): void
    {
        // Conecta ao banco
        $this->pdo = Database::getConnection();

        // Inicia transação para isolamento
        $this->pdo->beginTransaction();
    }

    protected function tearDown(): void
    {
        // Reverte alterações após o teste
        if ($this->pdo->inTransaction()) {
            $this->pdo->rollBack();
        }
    }
}
