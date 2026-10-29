<?php
declare(strict_types=1);

require_once __DIR__ . '/../Models/Supplier.php';
require_once __DIR__ . '/../Validator.php';

class SuppliersController
{
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
        $data = Validator::jsonInput();

        Validator::validate($data, function (Validator $v) {
            $v->required(['name'])
                ->string(['name', 'company_name'], 255)
                ->string(['cpf_cnpj'], 20);
        });

        $cpfCnpj = $this->normalizeCpfCnpj(isset($data['cpf_cnpj']) ? trim($data['cpf_cnpj']) : null);
        if ($cpfCnpj !== null && !in_array(strlen($cpfCnpj), [11, 14], true)) {
            http_response_code(400);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'CPF/CNPJ deve conter 11 ou 14 dígitos']);
            return;
        }

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
            'name' => trim($data['name']),
            'company_name' => isset($data['company_name']) ? trim($data['company_name']) : null,
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

        $data = Validator::jsonInput();

        Validator::validate($data, function (Validator $v) {
            $v->required(['name'])
                ->string(['name', 'company_name'], 255)
                ->string(['cpf_cnpj'], 20);
        });

        $cpfCnpj = $this->normalizeCpfCnpj(isset($data['cpf_cnpj']) ? trim($data['cpf_cnpj']) : null);
        if ($cpfCnpj !== null && !in_array(strlen($cpfCnpj), [11, 14], true)) {
            http_response_code(400);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'CPF/CNPJ deve conter 11 ou 14 dígitos']);
            return;
        }

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
            'name' => trim($data['name']),
            'company_name' => isset($data['company_name']) ? trim($data['company_name']) : null,
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
