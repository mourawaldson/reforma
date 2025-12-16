<?php
require_once __DIR__ . '/../Database.php';

class Supplier
{
    public static function all(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("
            SELECT
                id,
                name AS display_name,
                cpf_cnpj
            FROM suppliers
            ORDER BY name ASC
        ");
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM suppliers WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function findByCpfCnpj(string $cpfCnpj): ?array
    {
        $cpfCnpj = preg_replace('/\D/', '', $cpfCnpj);

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
        SELECT id
        FROM suppliers
        WHERE REPLACE(REPLACE(REPLACE(cpf_cnpj, '.', ''), '-', ''), '/', '') = :cpf_cnpj
        LIMIT 1
    ");
        $stmt->execute(['cpf_cnpj' => $cpfCnpj]);

        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(array $data): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO suppliers (name, company_name, cpf_cnpj)
            VALUES (:name, :company_name, :cpf_cnpj)
        ");
        $stmt->execute([
            'name'         => $data['name'],
            'company_name' => $data['company_name'],
            'cpf_cnpj'     => $data['cpf_cnpj'],
        ]);
        return (int)$pdo->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            UPDATE suppliers
            SET
                name = :name,
                company_name = :company_name,
                cpf_cnpj = :cpf_cnpj
            WHERE id = :id
        ");
        return $stmt->execute([
            'name'         => $data['name'],
            'company_name' => $data['company_name'],
            'cpf_cnpj'     => $data['cpf_cnpj'],
            'id'           => $id,
        ]);
    }

    public static function delete(int $id): bool
    {
        $pdo = Database::getConnection();
        $pdo->beginTransaction();

        $pdo->prepare(
            "UPDATE expenses SET supplier_id = NULL WHERE supplier_id = :id"
        )->execute(['id' => $id]);

        $ok = $pdo->prepare(
            "DELETE FROM suppliers WHERE id = :id"
        )->execute(['id' => $id]);

        $pdo->commit();
        return $ok;
    }
}
