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

        function formatCpfCnpj(value) {
            if (!value) return '';

            const digits = value.replace(/\D/g, '');

            if (digits.length === 11) {
                // CPF
                return digits.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
            }

            if (digits.length === 14) {
                // CNPJ
                return digits.replace(
                    /(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/,
                    '$1.$2.$3/$4-$5'
                );
            }

            return value;
        }

        function loadSuppliers() {
            $.getJSON(API_BASE + '/suppliers', function(data) {
                if (!Array.isArray(data) || data.length === 0) {
                    $('#suppliers-table-wrapper').html(
                        '<div class="alert alert-info">Nenhum fornecedor cadastrado.</div>'
                    );
                    return;
                }

                let html = '<div class="table-responsive">';
                html += '<table class="table table-sm table-striped align-middle">';
                html += '<thead><tr>';
                html += '<th>Fornecedor</th>';
                html += '<th class="text-end">Ações</th>';
                html += '</tr></thead><tbody>';

                data.forEach(function(s) {
                    const doc = formatCpfCnpj(s.cpf_cnpj);

                    html += '<tr>';
                    html += '<td>';
                    html += '<div>' + s.display_name + '</div>';

                    if (doc) {
                        html += '<div class="small text-muted">' + doc + '</div>';
                    }

                    html += '</td>';
                    html += '<td class="text-end">';
                    html += '<a href="/suppliers/' + s.id + '/edit" ';
                    html += 'class="btn btn-sm btn-outline-secondary me-2">Editar</a>';
                    html += '<button class="btn btn-sm btn-outline-danger btn-delete" ';
                    html += 'data-id="' + s.id + '">Remover</button>';
                    html += '</td>';
                    html += '</tr>';
                });

                html += '</tbody></table></div>';
                $('#suppliers-table-wrapper').html(html);
            }).fail(function() {
                $('#suppliers-table-wrapper').html(
                    '<div class="alert alert-danger">Erro ao carregar fornecedores.</div>'
                );
            });
        }

        $(document).on('click', '.btn-delete', function() {
            if (!confirm('Remover fornecedor? As despesas ficarão sem fornecedor.')) {
                return;
            }

            const id = $(this).data('id');

            $.ajax({
                url: API_BASE + '/suppliers/' + id,
                method: 'DELETE',
                success: loadSuppliers,
                error: function() {
                    alert('Erro ao remover fornecedor.');
                }
            });
        });

        loadSuppliers();
    });
</script>
