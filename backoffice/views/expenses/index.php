<style>
    .descricao-coluna {
        max-width: 260px;      /* ajuste aqui o "tamanho visual" da coluna */
        white-space: nowrap;   /* mantém tudo em uma linha */
        overflow: hidden;      /* esconde o que passar do limite */
        text-overflow: ellipsis; /* adiciona os "..." automaticamente */
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3">Despesas</h1>
    <a href="/expenses/create" class="btn btn-primary">+ Nova despesa</a>
</div>

<div id="expenses-table-wrapper">
    <div class="text-muted">Carregando...</div>
</div>

<script>
$(function() {
    const API_BASE = 'http://localhost:8000';

    function formatCurrencyBRFromNumber(num) {
        if (num === null || num === undefined || num === '') return '';
        const n = Number(num);
        if (isNaN(n)) return '';
        const fixed = n.toFixed(2);
        let [intPart, decPart] = fixed.split('.');
        intPart = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        return 'R$ ' + intPart + ',' + decPart;
    }

    // converte 'YYYY-MM-DD' -> 'DD/MM/YYYY'
    function formatDateBR(isoDate) {
        if (!isoDate) return '';
        const [y, m, d] = isoDate.split('-');
        return `${d}/${m}/${y}`;
    }

    // suporte ao formato DD/MM/YYYY para ordenação (date-eu)
    $.extend($.fn.dataTable.ext.type.order, {
        "date-eu-pre": function (date) {
            if (!date) return 0;
            const parts = date.split('/');
            if (parts.length !== 3) return 0;
            return parseInt(parts[2] + parts[1] + parts[0], 10); // yyyymmdd
        }
    });

    function loadExpenses() {
        $.getJSON(API_BASE + '/expenses', function(data) {
            if (!Array.isArray(data) || data.length === 0) {
                $('#expenses-table-wrapper').html('<div class="alert alert-info">Nenhuma despesa encontrada.</div>');
                return;
            }

            // Agrupa por ano-calendário
            const yearsMap = {}; // { 2024: { pending: [], confirmed: [] }, ... }

            data.forEach(function(e) {
                let year = e.calendar_year;
                if (!year && e.date) {
                    year = e.date.substring(0, 4);
                }
                if (!year) {
                    year = 'Sem ano';
                }

                if (!yearsMap[year]) {
                    yearsMap[year] = {
                        pending: [],
                        confirmed: []
                    };
                }

                const isConfirmed = (e.is_confirmed === 1 || e.is_confirmed === '1' || e.is_confirmed === true);

                if (isConfirmed) {
                    yearsMap[year].confirmed.push(e);
                } else {
                    yearsMap[year].pending.push(e);
                }
            });

            const yearKeys = Object.keys(yearsMap).sort();

            if (yearKeys.length === 0) {
                $('#expenses-table-wrapper').html('<div class="alert alert-info">Nenhuma despesa encontrada.</div>');
                return;
            }

            let html = '';

            yearKeys.forEach(function(year) {
                const yearData  = yearsMap[year];
                const pending   = yearData.pending;
                const confirmed = yearData.confirmed;

                html += '<div class="mb-4">';
                html += '<h2 class="h5 mb-3">Ano ' + year + '</h2>';

                // Pendentes deste ano
                if (pending.length > 0) {
                    html += '<div class="card border-warning mb-3">';
                    html += '<div class="card-header bg-warning-subtle"><strong>Pendentes de confirmação</strong></div>';
                    html += '<div class="card-body p-0">';
                    html += '<div class="table-responsive mb-0">';
                    html += '<table class="table table-sm table-striped align-middle mb-0">';
                    html += '<thead><tr>';
                    html += '<th>Data</th><th>Categoria</th><th>Fornecedor</th><th>Descrição</th><th class="text-end">NF</th><th class="text-end">Pago</th><th class="text-center">Ações</th>';
                    html += '</tr></thead><tbody>';

                    pending.forEach(function(e) {
                        const description = e.description || '';
                        const descriptionTitle = description.replace(/"/g, '&quot;');

                        html += '<tr>';
                        html += '<td>' + formatDateBR(e.date) + '</td>';
                        html += '<td>' + (e.category_name || '') + '</td>';
                        html += '<td>' + (e.supplier_name || '') + '</td>';
                        html += '<td class="descricao-coluna" title="' + descriptionTitle + '">' + description + '</td>';
                        html += '<td class="text-end">' + (e.amount_nf !== null ? formatCurrencyBRFromNumber(e.amount_nf) : '') + '</td>';
                        html += '<td class="text-end">' + formatCurrencyBRFromNumber(e.amount_paid) + '</td>';
                        html += '<td class="text-center">';
                        html += '<button type="button" class="btn btn-sm btn-outline-success btn-confirm-expense" data-id="' + e.id + '">Confirmar</button>';
                        html += '</td>';
                        html += '</tr>';
                    });

                    html += '</tbody></table></div></div></div>';
                }

                // Confirmadas deste ano
                if (confirmed.length > 0) {
                    let totalPaid = 0;
                    let totalNf   = 0;

                    const tableId = 'expenses-table-' + year;

                    html += '<div class="card">';
                    html += '<div class="card-body">';
                    html += '<h3 class="h6 mb-3">Despesas confirmadas</h3>';
                    html += '<div class="table-responsive">';
                    html += '<table id="' + tableId + '" class="table table-sm table-striped align-middle">';
                    html += '<thead><tr>';
                    html += '<th>Data</th><th>Categoria</th><th>Fornecedor</th><th>Descrição</th><th class="text-end">NF</th><th class="text-end">Pago</th><th>Tags</th><th></th>';
                    html += '</tr></thead><tbody>';

                    confirmed.forEach(function(e) {
                        const nfVal   = e.amount_nf !== null ? Number(e.amount_nf) : 0;
                        const paidVal = e.amount_paid !== null ? Number(e.amount_paid) : 0;

                        totalNf   += nfVal;
                        totalPaid += paidVal;

                        const tags = (e.tags || []).map(function(t) {
                            return '<span class="badge bg-secondary me-1">' + t.name + '</span>';
                        }).join('');

                        const description = e.description || '';
                        const descriptionTitle = description.replace(/"/g, '&quot;');

                        html += '<tr>';
                        html += '<td>' + formatDateBR(e.date) + '</td>';
                        html += '<td>' + (e.category_name || '') + '</td>';
                        html += '<td>' + (e.supplier_name || '') + '</td>';
                        html += '<td class="descricao-coluna" title="' + descriptionTitle + '">' + description + '</td>';
                        html += '<td class="text-end">' + (e.amount_nf !== null ? formatCurrencyBRFromNumber(e.amount_nf) : '') + '</td>';
                        html += '<td class="text-end">' + formatCurrencyBRFromNumber(e.amount_paid) + '</td>';
                        html += '<td>' + tags + '</td>';
                        html += '<td><a href="/expenses/' + e.id + '/edit" class="btn btn-sm btn-outline-secondary">Editar</a></td>';
                        html += '</tr>';
                    });

                    html += '</tbody><tfoot>';
                    html += '<tr>';
                    html += '<th colspan="4" class="text-end">Total ano ' + year + '</th>';
                    html += '<th class="text-end">' + formatCurrencyBRFromNumber(totalNf) + '</th>';
                    html += '<th class="text-end">' + formatCurrencyBRFromNumber(totalPaid) + '</th>';
                    html += '<th colspan="2"></th>';
                    html += '</tr>';
                    html += '</tfoot></table></div></div></div>';
                } else {
                    html += '<div class="alert alert-info">Nenhuma despesa confirmada para este ano.</div>';
                }

                html += '</div>'; // fim bloco ano
            });

            $('#expenses-table-wrapper').html(html);

            // Inicializa DataTables em cada tabela de ano
            yearKeys.forEach(function(year) {
                const tableSelector = '#expenses-table-' + year;
                if ($(tableSelector).length) {
                    $(tableSelector).DataTable({
                        order: [],
                        pageLength: 50,
                        language: {
                            url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json"
                        },
                        columnDefs: [
                            { targets: 0, type: 'date-eu' },   // Data
                            { targets: [4, 5], className: 'text-end' } // NF / Pago alinhados
                        ]
                    });
                }
            });

        }).fail(function() {
            $('#expenses-table-wrapper').html('<div class="alert alert-danger">Erro ao carregar despesas.</div>');
        });
    }

    // Confirmação de despesa (pendentes)
    $(document).on('click', '.btn-confirm-expense', function() {
        const id = $(this).data('id');
        if (!id) return;

        if (!confirm('Confirmar lançamento desta despesa?')) {
            return;
        }

        $.post(API_BASE + '/expenses/' + id + '/confirm')
            .done(function() {
                loadExpenses();
            })
            .fail(function() {
                alert('Erro ao confirmar despesa.');
            });
    });

    loadExpenses();
});
</script>
