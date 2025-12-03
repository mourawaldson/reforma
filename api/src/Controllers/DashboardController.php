<?php
require_once __DIR__ . '/../Database.php';

class DashboardController
{
    public function category()
    {
        $pdo = Database::getConnection();
        $year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
        $supplierId = !empty($_GET['supplier_id']) ? (int)$_GET['supplier_id'] : null;
        $tagId = !empty($_GET['tag_id']) ? (int)$_GET['tag_id'] : null;

        $sql = "SELECT c.id AS category_id, c.name AS category_name,
                       SUM(e.amount_paid) AS total_paid,
                       SUM(e.amount_nf)   AS total_nf
                FROM expenses e
                JOIN categories c ON c.id = e.category_id";
        $where = ["e.calendar_year = :year"];
        $params = ['year' => $year];

        if ($supplierId) {
            $where[] = "e.supplier_id = :supplier_id";
            $params['supplier_id'] = $supplierId;
        }
        if ($tagId) {
            $sql .= " JOIN expense_tags et ON et.expense_id = e.id";
            $where[] = "et.tag_id = :tag_id";
            $params['tag_id'] = $tagId;
        }

        if ($where) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        $sql .= " GROUP BY c.id, c.name ORDER BY total_paid DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        $summaryTotalPaid = 0;
        $summaryTotalNf = 0;
        foreach ($rows as $r) {
            $summaryTotalPaid += (float)$r['total_paid'];
            $summaryTotalNf += (float)$r['total_nf'];
        }

        foreach ($rows as &$r) {
            $r['total_paid'] = (float)$r['total_paid'];
            $r['total_nf'] = (float)$r['total_nf'];
            $r['percentage_paid'] = $summaryTotalPaid > 0
                ? round(((float)$r['total_paid'] / $summaryTotalPaid) * 100, 2)
                : 0.0;
        }

        $response = [
            'year' => $year,
            'summary' => [
                'total_paid' => $summaryTotalPaid,
                'total_nf' => $summaryTotalNf,
            ],
            'data' => $rows,
        ];

        header('Content-Type: application/json');
        echo json_encode($response);
    }
}
