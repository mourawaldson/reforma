<?php
class ExpensesController
{
    public function index()
    {
        $pageTitle = 'Despesas - Backoffice';
        include __DIR__ . '/../../views/layout/header.php';
        include __DIR__ . '/../../views/expenses/index.php';
        include __DIR__ . '/../../views/layout/footer.php';
    }

    public function create()
    {
        $pageTitle = 'Nova Despesa';
        $expenseId = null;
        include __DIR__ . '/../../views/layout/header.php';
        include __DIR__ . '/../../views/expenses/form.php';
        include __DIR__ . '/../../views/layout/footer.php';
    }

    public function edit($id)
    {
        $pageTitle = 'Editar Despesa';
        $expenseId = (int)$id;
        include __DIR__ . '/../../views/layout/header.php';
        include __DIR__ . '/../../views/expenses/form.php';
        include __DIR__ . '/../../views/layout/footer.php';
    }
}
