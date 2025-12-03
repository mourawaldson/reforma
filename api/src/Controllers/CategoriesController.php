<?php
require_once __DIR__ . '/../Models/Category.php';

class CategoriesController
{
    private function getJsonInput(): array
    {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }

    public function index()
    {
        $data = Category::all();
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    public function show($id)
    {
        $cat = Category::find((int)$id);
        header('Content-Type: application/json');
        if (!$cat) {
            http_response_code(404);
            echo json_encode(['error' => 'Category not found']);
            return;
        }
        echo json_encode($cat);
    }

    public function store()
    {
        $data = $this->getJsonInput();
        if (empty($data['name'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Field name is required']);
            return;
        }
        $id = Category::create(['name' => $data['name']]);
        header('Content-Type: application/json');
        http_response_code(201);
        echo json_encode(['id' => $id, 'message' => 'Category created']);
    }

    public function update($id)
    {
        $id = (int)$id;
        if (!Category::find($id)) {
            http_response_code(404);
            echo json_encode(['error' => 'Category not found']);
            return;
        }
        $data = $this->getJsonInput();
        if (empty($data['name'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Field name is required']);
            return;
        }
        Category::update($id, ['name' => $data['name']]);
        header('Content-Type: application/json');
        echo json_encode(['message' => 'Category updated']);
    }

    public function destroy($id)
    {
        $id = (int)$id;
        if (!Category::find($id)) {
            http_response_code(404);
            echo json_encode(['error' => 'Category not found']);
            return;
        }
        Category::delete($id);
        header('Content-Type: application/json');
        echo json_encode(['message' => 'Category deleted']);
    }
}
