<?php
class CategoriesController
{
    public function index()
    {
        $pageTitle = 'Categorias';
        include __DIR__ . '/../../views/layout/header.php';
        include __DIR__ . '/../../views/categories/index.php';
        include __DIR__ . '/../../views/layout/footer.php';
    }

    public function create()
    {
        $pageTitle = 'Nova Categoria';
        $categoryId = null;
        include __DIR__ . '/../../views/layout/header.php';
        include __DIR__ . '/../../views/categories/form.php';
        include __DIR__ . '/../../views/layout/footer.php';
    }

    public function edit($id)
    {
        $pageTitle = 'Editar Categoria';
        $categoryId = (int)$id;
        include __DIR__ . '/../../views/layout/header.php';
        include __DIR__ . '/../../views/categories/form.php';
        include __DIR__ . '/../../views/layout/footer.php';
    }
}
