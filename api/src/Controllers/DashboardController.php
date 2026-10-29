<?php
declare(strict_types=1);

require_once __DIR__ . '/../Database.php';

class DashboardController
{
    public function overview()
    {
        $pdo = Database::getConnection();

        // Total acumulado (confirmadas)
        $total = (float) $pdo->query("
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
            $years[$r['calendar_year']] = (float) $r['total_paid'];
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
                    'total_paid' => (float) $r['total_paid'],
                    'total_nf' => (float) $r['total_nf'],
                    'diff_nf_paid' => (float) $r['total_nf'] - (float) $r['total_paid'],
                ]
            ];
        }

        // =========================
        // PENDENTES
        // =========================
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
        $pendingCount = 0;
        $pendingTotalPaid = 0.0;
        foreach ($stmt->fetchAll() as $r) {
            $count = (int) $r['count'];
            $totalPaid = (float) $r['total_paid'];
            $pendingCount += $count;
            $pendingTotalPaid += $totalPaid;
            $pendingYears[$r['calendar_year']] = [
                'count' => $count,
                'total_paid' => $totalPaid,
            ];
        }

        // =========================
        // DESPESAS CONFIRMADAS COM A TAG AUMENTO DE CAPITAL
        // =========================
        $stmt = $pdo->prepare("
            SELECT
                e.date,
                e.calendar_year,
                e.description,
                e.amount_paid,
                s.name AS supplier_name
            FROM expenses e
            JOIN expense_tags et ON et.expense_id = e.id
            JOIN tags t ON t.id = et.tag_id
            LEFT JOIN suppliers s ON s.id = e.supplier_id
            WHERE e.is_confirmed = 1
              AND t.name = :tag_name
            ORDER BY e.date ASC, e.id ASC
        ");
        $stmt->execute(['tag_name' => 'Aumento de Capital']);

        $capitalIncreaseItems = [];
        $capitalIncreaseYears = [];
        $capitalIncreaseTotal = 0.0;

        foreach ($stmt->fetchAll() as $r) {
            $year = (int) $r['calendar_year'];
            $amountPaid = (float) $r['amount_paid'];

            $capitalIncreaseTotal += $amountPaid;
            $capitalIncreaseYears[$year] = ($capitalIncreaseYears[$year] ?? 0.0) + $amountPaid;
            $capitalIncreaseItems[] = [
                'date' => $r['date'],
                'supplier_name' => $r['supplier_name'],
                'description' => $r['description'],
                'amount_paid' => $amountPaid,
            ];
        }
        ksort($capitalIncreaseYears, SORT_NUMERIC);
        $discounts = $this->getDiscountsData($pdo);

        header('Content-Type: application/json');
        echo json_encode([
            'years' => $years,
            'pending' => [
                'summary' => [
                    'count' => $pendingCount,
                    'total_paid' => $pendingTotalPaid,
                ],
                'years' => $pendingYears
            ],
            'capital_increase' => [
                'summary' => [
                    'count' => count($capitalIncreaseItems),
                    'total_paid' => $capitalIncreaseTotal,
                ],
                'years' => $capitalIncreaseYears,
                'items' => $capitalIncreaseItems,
            ],
            'discounts' => $discounts,
        ]);
    }

    // ==================================================
    // NOVO ENDPOINT: DESCONTOS
    // ==================================================
    public function discounts()
    {
        $pdo = Database::getConnection();

        header('Content-Type: application/json');
        echo json_encode($this->getDiscountsData($pdo));
    }

    private function getDiscountsData(PDO $pdo): array
    {

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
            $vet = (float) $r['amount_paid'] - (float) $r['additional_discount'];

            $items[] = [
                'date' => $r['date'],
                'supplier_name' => $r['supplier_name'],
                'description' => $r['description'],
                'amount_paid' => (float) $r['amount_paid'],
                'additional_discount' => (float) $r['additional_discount'],
                'vet' => $vet,
            ];

            $totalPaid += (float) $r['amount_paid'];
            $totalDiscount += (float) $r['additional_discount'];
            $totalVet += $vet;
        }

        return [
            'items' => $items,
            'totals' => [
                'amount_paid' => $totalPaid,
                'additional_discount' => $totalDiscount,
                'vet' => $totalVet,
            ]
        ];
    }

    public function tags()
    {
        $pdo = Database::getConnection();

        // =========================
        // TOTAL PAGO GLOBAL (confirmadas)
        // =========================
        $totalPaid = (float) $pdo
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
            $year = (int) $r['calendar_year'];

            $years[$year]['tags'][] = [
                'tag' => $r['tag_name'],
                'count' => (int) $r['count'],
                'total_paid' => (float) $r['total_paid'],
            ];

            $table[] = [
                'year' => $year,
                'tag' => $r['tag_name'],
                'count' => (int) $r['count'],
                'total_paid' => (float) $r['total_paid'],
            ];
        }

        header('Content-Type: application/json');
        echo json_encode([
            'summary' => [
                'total_paid' => $totalPaid
            ],
            'years' => $years,
            'table' => $table,
            'unused' => $this->getUnusedTags($pdo),
        ]);
    }

    private function getUnusedTags(PDO $pdo): array
    {
        $stmt = $pdo->query("
            SELECT t.name
            FROM tags t
            LEFT JOIN expense_tags et ON et.tag_id = t.id
            WHERE et.tag_id IS NULL
            ORDER BY t.name ASC
        ");

        return $stmt->fetchAll();
    }

    public function tagsNotUsed()
    {
        $pdo = Database::getConnection();

        // =========================
        // TAGS SEM USO
        // =========================

        header('Content-Type: application/json');
        echo json_encode($this->getUnusedTags($pdo));
    }

    public function suppliers()
    {
        $pdo = Database::getConnection();

        // =========================
        // TOTAL PAGO GLOBAL (confirmadas)
        // =========================
        $totalPaid = (float) $pdo
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
            $year = (int) $r['calendar_year'];

            $years[$year]['suppliers'][] = [
                'supplier' => $r['supplier_name'],
                'count' => (int) $r['count'],
                'total_paid' => (float) $r['total_paid'],
            ];

            $table[] = [
                'year' => $year,
                'supplier' => $r['supplier_name'],
                'count' => (int) $r['count'],
                'total_paid' => (float) $r['total_paid'],
            ];
        }

        header('Content-Type: application/json');
        echo json_encode([
            'summary' => [
                'total_paid' => $totalPaid
            ],
            'years' => $years,
            'table' => $table,
            'unused' => $this->getUnusedSuppliers($pdo),
        ]);
    }

    private function getUnusedSuppliers(PDO $pdo): array
    {
        $stmt = $pdo->query("
            SELECT s.cpf_cnpj, s.name
            FROM suppliers s
            LEFT JOIN expenses e ON e.supplier_id = s.id AND e.is_confirmed = 1
            WHERE e.id IS NULL
            ORDER BY s.name ASC
        ");

        return $stmt->fetchAll();
    }

    public function suppliersNotUsed()
    {
        $pdo = Database::getConnection();

        // =========================
        // FORNECEDORES SEM USO
        // =========================

        header('Content-Type: application/json');
        echo json_encode($this->getUnusedSuppliers($pdo));
    }
}
