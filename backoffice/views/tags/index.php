<section class="backoffice-list backoffice-list--tags">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3">Tags</h1>
        <a href="/tags/create" class="btn btn-primary">+ Nova tag</a>
    </div>

    <div id="tags-status" class="text-muted">Carregando...</div>
    <div id="tags-table-wrapper" hidden></div>

    <template id="tags-table-template">
        <div class="table-responsive">
            <table class="table table-sm table-striped align-middle">
                <thead><tr><th>Nome</th><th class="text-end">Ações</th></tr></thead>
                <tbody data-rows></tbody>
            </table>
        </div>
    </template>

    <template id="tag-row-template">
        <tr>
            <td data-field="name"></td>
            <td class="text-end">
                <a class="btn btn-sm btn-outline-secondary me-2" data-action="edit">Editar</a>
                <button class="btn btn-sm btn-outline-danger btn-remove" type="button">Remover</button>
            </td>
        </tr>
    </template>
</section>

<script src="/assets/js/tags.js?v=<?php echo filemtime(__DIR__ . '/../../public/assets/js/tags.js'); ?>"></script>
