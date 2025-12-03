<?php
require_once __DIR__ . '/../Database.php';

class DashboardController
{
    public function category()
    {
        $pdo = Database::getConnection();

        // Parâmetros opcionais
        $year       = isset($_GET['year']) ? (int)$_GET['year'] : null;
        $supplierId = !empty($_GET['supplier_id']) ? (int)$_GET['supplier_id'] : null;
        $tagId      = !empty($_GET['tag_id']) ? (int)$_GET['tag_id'] : null;

        $sql = "SELECT 
                    e.calendar_year,
                    c.id   AS category_id,
                    c.name AS category_name,
                    SUM(e.amount_paid) AS total_paid,
                    SUM(e.amount_nf)   AS total_nf
                FROM expenses e
                JOIN categories c ON c.id = e.category_id";

        $where  = [];
        $params = [];

        if ($year) {
            $where[]          = "e.calendar_year = :year";
            $params['year']   = $year;
        }

        if ($supplierId) {
            $where[]              = "e.supplier_id = :supplier_id";
            $params['supplier_id'] = $supplierId;
        }

        if ($tagId) {
            $sql .= " JOIN expense_tags et ON et.expense_id = e.id";
            $where[]            = "et.tag_id = :tag_id";
            $params['tag_id']   = $tagId;
        }

        if ($where) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        $sql .= " GROUP BY e.calendar_year, c.id, c.name
                  ORDER BY e.calendar_year ASC, total_paid DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        // Agrupar por ano
        $years = [];

        foreach ($rows as $r) {
            $yearKey = (int)$r['calendar_year'];

            if (!isset($years[$yearKey])) {
                $years[$yearKey] = [
                    'summary' => [
                        'total_paid' => 0.0,
                        'total_nf'   => 0.0,
                    ],
                    'data' => [],
                ];
            }

            $totalPaid = (float)$r['total_paid'];
            $totalNf   = (float)$r['total_nf'];

            // Atualiza resumo por ano
            $years[$yearKey]['summary']['total_paid'] += $totalPaid;
            $years[$yearKey]['summary']['total_nf']   += $totalNf;

            // Adiciona linha de categoria
            $years[$yearKey]['data'][] = [
                'category_id'   => (int)$r['category_id'],
                'category_name' => $r['category_name'],
                'total_paid'    => $totalPaid,
                'total_nf'      => $totalNf,
                // percentage_paid será calculado depois
            ];
        }

        // Calcula o percentage_paid dentro de cada ano
        foreach ($years as $yearKey => &$yearData) {
            $summaryTotalPaid = (float)$yearData['summary']['total_paid'];

            foreach ($yearData['data'] as &$row) {
                $row['percentage_paid'] = $summaryTotalPaid > 0
                    ? round(($row['total_paid'] / $summaryTotalPaid) * 100, 2)
                    : 0.0;
            }
        }
        unset($yearData, $row); // segurança

        $response = [
            // Mantém compatível com a view nova
            'years' => $years,
        ];

        header('Content-Type: application/json');
        echo json_encode($response);
    }
}
