$(function () {
    const API_BASE = 'http://localhost:8000';
    const $wrapper = $('#suppliers-table-wrapper');
    const $status = $('#suppliers-status');
    const tableTemplate = document.getElementById('suppliers-table-template');
    const rowTemplate = document.getElementById('supplier-row-template');

    function formatCpfCnpj(value) {
        if (!value) return '';

        const digits = value.replace(/\D/g, '');
        if (digits.length === 11) {
            return digits.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
        }
        if (digits.length === 14) {
            return digits.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, '$1.$2.$3/$4-$5');
        }
        return value;
    }

    function showStatus(message, type) {
        $wrapper.prop('hidden', true).empty();
        $status
            .removeClass('text-muted alert-info alert-danger')
            .addClass(type)
            .text(message)
            .prop('hidden', false);
    }

    function loadSuppliers() {
        $.getJSON(API_BASE + '/suppliers', function (data) {
            if (!Array.isArray(data) || data.length === 0) {
                showStatus('Nenhum fornecedor cadastrado.', 'alert alert-info');
                return;
            }

            const table = tableTemplate.content.firstElementChild.cloneNode(true);
            const rows = table.querySelector('[data-rows]');

            data.forEach(function (supplier) {
                const row = rowTemplate.content.firstElementChild.cloneNode(true);
                const documentValue = formatCpfCnpj(supplier.cpf_cnpj);
                const documentCell = row.querySelector('[data-field="document"]');

                row.querySelector('[data-field="name"]').textContent = supplier.display_name || '-';
                documentCell.textContent = documentValue;
                documentCell.hidden = !documentValue;

                const editLink = row.querySelector('[data-action="edit"]');
                editLink.href = '/suppliers/' + encodeURIComponent(supplier.id) + '/edit';
                row.querySelector('.btn-delete').dataset.id = supplier.id;
                rows.appendChild(row);
            });

            $status.prop('hidden', true);
            $wrapper.empty().append(table).prop('hidden', false);
        }).fail(function () {
            showStatus('Erro ao carregar fornecedores.', 'alert alert-danger');
        });
    }

    $wrapper.on('click', '.btn-delete', function () {
        if (!confirm('Remover fornecedor? As despesas ficarão sem fornecedor.')) return;

        const id = $(this).data('id');
        $.ajax({
            url: API_BASE + '/suppliers/' + encodeURIComponent(id),
            method: 'DELETE',
            success: loadSuppliers,
            error: function () {
                alert('Erro ao remover fornecedor.');
            }
        });
    });

    loadSuppliers();
});
