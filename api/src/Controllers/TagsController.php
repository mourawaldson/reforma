<?php
declare(strict_types=1);

require_once __DIR__ . '/../Models/Tag.php';
require_once __DIR__ . '/../Validator.php';

class TagsController
{
    private function getJsonInput(): array
    {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }

    public function index()
    {
        $data = Tag::all();
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    public function show($id)
    {
        $row = Tag::find((int) $id);
        header('Content-Type: application/json');

        if (!$row) {
            http_response_code(404);
            echo json_encode(['error' => 'Tag não encontrada']);
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

        if (Tag::findByName($data['name'])) {
            http_response_code(409);
            echo json_encode(['error' => 'Já existe uma tag com esse nome']);
            return;
        }

        $id = Tag::create(['name' => $data['name']]);

        header('Content-Type: application/json');
        http_response_code(201);
        echo json_encode([
            'id' => $id,
            'message' => 'Tag criada com sucesso'
        ]);
    }

    public function update($id)
    {
        $id = (int) $id;
        $existing = Tag::find($id);

        if (!$existing) {
            http_response_code(404);
            echo json_encode(['error' => 'Tag não encontrada']);
            return;
        }

        $data = $this->getJsonInput();

        Validator::validate($data, function (Validator $v) {
            $v->required(['name']);
        });

        $byName = Tag::findByName($data['name']);
        if ($byName && (int) $byName['id'] !== $id) {
            http_response_code(409);
            echo json_encode(['error' => 'Já existe uma tag com esse nome']);
            return;
        }

        Tag::update($id, ['name' => $data['name']]);

        header('Content-Type: application/json');
        echo json_encode(['message' => 'Tag atualizada com sucesso']);
    }

    public function destroy($id)
    {
        $id = (int) $id;

        if (!Tag::find($id)) {
            http_response_code(404);
            echo json_encode(['error' => 'Tag não encontrada']);
            return;
        }

        // remove associações com despesas
        Tag::deleteExpenseRelations($id);

        // remove a tag
        Tag::delete($id);

        header('Content-Type: application/json');
        echo json_encode(['message' => 'Tag removida com sucesso']);
    }
}
