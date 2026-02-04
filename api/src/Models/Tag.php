<?php
declare(strict_types=1);

require_once __DIR__ . '/../Database.php';

class Tag
{
    public static function all(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT * FROM tags ORDER BY name");
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM tags WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findByName(string $name): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM tags WHERE name = :name");
        $stmt->execute(['name' => $name]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(array $data): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("INSERT INTO tags (name) VALUES (:name)");
        $stmt->execute(['name' => $data['name']]);
        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE tags SET name = :name WHERE id = :id");
        return $stmt->execute([
            'name' => $data['name'],
            'id' => $id,
        ]);
    }

    public static function deleteExpenseRelations(int $tagId): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM expense_tags WHERE tag_id = :tag_id");
        return $stmt->execute(['tag_id' => $tagId]);
    }

    public static function delete(int $id): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM tags WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
