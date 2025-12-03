<?php
require_once __DIR__ . '/../Models/Tag.php';

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
        $row = Tag::find((int)$id);
        header('Content-Type: application/json');
        if (!$row) {
            http_response_code(404);
            echo json_encode(['error' => 'Tag not found']);
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
        $id = Tag::create(['name' => $data['name']]);
        header('Content-Type: application/json');
        http_response_code(201);
        echo json_encode(['id' => $id, 'message' => 'Tag created']);
    }

    public function update($id)
    {
        $id = (int)$id;
        if (!Tag::find($id)) {
            http_response_code(404);
            echo json_encode(['error' => 'Tag not found']);
            return;
        }
        $data = $this->getJsonInput();
        if (empty($data['name'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Field name is required']);
            return;
        }
        Tag::update($id, ['name' => $data['name']]);
        header('Content-Type: application/json');
        echo json_encode(['message' => 'Tag updated']);
    }

    public function destroy($id)
    {
        $id = (int)$id;
        if (!Tag::find($id)) {
            http_response_code(404);
            echo json_encode(['error' => 'Tag not found']);
            return;
        }
        Tag::delete($id);
        header('Content-Type: application/json');
        echo json_encode(['message' => 'Tag deleted']);
    }
}
