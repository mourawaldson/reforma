<?php
require_once __DIR__ . '/../Database.php';

class DashboardController
{
    /**
     * Dashboard por categoria, agrupado por ano-calendário, com:
     * - years: dados confirmados por ano/categoria
     * - pending: resumo de despesas pendentes (não confirmadas)
     * - supplier_summary: resumo por fornecedor (confirmadas, todos os anos)
     */
    public function category()
    {
        $pdo = Database::getConnection();

        $supplierId = !empty($_GET['supplier_id']) ? (int)$_GET['supplier_id'] : null;
        $tagId      = !empty($_GET['tag_id'])      ? (int)$_GET['tag_id']      : null;

        // =========================
        // 1) DESPESAS CONFIRMADAS AGRUPADAS POR ANO/CATEGORIA
        // =========================
        $sql = "SELECT 
                    e.calendar_year,
                    c.id   AS category_id,
                    c.name AS category_name,
                    SUM(e.amount_paid) AS total_paid,
                    SUM(e.amount_nf)   AS total_nf
                FROM expenses e
                JOIN categories c ON c.id = e.category_id";

        $where  = ["e.is_confirmed = 1"];
        $params = [];

        if ($supplierId) {
            $where[]               = "e.supplier_id = :supplier_id";
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

            $years[$yearKey]['summary']['total_paid'] += $totalPaid;
            $years[$yearKey]['summary']['total_nf']   += $totalNf;

            $years[$yearKey]['data'][] = [
                'category_id'   => (int)$r['category_id'],
                'category_name' => $r['category_name'],
                'total_paid'    => $totalPaid,
                'total_nf'      => $totalNf,
                // percentage_paid será calculado abaixo
            ];
        }

        // Calcula percentage_paid por ano
        foreach ($years as $yearKey => &$yearData) {
            $summaryTotalPaid = (float)$yearData['summary']['total_paid'];

            foreach ($yearData['data'] as &$row) {
                $row['percentage_paid'] = $summaryTotalPaid > 0
                    ? round(($row['total_paid'] / $summaryTotalPaid) * 100, 2)
                    : 0.0;
            }
        }
        unset($yearData, $row);

        // =========================
        // 2) RESUMO DE DESPESAS PENDENTES (NÃO CONFIRMADAS)
        // =========================
        $sqlPending = "SELECT 
                            COUNT(*)            AS pending_count,
                            SUM(e.amount_paid)  AS total_paid,
                            SUM(e.amount_nf)    AS total_nf
                       FROM expenses e";

        $wherePending  = ["e.is_confirmed = 0"];
        $paramsPending = [];

        if ($supplierId) {
            $wherePending[]             = "e.supplier_id = :supplier_id";
            $paramsPending['supplier_id'] = $supplierId;
        }

        if ($tagId) {
            $sqlPending .= " JOIN expense_tags et2 ON et2.expense_id = e.id";
            $wherePending[]           = "et2.tag_id = :tag_id";
            $paramsPending['tag_id']  = $tagId;
        }

        if ($wherePending) {
            $sqlPending .= " WHERE " . implode(' AND ', $wherePending);
        }

        $stmtPending = $pdo->prepare($sqlPending);
        $stmtPending->execute($paramsPending);
        $pendingRow = $stmtPending->fetch() ?: [
            'pending_count' => 0,
            'total_paid'    => 0,
            'total_nf'      => 0,
        ];

        $pendingSummary = [
            'count'      => (int)($pendingRow['pending_count'] ?? 0),
            'total_paid' => (float)($pendingRow['total_paid'] ?? 0),
            'total_nf'   => (float)($pendingRow['total_nf'] ?? 0),
        ];
        $pendingSummary['diff_nf_paid'] = $pendingSummary['total_nf'] - $pendingSummary['total_paid'];

        // =========================
        // 3) RESUMO POR FORNECEDOR (CONFIRMADAS, TODOS OS ANOS)
        // =========================
        $sqlSup = "SELECT 
                        COALESCE(s.name, '(Sem fornecedor)') AS supplier_name,
                        SUM(e.amount_nf)   AS total_nf,
                        SUM(e.amount_paid) AS total_paid
                   FROM expenses e
                   LEFT JOIN suppliers s ON s.id = e.supplier_id";

        $whereSup  = ["e.is_confirmed = 1"];
        $paramsSup = [];

        if ($supplierId) {
            $whereSup[]             = "e.supplier_id = :supplier_id";
            $paramsSup['supplier_id'] = $supplierId;
        }

        if ($tagId) {
            $sqlSup .= " JOIN expense_tags et3 ON et3.expense_id = e.id";
            $whereSup[]            = "et3.tag_id = :tag_id";
            $paramsSup['tag_id']   = $tagId;
        }

        if ($whereSup) {
            $sqlSup .= " WHERE " . implode(' AND ', $whereSup);
        }

        $sqlSup .= " GROUP BY supplier_name
                     ORDER BY total_paid DESC";

        $stmtSup = $pdo->prepare($sqlSup);
        $stmtSup->execute($paramsSup);
        $supRows = $stmtSup->fetchAll();

        $supplierSummary = [];
        foreach ($supRows as $r) {
            $supplierSummary[] = [
                'supplier_name' => $r['supplier_name'],
                'total_nf'      => (float)$r['total_nf'],
                'total_paid'    => (float)$r['total_paid'],
            ];
        }

        // =========================
        // 4) RESPOSTA FINAL
        // =========================
        $response = [
            'years'            => $years,
            'pending'          => $pendingSummary,
            'supplier_summary' => $supplierSummary,
        ];

        header('Content-Type: application/json');
        echo json_encode($response);
    }
}
