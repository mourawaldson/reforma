<?php
require_once __DIR__ . '/../Database.php';

class DashboardController
{
    public function overview()
    {
        $pdo = Database::getConnection();

        // Total acumulado (confirmadas)
        $total = (float)$pdo->query("
            SELECT SUM(amount_paid)
            FROM expenses
            WHERE is_confirmed = 1
        ")->fetchColumn();

        // Total por ano
        $stmt = $pdo->query("
            SELECT
                calendar_year,
                SUM(amount_paid) AS total_paid
            FROM expenses
            WHERE is_confirmed = 1
            GROUP BY calendar_year
            ORDER BY calendar_year
        ");

        $years = [];
        foreach ($stmt->fetchAll() as $r) {
            $years[$r['calendar_year']] = (float)$r['total_paid'];
        }

        header('Content-Type: application/json');
        echo json_encode([
            'total' => $total,
            'years' => $years
        ]);
    }

    public function expenses()
    {
        $pdo = Database::getConnection();

        // =========================
        // CONFIRMADAS POR ANO
        // =========================
        $stmt = $pdo->query("
            SELECT
                calendar_year,
                SUM(amount_paid) AS total_paid,
                SUM(amount_nf)   AS total_nf
            FROM expenses
            WHERE is_confirmed = 1
            GROUP BY calendar_year
            ORDER BY calendar_year
        ");

        $years = [];
        foreach ($stmt->fetchAll() as $r) {
            $years[$r['calendar_year']] = [
                'summary' => [
                    'total_paid'   => (float)$r['total_paid'],
                    'total_nf'     => (float)$r['total_nf'],
                    'diff_nf_paid' => (float)$r['total_nf'] - (float)$r['total_paid'],
                ]
            ];
        }

        // =========================
        // PENDENTES
        // =========================
        $stmt = $pdo->query("
            SELECT
                COUNT(*) AS count,
                SUM(amount_paid) AS total_paid
            FROM expenses
            WHERE is_confirmed = 0
        ");

        $pendingSummary = $stmt->fetch();

        $stmt = $pdo->query("
            SELECT
                calendar_year,
                COUNT(*) AS count,
                SUM(amount_paid) AS total_paid
            FROM expenses
            WHERE is_confirmed = 0
            GROUP BY calendar_year
            ORDER BY calendar_year
        ");

        $pendingYears = [];
        foreach ($stmt->fetchAll() as $r) {
            $pendingYears[$r['calendar_year']] = [
                'count'      => (int)$r['count'],
                'total_paid' => (float)$r['total_paid'],
            ];
        }

        header('Content-Type: application/json');
        echo json_encode([
            'years' => $years,
            'pending' => [
                'summary' => [
                    'count'      => (int)$pendingSummary['count'],
                    'total_paid' => (float)$pendingSummary['total_paid'],
                ],
                'years' => $pendingYears
            ]
        ]);
    }

    // ==================================================
    // NOVO ENDPOINT: DESCONTOS
    // ==================================================
    public function discounts()
    {
        $pdo = Database::getConnection();

        $sql = "
            SELECT
                e.date,
                s.name AS supplier_name,
                e.description,
                e.amount_paid,
                e.additional_discount
            FROM expenses e
            LEFT JOIN suppliers s ON s.id = e.supplier_id
            WHERE
                e.is_confirmed = 1
                AND e.additional_discount IS NOT NULL
            ORDER BY e.date ASC
        ";

        $stmt = $pdo->query($sql);
        $rows = $stmt->fetchAll();

        $items = [];
        $totalPaid = 0;
        $totalDiscount = 0;
        $totalVet = 0;

        foreach ($rows as $r) {
            $vet = (float)$r['amount_paid'] - (float)$r['additional_discount'];

            $items[] = [
                'date'                => $r['date'],
                'supplier_name'       => $r['supplier_name'],
                'description'         => $r['description'],
                'amount_paid'         => (float)$r['amount_paid'],
                'additional_discount' => (float)$r['additional_discount'],
                'vet'                 => $vet,
            ];

            $totalPaid     += (float)$r['amount_paid'];
            $totalDiscount += (float)$r['additional_discount'];
            $totalVet      += $vet;
        }

        header('Content-Type: application/json');
        echo json_encode([
            'items' => $items,
            'totals' => [
                'amount_paid'         => $totalPaid,
                'additional_discount' => $totalDiscount,
                'vet'                 => $totalVet,
            ]
        ]);
    }

    public function tags()
    {
        $pdo = Database::getConnection();

        // =========================
        // TOTAL PAGO GLOBAL (confirmadas)
        // =========================
        $totalPaid = (float)$pdo
            ->query("SELECT SUM(amount_paid) FROM expenses WHERE is_confirmed = 1")
            ->fetchColumn();

        // =========================
        // TOTAL PAGO POR ANO → TAG
        // =========================
        $sql = "
            SELECT
                e.calendar_year,
                t.name AS tag_name,
                COUNT(*) AS count,
                SUM(e.amount_paid) AS total_paid
            FROM expenses e
            JOIN expense_tags et ON et.expense_id = e.id
            JOIN tags t ON t.id = et.tag_id
            WHERE e.is_confirmed = 1
            GROUP BY e.calendar_year, t.id
            ORDER BY e.calendar_year ASC, total_paid DESC
        ";

        $stmt = $pdo->query($sql);
        $rows = $stmt->fetchAll();

        $years = [];
        $table = [];

        foreach ($rows as $r) {
            $year = (int)$r['calendar_year'];

            $years[$year]['tags'][] = [
                'tag'        => $r['tag_name'],
                'count'      => (int)$r['count'],
                'total_paid' => (float)$r['total_paid'],
            ];

            $table[] = [
                'year'       => $year,
                'tag'        => $r['tag_name'],
                'count'      => (int)$r['count'],
                'total_paid' => (float)$r['total_paid'],
            ];
        }

        header('Content-Type: application/json');
        echo json_encode([
            'summary' => [
                'total_paid' => $totalPaid
            ],
            'years' => $years,
            'table' => $table
        ]);
    }

    public function suppliers()
    {
        $pdo = Database::getConnection();

        // =========================
        // TOTAL PAGO GLOBAL (confirmadas)
        // =========================
        $totalPaid = (float)$pdo
            ->query("SELECT SUM(amount_paid) FROM expenses WHERE is_confirmed = 1")
            ->fetchColumn();

        // =========================
        // TOTAL PAGO POR ANO → SUPPLIER
        // =========================
        $sql = "
            SELECT
                e.calendar_year,
                s.name AS supplier_name,
                COUNT(*) AS count,
                SUM(e.amount_paid) AS total_paid
            FROM expenses e
            JOIN suppliers s ON s.id = e.supplier_id
            WHERE e.is_confirmed = 1
            GROUP BY e.calendar_year, s.id
            ORDER BY e.calendar_year ASC, total_paid DESC
        ";

        $stmt = $pdo->query($sql);
        $rows = $stmt->fetchAll();

        $years = [];
        $table = [];

        foreach ($rows as $r) {
            $year = (int)$r['calendar_year'];

            $years[$year]['suppliers'][] = [
                'supplier'   => $r['supplier_name'],
                'count'      => (int)$r['count'],
                'total_paid' => (float)$r['total_paid'],
            ];

            $table[] = [
                'year'       => $year,
                'supplier'   => $r['supplier_name'],
                'count'      => (int)$r['count'],
                'total_paid' => (float)$r['total_paid'],
            ];
        }

        header('Content-Type: application/json');
        echo json_encode([
            'summary' => [
                'total_paid' => $totalPaid
            ],
            'years' => $years,
            'table' => $table
        ]);
    }
}
