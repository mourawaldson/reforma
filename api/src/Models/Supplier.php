<?php
require_once __DIR__ . '/../Database.php';

class Supplier
{
    public static function all(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT * FROM suppliers ORDER BY name ASC");
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM suppliers WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(array $data): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO suppliers (name, type, cpf_cnpj)
            VALUES (:name, :type, :cpf_cnpj)
        ");
        $stmt->execute([
            'name'     => $data['name'],
            'type'     => $data['type'] ?? null,
            'cpf_cnpj' => $data['cpf_cnpj'] ?? null,
        ]);
        return (int)$pdo->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            UPDATE suppliers
            SET name = :name,
                type = :type,
                cpf_cnpj = :cpf_cnpj
            WHERE id = :id
        ");
        return $stmt->execute([
            'name'     => $data['name'],
            'type'     => $data['type'] ?? null,
            'cpf_cnpj' => $data['cpf_cnpj'] ?? null,
            'id'       => $id,
        ]);
    }

    public static function delete(int $id): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM suppliers WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
