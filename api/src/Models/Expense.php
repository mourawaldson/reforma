<?php
require_once __DIR__ . '/../Database.php';

class Expense
{
    public static function list(array $filters = []): array
    {
        $pdo = Database::getConnection();
        $sql = "SELECT e.*, c.name AS category_name, s.name AS supplier_name
                FROM expenses e
                JOIN categories c ON e.category_id = c.id
                LEFT JOIN suppliers s ON e.supplier_id = s.id";
        $where = [];
        $params = [];

        if (!empty($filters['year'])) {
            $where[] = 'e.calendar_year = :year';
            $params['year'] = (int)$filters['year'];
        }
        if (!empty($filters['category_id'])) {
            $where[] = 'e.category_id = :category_id';
            $params['category_id'] = (int)$filters['category_id'];
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
        $sql .= " ORDER BY e.date ASC, s.id ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        // Carregar tags por despesa
        $ids = array_column($rows, 'id');
        $tagsByExpense = [];
        if ($ids) {
            $in = implode(',', array_fill(0, count($ids), '?'));
            $tagSql = "SELECT et.expense_id, t.id, t.name
                       FROM expense_tags et
                       JOIN tags t ON t.id = et.tag_id
                       WHERE et.expense_id IN ($in)";
            $tagStmt = $pdo->prepare($tagSql);
            $tagStmt->execute($ids);
            while ($t = $tagStmt->fetch()) {
                $tagsByExpense[$t['expense_id']][] = [
                    'id' => (int)$t['id'],
                    'name' => $t['name'],
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
        $stmt = $pdo->prepare("SELECT e.*, c.name AS category_name, s.name AS supplier_name
                               FROM expenses e
                               JOIN categories c ON e.category_id = c.id
                               LEFT JOIN suppliers s ON e.supplier_id = s.id
                               WHERE e.id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }

        $tagStmt = $pdo->prepare("SELECT t.id, t.name
                                  FROM expense_tags et
                                  JOIN tags t ON t.id = et.tag_id
                                  WHERE et.expense_id = :id");
        $tagStmt->execute(['id' => $id]);
        $tags = $tagStmt->fetchAll();
        $row['tags'] = array_map(fn($t) => [
            'id' => (int)$t['id'],
            'name' => $t['name']
        ], $tags);

        return $row;
    }

    public static function create(array $data, array $tags = []): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("INSERT INTO expenses
            (category_id, supplier_id, date, description, amount_nf, amount_paid, calendar_year)
            VALUES (:category_id, :supplier_id, :date, :description, :amount_nf, :amount_paid, :calendar_year)");
        $stmt->execute([
            'category_id'   => (int)$data['category_id'],
            'supplier_id'   => $data['supplier_id'] !== null ? (int)$data['supplier_id'] : null,
            'date'          => $data['date'],
            'description'   => $data['description'],
            'amount_nf'     => $data['amount_nf'] !== null ? $data['amount_nf'] : null,
            'amount_paid'   => $data['amount_paid'],
            'calendar_year' => (int)$data['calendar_year'],
        ]);
        $id = (int)$pdo->lastInsertId();
        self::setTags($id, $tags);
        return $id;
    }

    public static function update(int $id, array $data, array $tags = []): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE expenses SET
            category_id = :category_id,
            supplier_id = :supplier_id,
            date = :date,
            description = :description,
            amount_nf = :amount_nf,
            amount_paid = :amount_paid,
            calendar_year = :calendar_year
            WHERE id = :id");
        $ok = $stmt->execute([
            'category_id'   => (int)$data['category_id'],
            'supplier_id'   => $data['supplier_id'] !== null ? (int)$data['supplier_id'] : null,
            'date'          => $data['date'],
            'description'   => $data['description'],
            'amount_nf'     => $data['amount_nf'] !== null ? $data['amount_nf'] : null,
            'amount_paid'   => $data['amount_paid'],
            'calendar_year' => (int)$data['calendar_year'],
            'id'            => $id,
        ]);
        if ($ok) {
            self::setTags($id, $tags);
        }
        return $ok;
    }

    public static function delete(int $id): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM expenses WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public static function setTags(int $expenseId, array $tagIds): void
    {
        $pdo = Database::getConnection();
        $pdo->prepare("DELETE FROM expense_tags WHERE expense_id = :id")
            ->execute(['id' => $expenseId]);

        if (!$tagIds) {
            return;
        }

        $stmt = $pdo->prepare("INSERT INTO expense_tags (expense_id, tag_id) VALUES (:expense_id, :tag_id)");
        foreach ($tagIds as $tagId) {
            $stmt->execute([
                'expense_id' => $expenseId,
                'tag_id'     => (int)$tagId,
            ]);
        }
    }
}
