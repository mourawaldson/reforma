<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3">Fornecedores</h1>
    <a href="/suppliers/create" class="btn btn-primary">+ Novo fornecedor</a>
</div>

<div id="suppliers-table-wrapper">
    <div class="text-muted">Carregando...</div>
</div>

<script>
$(function() {
    const API_BASE = 'http://localhost:8000';

    function loadSuppliers() {
        $.getJSON(API_BASE + '/suppliers', function(data) {
            if (!Array.isArray(data) || data.length === 0) {
                $('#suppliers-table-wrapper').html('<div class="alert alert-info">Nenhum fornecedor cadastrado.</div>');
                return;
            }
            let html = '<div class="table-responsive"><table class="table table-sm table-striped align-middle">';
            html += '<thead><tr><th>Nome</th><th>Tipo</th><th>CPF/CNPJ</th><th></th></tr></thead><tbody>';
            data.forEach(function(s) {
                html += '<tr>';
                html += '<td>'+s.name+'</td>';
                html += '<td>'+(s.type || '')+'</td>';
                html += '<td>'+(s.cpf_cnpj || '')+'</td>';
                html += '<td><a href="/suppliers/'+s.id+'/edit" class="btn btn-sm btn-outline-secondary">Editar</a></td>';
                html += '</tr>';
            });
            html += '</tbody></table></div>';
            $('#suppliers-table-wrapper').html(html);
        }).fail(function() {
            $('#suppliers-table-wrapper').html('<div class="alert alert-danger">Erro ao carregar fornecedores.</div>');
        });
    }

    loadSuppliers();
});
</script>
