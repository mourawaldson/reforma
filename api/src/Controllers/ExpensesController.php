<?php
declare(strict_types=1);

require_once __DIR__ . '/../Validator.php';

class ExpensesController
{
    private function getJsonInput(): array
    {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }

    public function index()
    {
        $filters = [
            'year' => isset($_GET['year']) ? (int) $_GET['year'] : null,
            'supplier_id' => isset($_GET['supplier_id']) ? (int) $_GET['supplier_id'] : null,
            'tag_id' => isset($_GET['tag_id']) ? (int) $_GET['tag_id'] : null,
            'q' => $_GET['q'] ?? null,
        ];

        // Filter out nulls
        $filters = array_filter($filters, fn($v) => !is_null($v));

        $data = Expense::list($filters);

        header('Content-Type: application/json');
        echo json_encode($data);
    }

    public function show($id)
    {
        $exp = Expense::find((int) $id);
        header('Content-Type: application/json');

        if (!$exp) {
            http_response_code(404);
            echo json_encode(['error' => 'Despesa não encontrada']);
            return;
        }

        echo json_encode($exp);
    }

    public function store()
    {
        $data = $this->getJsonInput();

        Validator::validate($data, function (Validator $v) {
            $v->required(['date', 'description', 'amount_paid'])
                ->numeric(['amount_paid'])
                ->date(['date']);

            // Optional numeric fields
            $d = $v->getData();
            if (isset($d['amount_nf']))
                $v->numeric(['amount_nf']);
            if (isset($d['additional_discount']))
                $v->numeric(['additional_discount']);
        });

        // Extrai ano da data (YYYY-MM-DD)
        $calendarYear = (int) substr($data['date'], 0, 4);

        // Regra de confirmação inicial
        $today = date('Y-m-d');
        $isConfirmed = ($data['date'] > $today) ? 0 : 1;

        $tags = isset($data['tags']) && is_array($data['tags'])
            ? $data['tags']
            : [];

        $payload = [
            'supplier_id' => isset($data['supplier_id']) ? (int) $data['supplier_id'] : null,
            'date' => $data['date'],
            'description' => $data['description'],
            'amount_nf' => isset($data['amount_nf']) ? (float) $data['amount_nf'] : null,
            'amount_paid' => (float) $data['amount_paid'],
            'additional_discount' => isset($data['additional_discount']) ? (float) $data['additional_discount'] : null,
            'calendar_year' => $calendarYear,
            'is_confirmed' => $isConfirmed,
        ];

        $id = Expense::create($payload, $tags);

        header('Content-Type: application/json');
        http_response_code(201);
        echo json_encode([
            'id' => $id,
            'message' => 'Despesa criada'
        ]);
    }

    public function update($id)
    {
        $id = (int) $id;

        if (!Expense::find($id)) {
            http_response_code(404);
            echo json_encode(['error' => 'Despesa não encontrada']);
            return;
        }

        $data = $this->getJsonInput();

        Validator::validate($data, function (Validator $v) {
            $v->required(['date', 'description', 'amount_paid'])
                ->numeric(['amount_paid'])
                ->date(['date']);

            // Optional numeric fields
            $d = $v->getData();
            if (isset($d['amount_nf']))
                $v->numeric(['amount_nf']);
            if (isset($d['additional_discount']))
                $v->numeric(['additional_discount']);
        });

        $calendarYear = (int) substr($data['date'], 0, 4);

        $payload = [
            'supplier_id' => isset($data['supplier_id']) ? (int) $data['supplier_id'] : null,
            'date' => $data['date'],
            'description' => $data['description'],
            'amount_nf' => isset($data['amount_nf']) ? (float) $data['amount_nf'] : null,
            'amount_paid' => (float) $data['amount_paid'],
            'additional_discount' => isset($data['additional_discount']) ? (float) $data['additional_discount'] : null,
            'calendar_year' => $calendarYear,
        ];

        Expense::update($id, $payload, $data['tags'] ?? []);

        header('Content-Type: application/json');
        echo json_encode(['message' => 'Despesa atualizada']);
    }

    public function destroy($id)
    {
        $id = (int) $id;

        if (!Expense::find($id)) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Despesa não encontrada']);
            return;
        }

        Expense::delete($id);

        header('Content-Type: application/json');
        echo json_encode(['message' => 'Despesa removida']);
    }

    public function confirm($id)
    {
        $id = (int) $id;

        if (!Expense::find($id)) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Despesa não encontrada']);
            return;
        }

        Expense::confirm($id);

        header('Content-Type: application/json');
        echo json_encode(['message' => 'Despesa confirmada']);
    }
}
