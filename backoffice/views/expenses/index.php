<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3">Despesas</h1>
    <a href="/expenses/create" class="btn btn-primary">+ Nova despesa</a>
</div>

<div id="expenses-status" class="text-muted">Carregando...</div>
<div id="expenses-table-wrapper" hidden></div>

<template id="missing-expenses-table-template">
    <div class="card border-warning mb-4">
        <div class="card-header bg-warning-subtle"><strong>Despesas sem valor de NF</strong></div>
        <div class="table-responsive">
            <table class="table table-sm table-striped align-middle mb-0">
                <thead><tr><th>Data</th><th>Fornecedor</th><th>Descrição</th><th class="text-end">Pago</th><th class="text-center">Ações</th></tr></thead>
                <tbody data-rows></tbody>
            </table>
        </div>
    </div>
</template>

<template id="missing-expense-row-template">
    <tr>
        <td data-field="date"></td>
        <td data-field="supplier"></td>
        <td>
            <div class="descricao-coluna" data-field="description"></div>
            <div class="small text-muted mt-1" data-field="tags"></div>
        </td>
        <td class="text-end" data-field="paid"></td>
        <td class="text-center">
            <a class="btn btn-sm btn-outline-secondary me-1" data-action="edit">Editar</a>
            <button type="button" class="btn btn-sm btn-outline-danger btn-delete-expense" data-action="delete">Remover</button>
        </td>
    </tr>
</template>

<template id="pending-expenses-table-template">
    <div class="card border-warning mb-4">
        <div class="card-header bg-warning-subtle"><strong>Pendentes de confirmação</strong></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-striped align-middle mb-0">
                    <thead><tr><th>Data</th><th>Fornecedor</th><th>Descrição</th><th class="text-end">NF</th><th class="text-end">Pago</th><th class="text-center">Ações</th></tr></thead>
                    <tbody data-rows></tbody>
                    <tfoot><tr><th colspan="3" class="text-end">Total pendentes</th><th class="text-end" data-field="total-nf"></th><th class="text-end" data-field="total-paid"></th><th></th></tr></tfoot>
                </table>
            </div>
        </div>
    </div>
</template>

<template id="pending-expense-row-template">
    <tr>
        <td data-field="date"></td>
        <td data-field="supplier"></td>
        <td>
            <div class="descricao-coluna" data-field="description"></div>
            <div class="small text-muted mt-1" data-field="tags"></div>
        </td>
        <td class="text-end" data-field="nf"></td>
        <td class="text-end" data-field="paid"></td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-outline-success btn-confirm-expense me-1" data-action="confirm">Confirmar</button>
            <a class="btn btn-sm btn-outline-secondary me-1" data-action="edit">Editar</a>
            <button type="button" class="btn btn-sm btn-outline-danger btn-delete-expense" data-action="delete">Remover</button>
        </td>
    </tr>
</template>

<template id="confirmed-expenses-heading-template">
    <h2 class="h5 mt-5 mb-3">Despesas confirmadas</h2>
</template>

<template id="confirmed-year-table-template">
    <div class="mb-4">
        <div class="card">
            <div class="card-body">
                <h2 class="h6 mb-3" data-field="year"></h2>
                <div class="table-responsive">
                    <table class="table table-sm table-striped align-middle" data-field="table">
                        <thead><tr><th>Data</th><th>Fornecedor</th><th>Descrição</th><th class="text-end">NF</th><th class="text-end">Pago</th><th class="text-center">Ações</th></tr></thead>
                        <tbody data-rows></tbody>
                        <tfoot><tr><th colspan="3" class="text-end" data-field="year-total-label"></th><th class="text-end" data-field="total-nf"></th><th class="text-end" data-field="total-paid"></th><th></th></tr></tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>

<template id="confirmed-expense-row-template">
    <tr>
        <td data-field="date"></td>
        <td data-field="supplier"></td>
        <td>
            <div class="descricao-coluna" data-field="description"></div>
            <div class="small text-muted mt-1" data-field="tags"></div>
        </td>
        <td class="text-end" data-field="nf"></td>
        <td class="text-end" data-field="paid"></td>
        <td class="text-center">
            <a class="btn btn-sm btn-outline-secondary me-1" data-action="edit">Editar</a>
            <button type="button" class="btn btn-sm btn-outline-danger btn-delete-expense" data-action="delete">Remover</button>
        </td>
    </tr>
</template>

<template id="expense-tag-template">
    <span class="badge bg-secondary me-1"></span>
</template>

<script src="/assets/js/expenses.js?v=<?php echo filemtime(__DIR__ . '/../../public/assets/js/expenses.js'); ?>"></script>
