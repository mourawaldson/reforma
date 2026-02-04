<?php
declare(strict_types=1);

require_once __DIR__ . '/../Models/Supplier.php';
require_once __DIR__ . '/../Validator.php';

class SuppliersController
{
    private function getJsonInput(): array
    {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }

    private function normalizeCpfCnpj(?string $value): ?string
    {
        if (!$value)
            return null;
        return preg_replace('/\D/', '', $value);
    }

    public function index()
    {
        header('Content-Type: application/json');
        echo json_encode(Supplier::all());
    }

    public function show($id)
    {
        $row = Supplier::find((int) $id);
        header('Content-Type: application/json');

        if (!$row) {
            http_response_code(404);
            echo json_encode(['error' => 'Fornecedor não encontrado']);
            return;
        }

        echo json_encode($row);
    }

    public function store()
    {
        $data = $this->getJsonInput();

        Validator::validate($data, function (Validator $v) {
            $v->required(['name']);
        });

        $cpfCnpj = $this->normalizeCpfCnpj($data['cpf_cnpj'] ?? null);

        if ($cpfCnpj) {
            $existing = Supplier::findByCpfCnpj($cpfCnpj);

            if ($existing) {
                $isCnpj = strlen($cpfCnpj) === 14;

                http_response_code(400);
                echo json_encode([
                    'error' => $isCnpj
                        ? 'Já existe um fornecedor pessoa jurídica cadastrado com este CNPJ'
                        : 'Já existe um fornecedor pessoa física cadastrado com este CPF'
                ]);
                return;
            }
        }

        $id = Supplier::create([
            'name' => $data['name'],
            'company_name' => $data['company_name'] ?? null,
            'cpf_cnpj' => $cpfCnpj,
        ]);

        header('Content-Type: application/json');
        http_response_code(201);
        echo json_encode(['id' => $id]);
    }

    public function update($id)
    {
        $id = (int) $id;

        if (!Supplier::find($id)) {
            http_response_code(404);
            echo json_encode(['error' => 'Fornecedor não encontrado']);
            return;
        }

        $data = $this->getJsonInput();

        Validator::validate($data, function (Validator $v) {
            $v->required(['name']);
        });

        $cpfCnpj = $this->normalizeCpfCnpj($data['cpf_cnpj'] ?? null);

        if ($cpfCnpj) {
            $existing = Supplier::findByCpfCnpj($cpfCnpj);

            // se existir e NÃO for o próprio registro
            if ($existing && (int) $existing['id'] !== $id) {
                $isCnpj = strlen($cpfCnpj) === 14;

                http_response_code(400);
                echo json_encode([
                    'error' => $isCnpj
                        ? 'Já existe um fornecedor pessoa jurídica cadastrado com este CNPJ'
                        : 'Já existe um fornecedor pessoa física cadastrado com este CPF'
                ]);
                return;
            }
        }

        Supplier::update($id, [
            'name' => $data['name'],
            'company_name' => $data['company_name'] ?? null,
            'cpf_cnpj' => $cpfCnpj,
        ]);

        header('Content-Type: application/json');
        echo json_encode(['message' => 'Fornecedor atualizado']);
    }

    public function destroy($id)
    {
        $id = (int) $id;

        if (!Supplier::find($id)) {
            http_response_code(404);
            echo json_encode(['error' => 'Fornecedor não encontrado']);
            return;
        }

        Supplier::delete($id);

        header('Content-Type: application/json');
        echo json_encode(['message' => 'Fornecedor removido']);
    }
}
