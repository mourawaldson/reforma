<?php
class SuppliersController
{
    public function index()
    {
        $pageTitle = 'Fornecedores';
        include __DIR__ . '/../../views/layout/header.php';
        include __DIR__ . '/../../views/suppliers/index.php';
        include __DIR__ . '/../../views/layout/footer.php';
    }

    public function create()
    {
        $pageTitle = 'Novo Fornecedor';
        $supplierId = null;
        include __DIR__ . '/../../views/layout/header.php';
        include __DIR__ . '/../../views/suppliers/form.php';
        include __DIR__ . '/../../views/layout/footer.php';
    }

    public function edit($id)
    {
        $pageTitle = 'Editar Fornecedor';
        $supplierId = (int)$id;
        include __DIR__ . '/../../views/layout/header.php';
        include __DIR__ . '/../../views/suppliers/form.php';
        include __DIR__ . '/../../views/layout/footer.php';
    }
}
