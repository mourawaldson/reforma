<?php
require_once __DIR__ . '/../Models/Expense.php';
require_once __DIR__ . '/../Database.php';

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
            'year'        => $_GET['year'] ?? null,
            'supplier_id' => $_GET['supplier_id'] ?? null,
            'tag_id'      => $_GET['tag_id'] ?? null,
            'q'           => $_GET['q'] ?? null,
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
            echo json_encode(['error' => 'Despesa não encontrada']);
            return;
        }

        echo json_encode($exp);
    }

    public function store()
    {
        $data = $this->getJsonInput();

        $required = ['date', 'description', 'amount_paid'];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                http_response_code(400);
                echo json_encode([
                    'error' => "Campo {$field} é obrigatório"
                ]);
                return;
            }
        }

        // Extrai ano da data (YYYY-MM-DD)
        $calendarYear = (int)substr($data['date'], 0, 4);

        // Regra de confirmação inicial
        $today = date('Y-m-d');
        $isConfirmed = ($data['date'] > $today) ? 0 : 1;

        $tags = isset($data['tags']) && is_array($data['tags'])
            ? $data['tags']
            : [];

        $payload = [
            'supplier_id'         => $data['supplier_id'] ?? null,
            'date'                => $data['date'],
            'description'         => $data['description'],
            'amount_nf'           => $data['amount_nf'] ?? null,
            'amount_paid'         => (float)$data['amount_paid'],
            'additional_discount' => $data['additional_discount'] ?? null,
            'calendar_year'       => $calendarYear,
            'is_confirmed'        => $isConfirmed,
        ];

        $id = Expense::create($payload, $tags);

        header('Content-Type: application/json');
        http_response_code(201);
        echo json_encode([
            'id'      => $id,
            'message' => 'Despesa criada'
        ]);
    }

    public function update($id)
    {
        $id = (int)$id;

        if (!Expense::find($id)) {
            http_response_code(404);
            echo json_encode(['error' => 'Despesa não encontrada']);
            return;
        }

        $data = $this->getJsonInput();

        $required = ['date', 'description', 'amount_paid'];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                http_response_code(400);
                echo json_encode([
                    'error' => "Campo {$field} é obrigatório"
                ]);
                return;
            }
        }

        $calendarYear = (int)substr($data['date'], 0, 4);

        $payload = [
            'supplier_id'         => $data['supplier_id'] ?? null,
            'date'                => $data['date'],
            'description'         => $data['description'],
            'amount_nf'           => $data['amount_nf'] ?? null,
            'amount_paid'         => (float)$data['amount_paid'],
            'additional_discount' => $data['additional_discount'] ?? null,
            'calendar_year'       => $calendarYear,
        ];

        Expense::update($id, $payload, $data['tags'] ?? []);

        header('Content-Type: application/json');
        echo json_encode(['message' => 'Despesa atualizada']);
    }

    public function destroy($id)
    {
        $id = (int)$id;

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

    /**
     * Confirma explicitamente uma despesa pendente
     * Rota: POST /expenses/{id}/confirm
     */
    public function confirm($id)
    {
        $id = (int)$id;

        if (!Expense::find($id)) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Despesa não encontrada']);
            return;
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare(
            "UPDATE expenses SET is_confirmed = 1 WHERE id = :id"
        );
        $stmt->execute(['id' => $id]);

        header('Content-Type: application/json');
        echo json_encode(['message' => 'Despesa confirmada']);
    }
}
