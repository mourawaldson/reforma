<?php
require_once __DIR__ . '/../Models/Supplier.php';

class SuppliersController
{
    private function getJsonInput(): array
    {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }

    public function index()
    {
        $data = Supplier::all();
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    public function show($id)
    {
        $row = Supplier::find((int)$id);
        header('Content-Type: application/json');
        if (!$row) {
            http_response_code(404);
            echo json_encode(['error' => 'Supplier not found']);
            return;
        }
        echo json_encode($row);
    }

    public function store()
    {
        $data = $this->getJsonInput();
        if (empty($data['name'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Field name is required']);
            return;
        }
        $payload = [
            'name'     => $data['name'],
            'type'     => $data['type'] ?? null,
            'cpf_cnpj' => $data['cpf_cnpj'] ?? null,
        ];
        $id = Supplier::create($payload);
        header('Content-Type: application/json');
        http_response_code(201);
        echo json_encode(['id' => $id, 'message' => 'Supplier created']);
    }

    public function update($id)
    {
        $id = (int)$id;
        if (!Supplier::find($id)) {
            http_response_code(404);
            echo json_encode(['error' => 'Supplier not found']);
            return;
        }
        $data = $this->getJsonInput();
        if (empty($data['name'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Field name is required']);
            return;
        }
        $payload = [
            'name'     => $data['name'],
            'type'     => $data['type'] ?? null,
            'cpf_cnpj' => $data['cpf_cnpj'] ?? null,
        ];
        Supplier::update($id, $payload);
        header('Content-Type: application/json');
        echo json_encode(['message' => 'Supplier updated']);
    }

    public function destroy($id)
    {
        $id = (int)$id;
        if (!Supplier::find($id)) {
            http_response_code(404);
            echo json_encode(['error' => 'Supplier not found']);
            return;
        }
        Supplier::delete($id);
        header('Content-Type: application/json');
        echo json_encode(['message' => 'Supplier deleted']);
    }
}
