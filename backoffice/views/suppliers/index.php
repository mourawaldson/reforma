<section class="backoffice-list backoffice-list--suppliers">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3">Fornecedores</h1>
        <a href="/suppliers/create" class="btn btn-primary">+ Novo fornecedor</a>
    </div>

    <div id="suppliers-status" class="text-muted">Carregando...</div>
    <div id="suppliers-table-wrapper" hidden></div>

    <template id="suppliers-table-template">
        <div class="table-responsive">
            <table class="table table-sm table-striped align-middle">
                <thead><tr><th>Fornecedor</th><th class="text-end">Ações</th></tr></thead>
                <tbody data-rows></tbody>
            </table>
        </div>
    </template>

    <template id="supplier-row-template">
        <tr>
            <td>
                <div data-field="name"></div>
                <div class="small text-muted" data-field="document"></div>
            </td>
            <td class="text-end">
                <a class="btn btn-sm btn-outline-secondary me-2" data-action="edit">Editar</a>
                <button class="btn btn-sm btn-outline-danger btn-delete" type="button">Remover</button>
            </td>
        </tr>
    </template>
</section>

<script src="/assets/js/suppliers.js?v=<?php echo filemtime(__DIR__ . '/../../public/assets/js/suppliers.js'); ?>"></script>
