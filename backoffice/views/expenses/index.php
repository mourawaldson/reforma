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

<form class="row g-2 mb-3" id="filter-form">
    <div class="col-auto">
        <label for="filter-year" class="col-form-label">Ano:</label>
    </div>
    <div class="col-auto">
        <input type="number" class="form-control" id="filter-year" name="year" value="<?php echo date('Y'); ?>">
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-outline-secondary">Filtrar</button>
    </div>
</form>

<div id="expenses-table-wrapper">
    <div class="text-muted">Carregando...</div>
</div>

<div id="supplier-summary-wrapper" class="mt-4"></div>

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

    function loadExpenses() {
        const year = $('#filter-year').val();

        $.getJSON(API_BASE + '/expenses', { year }, function(data) {
            if (!Array.isArray(data) || data.length === 0) {
                $('#expenses-table-wrapper').html('<div class="alert alert-info">Nenhuma despesa encontrada.</div>');
                $('#supplier-summary-wrapper').html('');
                return;
            }

            // Totais gerais
            let totalPaid = 0;
            let totalNf = 0;

            // Totais por fornecedor
            const supplierTotals = {}; // { nomeFornecedor: { paid: x, nf: y } }

            let html = '<div class="table-responsive"><table class="table table-sm table-striped align-middle"><thead><tr>';
            html += '<th>Data</th><th>Categoria</th><th>Fornecedor</th><th>Descrição</th><th class="text-end">NF</th><th class="text-end">Pago</th><th>Tags</th><th></th>';
            html += '</tr></thead><tbody>';

            data.forEach(function(e) {
                const nfVal   = e.amount_nf !== null ? Number(e.amount_nf) : 0;
                const paidVal = e.amount_paid !== null ? Number(e.amount_paid) : 0;

                totalNf   += nfVal;
                totalPaid += paidVal;

                const supplierName = e.supplier_name || '(Sem fornecedor)';
                if (!supplierTotals[supplierName]) {
                    supplierTotals[supplierName] = { paid: 0, nf: 0 };
                }
                supplierTotals[supplierName].paid += paidVal;
                supplierTotals[supplierName].nf   += nfVal;

                const tags = (e.tags || []).map(t => '<span class="badge bg-secondary me-1">'+t.name+'</span>').join('');

                const description = e.description || '';

                html += '<tr>';
                html += '<td>'+e.date+'</td>';
                html += '<td>'+(e.category_name || '')+'</td>';
                html += '<td>'+(e.supplier_name || '')+'</td>';
                // descrição truncada por CSS, texto completo no title
                html += '<td class="descricao-coluna" title="'+description.replace(/"/g, '&quot;')+'">'+description+'</td>';
                html += '<td class="text-end">'+(e.amount_nf !== null ? formatCurrencyBRFromNumber(e.amount_nf) : '')+'</td>';
                html += '<td class="text-end">'+formatCurrencyBRFromNumber(e.amount_paid)+'</td>';
                html += '<td>'+tags+'</td>';
                html += '<td><a href="/expenses/'+e.id+'/edit" class="btn btn-sm btn-outline-secondary">Editar</a></td>';
                html += '</tr>';
            });

            // Rodapé com total geral
            html += '</tbody><tfoot>';
            html += '<tr>';
            html += '<th colspan="4" class="text-end">Total</th>';
            html += '<th class="text-end">'+formatCurrencyBRFromNumber(totalNf)+'</th>';
            html += '<th class="text-end">'+formatCurrencyBRFromNumber(totalPaid)+'</th>';
            html += '<th colspan="2"></th>';
            html += '</tr>';
            html += '</tfoot></table></div>';

            $('#expenses-table-wrapper').html(html);

            // ---- Resumo por fornecedor ----
            const entries = Object.entries(supplierTotals); // [ [nome, {paid, nf}], ... ]

            if (entries.length === 0) {
                $('#supplier-summary-wrapper').html('');
                return;
            }

            // Ordena por total pago desc
            entries.sort(function(a, b) {
                return b[1].paid - a[1].paid;
            });

            let sHtml = '<div class="card">';
            sHtml += '<div class="card-body">';
            sHtml += '<h2 class="h5 mb-3">Resumo por fornecedor</h2>';
            sHtml += '<div class="table-responsive"><table class="table table-sm table-striped align-middle mb-0">';
            sHtml += '<thead><tr><th>Fornecedor</th><th class="text-end">Total NF</th><th class="text-end">Total Pago</th></tr></thead><tbody>';

            entries.forEach(function([name, totals]) {
                sHtml += '<tr>';
                sHtml += '<td>'+name+'</td>';
                sHtml += '<td class="text-end">'+formatCurrencyBRFromNumber(totals.nf)+'</td>';
                sHtml += '<td class="text-end">'+formatCurrencyBRFromNumber(totals.paid)+'</td>';
                sHtml += '</tr>';
            });

            sHtml += '</tbody></table></div>';
            sHtml += '</div></div>';

            $('#supplier-summary-wrapper').html(sHtml);

        }).fail(function() {
            $('#expenses-table-wrapper').html('<div class="alert alert-danger">Erro ao carregar despesas.</div>');
            $('#supplier-summary-wrapper').html('');
        });
    }

    $('#filter-form').on('submit', function(e) {
        e.preventDefault();
        loadExpenses();
    });

    loadExpenses();
});
</script>
