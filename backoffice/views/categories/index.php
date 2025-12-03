<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3">Categorias</h1>
    <a href="/categories/create" class="btn btn-primary">+ Nova categoria</a>
</div>

<div id="categories-table-wrapper">
    <div class="text-muted">Carregando...</div>
</div>

<script>
$(function() {
    const API_BASE = 'http://localhost:8000';

    function loadCategories() {
        $.getJSON(API_BASE + '/categories', function(data) {
            if (!Array.isArray(data) || data.length === 0) {
                $('#categories-table-wrapper').html('<div class="alert alert-info">Nenhuma categoria cadastrada.</div>');
                return;
            }
            let html = '<div class="table-responsive"><table class="table table-sm table-striped align-middle">';
            html += '<thead><tr><th>ID</th><th>Nome</th><th></th></tr></thead><tbody>';
            data.forEach(function(c) {
                html += '<tr>';
                html += '<td>'+c.id+'</td>';
                html += '<td>'+c.name+'</td>';
                html += '<td><a href="/categories/'+c.id+'/edit" class="btn btn-sm btn-outline-secondary">Editar</a></td>';
                html += '</tr>';
            });
            html += '</tbody></table></div>';
            $('#categories-table-wrapper').html(html);
        }).fail(function() {
            $('#categories-table-wrapper').html('<div class="alert alert-danger">Erro ao carregar categorias.</div>');
        });
    }

    loadCategories();
});
</script>
