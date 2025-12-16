<style>
    .descricao-coluna {
        max-width: 260px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
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

        function formatDateBR(isoDate) {
            if (!isoDate) return '';
            const [y, m, d] = isoDate.split('-');
            return `${d}/${m}/${y}`;
        }

        $.extend($.fn.dataTable.ext.type.order, {
            "date-eu-pre": function (date) {
                if (!date) return 0;
                const parts = date.split('/');
                if (parts.length !== 3) return 0;
                return parseInt(parts[2] + parts[1] + parts[0], 10);
            }
        });

        function renderDescriptionWithTags(e) {
            const description = e.description || '';
            const title = description.replace(/"/g, '&quot;');

            const tagsHtml = (e.tags || []).map(function(t) {
                return '<span class="badge bg-secondary me-1">' + t.name + '</span>';
            }).join('');

            let html = '';
            html += '<div class="descricao-coluna" title="' + title + '">' + description + '</div>';

            if (tagsHtml) {
                html += '<div class="small text-muted mt-1">' + tagsHtml + '</div>';
            }

            return html;
        }

        function loadExpenses() {
            $.getJSON(API_BASE + '/expenses', function(data) {
                if (!Array.isArray(data) || data.length === 0) {
                    $('#expenses-table-wrapper').html(
                        '<div class="alert alert-info">Nenhuma despesa encontrada.</div>'
                    );
                    return;
                }

                const pendingList = [];
                const yearsMap = {};

                data.forEach(function(e) {
                    const isConfirmed =
                        e.is_confirmed === 1 ||
                        e.is_confirmed === '1' ||
                        e.is_confirmed === true;

                    if (!isConfirmed) {
                        pendingList.push(e);
                        return;
                    }

                    let year = e.calendar_year;
                    if (!year && e.date) year = e.date.substring(0, 4);
                    if (!year) year = 'Sem ano';

                    if (!yearsMap[year]) {
                        yearsMap[year] = [];
                    }

                    yearsMap[year].push(e);
                });

                let html = '';

                /* ===== Pendentes (TOPO) ===== */
                if (pendingList.length > 0) {
                    let totalPendingNf = 0;
                    let totalPendingPaid = 0;

                    pendingList.forEach(function(e) {
                        totalPendingNf   += e.amount_nf !== null ? Number(e.amount_nf) : 0;
                        totalPendingPaid += e.amount_paid !== null ? Number(e.amount_paid) : 0;
                    });

                    html += '<div class="card border-warning mb-4">';
                    html += '<div class="card-header bg-warning-subtle"><strong>Pendentes de confirmação</strong></div>';
                    html += '<div class="card-body p-0">';
                    html += '<div class="table-responsive">';
                    html += '<table class="table table-sm table-striped align-middle mb-0">';
                    html += '<thead><tr>';
                    html += '<th>Data</th>';
                    html += '<th>Fornecedor</th>';
                    html += '<th>Descrição</th>';
                    html += '<th class="text-end">NF</th>';
                    html += '<th class="text-end">Pago</th>';
                    html += '<th class="text-center">Ações</th>';
                    html += '</tr></thead><tbody>';

                    pendingList.forEach(function(e) {
                        html += '<tr>';
                        html += '<td>' + formatDateBR(e.date) + '</td>';
                        html += '<td>' + (e.supplier_name || '') + '</td>';
                        html += '<td>' + renderDescriptionWithTags(e) + '</td>';
                        html += '<td class="text-end">' +
                            (e.amount_nf !== null ? formatCurrencyBRFromNumber(e.amount_nf) : '') +
                            '</td>';
                        html += '<td class="text-end">' +
                            formatCurrencyBRFromNumber(e.amount_paid) +
                            '</td>';
                        html += '<td class="text-center">';
                        html += '<button type="button" class="btn btn-sm btn-outline-success btn-confirm-expense me-1" data-id="' + e.id + '">Confirmar</button>';
                        html += '<a href="/expenses/' + e.id + '/edit" class="btn btn-sm btn-outline-secondary me-1">Editar</a>';
                        html += '<button type="button" class="btn btn-sm btn-outline-danger btn-delete-expense" data-id="' + e.id + '">Remover</button>';
                        html += '</td>';
                        html += '</tr>';
                    });

                    html += '</tbody><tfoot>';
                    html += '<tr>';
                    html += '<th colspan="3" class="text-end">Total pendentes</th>';
                    html += '<th class="text-end">' + formatCurrencyBRFromNumber(totalPendingNf) + '</th>';
                    html += '<th class="text-end">' + formatCurrencyBRFromNumber(totalPendingPaid) + '</th>';
                    html += '<th></th>';
                    html += '</tr>';
                    html += '</tfoot></table></div></div></div>';
                }

                /* ===== Confirmadas por ano ===== */
                const yearKeys = Object.keys(yearsMap).sort();

                yearKeys.forEach(function(year) {
                    const confirmed = yearsMap[year];

                    html += '<div class="mb-4">';
                    html += '<h2 class="h5 mb-3">Ano ' + year + '</h2>';

                    let totalPaid = 0;
                    let totalNf   = 0;
                    const tableId = 'expenses-table-' + year;

                    html += '<div class="card">';
                    html += '<div class="card-body">';
                    html += '<h3 class="h6 mb-3">Despesas confirmadas</h3>';
                    html += '<div class="table-responsive">';
                    html += '<table id="' + tableId + '" class="table table-sm table-striped align-middle">';
                    html += '<thead><tr>';
                    html += '<th>Data</th>';
                    html += '<th>Fornecedor</th>';
                    html += '<th>Descrição</th>';
                    html += '<th class="text-end">NF</th>';
                    html += '<th class="text-end">Pago</th>';
                    html += '<th class="text-center">Ações</th>';
                    html += '</tr></thead><tbody>';

                    confirmed.forEach(function(e) {
                        const nfVal   = e.amount_nf !== null ? Number(e.amount_nf) : 0;
                        const paidVal = e.amount_paid !== null ? Number(e.amount_paid) : 0;

                        totalNf   += nfVal;
                        totalPaid += paidVal;

                        let rowClass = '';
                        if (nfVal > paidVal) rowClass = 'table-warning';
                        if (nfVal < paidVal) rowClass = 'table-danger';

                        html += '<tr class="' + rowClass + '">';
                        html += '<td>' + formatDateBR(e.date) + '</td>';
                        html += '<td>' + (e.supplier_name || '') + '</td>';
                        html += '<td>' + renderDescriptionWithTags(e) + '</td>';
                        html += '<td class="text-end">' +
                            (e.amount_nf !== null ? formatCurrencyBRFromNumber(e.amount_nf) : '') +
                            '</td>';
                        html += '<td class="text-end">' +
                            formatCurrencyBRFromNumber(e.amount_paid) +
                            '</td>';
                        html += '<td class="text-center">';
                        html += '<a href="/expenses/' + e.id + '/edit" class="btn btn-sm btn-outline-secondary me-1">Editar</a>';
                        html += '<button type="button" class="btn btn-sm btn-outline-danger btn-delete-expense" data-id="' + e.id + '">Remover</button>';
                        html += '</td>';
                        html += '</tr>';
                    });

                    html += '</tbody><tfoot>';
                    html += '<tr>';
                    html += '<th colspan="3" class="text-end">Total ano ' + year + '</th>';
                    html += '<th class="text-end">' + formatCurrencyBRFromNumber(totalNf) + '</th>';
                    html += '<th class="text-end">' + formatCurrencyBRFromNumber(totalPaid) + '</th>';
                    html += '<th></th>';
                    html += '</tr>';
                    html += '</tfoot></table></div></div></div>';
                    html += '</div>';
                });

                $('#expenses-table-wrapper').html(html);

                yearKeys.forEach(function(year) {
                    const tableSelector = '#expenses-table-' + year;
                    if ($(tableSelector).length) {
                        $(tableSelector).DataTable({
                            order: [],
                            pageLength: 10,
                            language: {
                                url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json"
                            },
                            columnDefs: [
                                { targets: 0, type: 'date-eu' },
                                { targets: [3, 4], className: 'text-end' }
                            ]
                        });
                    }
                });
            }).fail(function() {
                $('#expenses-table-wrapper').html(
                    '<div class="alert alert-danger">Erro ao carregar despesas.</div>'
                );
            });
        }

        $(document).on('click', '.btn-confirm-expense', function() {
            const id = $(this).data('id');
            if (!id) return;

            if (!confirm('Confirmar lançamento desta despesa?')) return;

            $.post(API_BASE + '/expenses/' + id + '/confirm')
                .done(loadExpenses)
                .fail(function() {
                    alert('Erro ao confirmar despesa.');
                });
        });

        loadExpenses();
    });
</script>
