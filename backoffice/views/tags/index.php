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
                html += '<thead><tr><th>Nome</th><th>Ações</th></tr></thead><tbody>';

                data.forEach(function(t) {
                    html += '<tr>';
                    html += '<td>'+t.name+'</td>';
                    html += '<td>';
                    html += '<a href="/tags/'+t.id+'/edit" class="btn btn-sm btn-outline-secondary me-2">Editar</a>';
                    html += '<button class="btn btn-sm btn-outline-danger btn-remove" data-id="'+t.id+'">Remover</button>';
                    html += '</td>';
                    html += '</tr>';
                });

                html += '</tbody></table></div>';
                $('#tags-table-wrapper').html(html);
            }).fail(function() {
                $('#tags-table-wrapper').html('<div class="alert alert-danger">Erro ao carregar tags.</div>');
            });
        }

        $('#tags-table-wrapper').on('click', '.btn-remove', function() {
            const id = $(this).data('id');

            if (!confirm('Deseja realmente remover esta tag?')) {
                return;
            }

            $.ajax({
                url: API_BASE + '/tags/' + id,
                method: 'DELETE',
                success: function() {
                    loadTags();
                },
                error: function(xhr) {
                    alert('Erro ao remover tag: ' + xhr.responseText);
                }
            });
        });

        loadTags();
    });
</script>
