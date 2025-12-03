<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3">Tags</h1>
    <a href="/tags/create" class="btn btn-primary">+ Nova tag</a>
</div>

<div id="tags-table-wrapper">
    <div class="text-muted">Carregando...</div>
</div>

<script>
$(function() {
    const API_BASE = 'http://localhost:8000';

    function loadTags() {
        $.getJSON(API_BASE + '/tags', function(data) {
            if (!Array.isArray(data) || data.length === 0) {
                $('#tags-table-wrapper').html('<div class="alert alert-info">Nenhuma tag cadastrada.</div>');
                return;
            }
            let html = '<div class="table-responsive"><table class="table table-sm table-striped align-middle">';
            html += '<thead><tr><th>ID</th><th>Nome</th><th></th></tr></thead><tbody>';
            data.forEach(function(t) {
                html += '<tr>';
                html += '<td>'+t.id+'</td>';
                html += '<td>'+t.name+'</td>';
                html += '<td><a href="/tags/'+t.id+'/edit" class="btn btn-sm btn-outline-secondary">Editar</a></td>';
                html += '</tr>';
            });
            html += '</tbody></table></div>';
            $('#tags-table-wrapper').html(html);
        }).fail(function() {
            $('#tags-table-wrapper').html('<div class="alert alert-danger">Erro ao carregar tags.</div>');
        });
    }

    loadTags();
});
</script>
