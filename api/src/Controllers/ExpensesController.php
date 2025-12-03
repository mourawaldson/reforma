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
            'year'         => $_GET['year'] ?? null,
            'category_id'  => $_GET['category_id'] ?? null,
            'supplier_id'  => $_GET['supplier_id'] ?? null,
            'tag_id'       => $_GET['tag_id'] ?? null,
            'q'            => $_GET['q'] ?? null,
        ];

        // IMPORTANTE: Expense::list() deve retornar o campo is_confirmed também.
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

        // Regra de confirmação inicial:
        // - Se a data for maior que hoje, nasce como pendente (is_confirmed = 0)
        // - Caso contrário, nasce confirmada (is_confirmed = 1)
        $today = date('Y-m-d');
        $isConfirmed = 1;
        if (!empty($data['date']) && $data['date'] > $today) {
            $isConfirmed = 0;
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
            'is_confirmed'  => $isConfirmed,
        ];

        // IMPORTANTE: Expense::create($payload, $tags) precisa dar INSERT em is_confirmed.
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

        // Aqui NÃO mexemos em is_confirmed:
        // - Se nasceu pendente, continua pendente até o endpoint de confirmação ser chamado.
        // - Se já estava confirmada, continua confirmada.
        $payload = [
            'category_id'   => (int)$data['category_id'],
            'supplier_id'   => $data['supplier_id'] ?? null,
            'date'          => $data['date'],
            'description'   => $data['description'],
            'amount_nf'     => $data['amount_nf'] ?? null,
            'amount_paid'   => (float)$data['amount_paid'],
            'calendar_year' => $calendarYear ?: (int)date('Y'),
            // 'is_confirmed' fica como está no banco
        ];

        // IMPORTANTE: Expense::update($id, $payload, $tags) deve preservar is_confirmed.
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

    /**
     * Confirma explicitamente uma despesa (usado pelo botão "Confirmar" do backoffice).
     *
     * Rota sugerida: POST /expenses/{id}/confirm
     */
    public function confirm($id)
    {
        $id = (int)$id;

        if (!Expense::find($id)) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Expense not found']);
            return;
        }

        // Aqui uso Database direto pra não depender da assinatura de Expense::update.
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE expenses SET is_confirmed = 1 WHERE id = :id");
        $stmt->execute(['id' => $id]);

        header('Content-Type: application/json');
        echo json_encode(['message' => 'Expense confirmed']);
    }
}
