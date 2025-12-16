<?php
require_once __DIR__ . '/../Database.php';

class Expense
{
    public static function list(array $filters = []): array
    {
        $pdo = Database::getConnection();
        $sql = "SELECT e.*, s.name AS supplier_name
                FROM expenses e
                LEFT JOIN suppliers s ON e.supplier_id = s.id";
        $where = [];
        $params = [];

        if (!empty($filters['year'])) {
            $where[] = 'e.calendar_year = :year';
            $params['year'] = (int)$filters['year'];
        }
        if (!empty($filters['supplier_id'])) {
            $where[] = 'e.supplier_id = :supplier_id';
            $params['supplier_id'] = (int)$filters['supplier_id'];
        }
        if (!empty($filters['tag_id'])) {
            $sql .= " JOIN expense_tags et ON et.expense_id = e.id";
            $where[] = 'et.tag_id = :tag_id';
            $params['tag_id'] = (int)$filters['tag_id'];
        }
        if (!empty($filters['q'])) {
            $where[] = 'e.description LIKE :q';
            $params['q'] = '%' . $filters['q'] . '%';
        }

        if ($where) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        $sql .= " ORDER BY e.date ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        $ids = array_column($rows, 'id');
        $tagsByExpense = [];

        if ($ids) {
            $in = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $pdo->prepare("
                SELECT et.expense_id, t.id, t.name
                FROM expense_tags et
                JOIN tags t ON t.id = et.tag_id
                WHERE et.expense_id IN ($in)
            ");
            $stmt->execute($ids);

            while ($t = $stmt->fetch()) {
                $tagsByExpense[$t['expense_id']][] = [
                    'id' => (int)$t['id'],
                    'name' => $t['name']
                ];
            }
        }

        foreach ($rows as &$r) {
            $r['tags'] = $tagsByExpense[$r['id']] ?? [];
        }

        return $rows;
    }

    public static function find(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT e.*, s.name AS supplier_name
            FROM expenses e
            LEFT JOIN suppliers s ON e.supplier_id = s.id
            WHERE e.id = :id
        ");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        if (!$row) return null;

        $stmt = $pdo->prepare("
            SELECT t.id, t.name
            FROM expense_tags et
            JOIN tags t ON t.id = et.tag_id
            WHERE et.expense_id = :id
        ");
        $stmt->execute(['id' => $id]);
        $row['tags'] = $stmt->fetchAll();

        return $row;
    }

    public static function create(array $data, array $tags): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO expenses
            (supplier_id, date, description, amount_nf, amount_paid, additional_discount, calendar_year, is_confirmed)
            VALUES (:supplier_id, :date, :description, :amount_nf, :amount_paid, :additional_discount, :calendar_year, :is_confirmed)
        ");
        $stmt->execute($data);

        $id = (int)$pdo->lastInsertId();
        self::setTags($id, $tags);
        return $id;
    }

    public static function update(int $id, array $data, array $tags): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            UPDATE expenses SET
                supplier_id = :supplier_id,
                date = :date,
                description = :description,
                amount_nf = :amount_nf,
                amount_paid = :amount_paid,
                additional_discount = :additional_discount,
                calendar_year = :calendar_year
            WHERE id = :id
        ");
        $data['id'] = $id;
        $stmt->execute($data);
        self::setTags($id, $tags);
    }

    public static function delete(int $id): void
    {
        Database::getConnection()
            ->prepare("DELETE FROM expenses WHERE id = :id")
            ->execute(['id' => $id]);
    }

    private static function setTags(int $expenseId, array $tagIds): void
    {
        $pdo = Database::getConnection();
        $pdo->prepare("DELETE FROM expense_tags WHERE expense_id = :id")
            ->execute(['id' => $expenseId]);

        if (!$tagIds) return;

        $stmt = $pdo->prepare("
            INSERT INTO expense_tags (expense_id, tag_id)
            VALUES (:expense_id, :tag_id)
        ");

        foreach ($tagIds as $tagId) {
            $stmt->execute([
                'expense_id' => $expenseId,
                'tag_id' => (int)$tagId
            ]);
        }
    }
}
