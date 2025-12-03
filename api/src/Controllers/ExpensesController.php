<?php
require_once __DIR__ . '/../Models/Expense.php';

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
            'year'         => $_GET['year'] ?? null,
            'category_id'  => $_GET['category_id'] ?? null,
            'supplier_id'  => $_GET['supplier_id'] ?? null,
            'tag_id'       => $_GET['tag_id'] ?? null,
            'q'            => $_GET['q'] ?? null,
        ];
        $data = Expense::list($filters);
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    public function show($id)
    {
        $exp = Expense::find((int)$id);
        header('Content-Type: application/json');
        if (!$exp) {
            http_response_code(404);
            echo json_encode(['error' => 'Expense not found']);
            return;
        }
        echo json_encode($exp);
    }

    public function store()
    {
        $data = $this->getJsonInput();
        $required = ['category_id', 'date', 'description', 'amount_paid'];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                http_response_code(400);
                echo json_encode(['error' => "Field {$field} is required"]);
                return;
            }
        }

        // Extrai ano da data (YYYY-MM-DD)
        $calendarYear = null;
        if (!empty($data['date'])) {
            $calendarYear = (int)substr($data['date'], 0, 4);
        }

        $tags = isset($data['tags']) && is_array($data['tags']) ? $data['tags'] : [];
        $payload = [
            'category_id'   => (int)$data['category_id'],
            'supplier_id'   => $data['supplier_id'] ?? null,
            'date'          => $data['date'],
            'description'   => $data['description'],
            'amount_nf'     => $data['amount_nf'] ?? null,
            'amount_paid'   => (float)$data['amount_paid'],
            'calendar_year' => $calendarYear ?: (int)date('Y'),
        ];
        $id = Expense::create($payload, $tags);
        header('Content-Type: application/json');
        http_response_code(201);
        echo json_encode(['id' => $id, 'message' => 'Expense created']);
    }

    public function update($id)
    {
        $id = (int)$id;
        if (!Expense::find($id)) {
            http_response_code(404);
            echo json_encode(['error' => 'Expense not found']);
            return;
        }
        $data = $this->getJsonInput();
        $required = ['category_id', 'date', 'description', 'amount_paid'];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                http_response_code(400);
                echo json_encode(['error' => "Field {$field} is required"]);
                return;
            }
        }

        // Extrai ano da data
        $calendarYear = null;
        if (!empty($data['date'])) {
            $calendarYear = (int)substr($data['date'], 0, 4);
        }

        $tags = isset($data['tags']) && is_array($data['tags']) ? $data['tags'] : [];
        $payload = [
            'category_id'   => (int)$data['category_id'],
            'supplier_id'   => $data['supplier_id'] ?? null,
            'date'          => $data['date'],
            'description'   => $data['description'],
            'amount_nf'     => $data['amount_nf'] ?? null,
            'amount_paid'   => (float)$data['amount_paid'],
            'calendar_year' => $calendarYear ?: (int)date('Y'),
        ];
        Expense::update($id, $payload, $tags);
        header('Content-Type: application/json');
        echo json_encode(['message' => 'Expense updated']);
    }

    public function destroy($id)
    {
        $id = (int)$id;
        if (!Expense::find($id)) {
            http_response_code(404);
            echo json_encode(['error' => 'Expense not found']);
            return;
        }
        Expense::delete($id);
        header('Content-Type: application/json');
        echo json_encode(['message' => 'Expense deleted']);
    }
}
