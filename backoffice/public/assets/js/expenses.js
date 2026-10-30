$(function () {
    const API_BASE = '/api-proxy';
    const $wrapper = $('#expenses-table-wrapper');
    const $status = $('#expenses-status');
    const templates = {
        missingTable: document.getElementById('missing-expenses-table-template'),
        missingRow: document.getElementById('missing-expense-row-template'),
        pendingTable: document.getElementById('pending-expenses-table-template'),
        pendingRow: document.getElementById('pending-expense-row-template'),
        confirmedHeading: document.getElementById('confirmed-expenses-heading-template'),
        confirmedTable: document.getElementById('confirmed-year-table-template'),
        confirmedRow: document.getElementById('confirmed-expense-row-template'),
        tag: document.getElementById('expense-tag-template')
    };

    $.extend(true, $.fn.dataTable.defaults, {
        language: {
            decimal: ',',
            thousands: '.',
            processing: 'Processando...',
            search: 'Buscar:',
            lengthMenu: 'Mostrar _MENU_ registros',
            info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
            infoEmpty: 'Mostrando 0 a 0 de 0 registros',
            infoFiltered: '(filtrado de _MAX_ registros no total)',
            loadingRecords: 'Carregando...',
            zeroRecords: 'Nenhum registro encontrado',
            emptyTable: 'Nenhum dado disponível na tabela',
            paginate: {
                first: 'Primeiro',
                previous: 'Anterior',
                next: 'Próximo',
                last: 'Último'
            },
            aria: {
                sortAscending: ': ativar para ordenar a coluna em ordem crescente',
                sortDescending: ': ativar para ordenar a coluna em ordem decrescente'
            }
        }
    });

    $.extend($.fn.dataTable.ext.type.order, {
        'date-eu-pre': function (date) {
            if (!date) return 0;
            const parts = date.split('/');
            if (parts.length !== 3) return 0;
            return parseInt(parts[2] + parts[1] + parts[0], 10);
        }
    });

    function formatCurrencyBRFromNumber(value) {
        if (value === null || value === undefined || value === '') return '-';
        const amount = Number(value);
        if (Number.isNaN(amount)) return '-';

        const [integerPart, decimalPart] = amount.toFixed(2).split('.');
        const formattedInteger = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        return 'R$ ' + formattedInteger + ',' + decimalPart;
    }

    function formatDateBR(isoDate) {
        if (!isoDate) return '-';
        const [year, month, day] = isoDate.split('-');
        return day + '/' + month + '/' + year;
    }

    function numberOrZero(value) {
        if (value === null || value === undefined || value === '') return 0;
        const amount = Number(value);
        return Number.isNaN(amount) ? 0 : amount;
    }

    function hasNfValue(expense) {
        const value = expense.amount_nf;
        if (value === null || value === undefined ||
            (typeof value === 'string' && value.trim() === '')) {
            return false;
        }
        return Number(value) !== 0;
    }

    function isConfirmed(expense) {
        return expense.is_confirmed === 1 ||
            expense.is_confirmed === '1' ||
            expense.is_confirmed === true;
    }

    function clone(template) {
        return template.content.firstElementChild.cloneNode(true);
    }

    function setText(root, selector, value) {
        root.querySelector(selector).textContent = value === null || value === undefined || value === ''
            ? '-'
            : String(value);
    }

    function fillDescription(row, expense) {
        const description = expense.description || '-';
        const descriptionElement = row.querySelector('[data-field="description"]');
        descriptionElement.textContent = description;
        descriptionElement.title = description;

        const tagsContainer = row.querySelector('[data-field="tags"]');
        const tags = Array.isArray(expense.tags)
            ? expense.tags.filter(function (tag) { return tag && tag.name; })
            : [];

        if (tags.length === 0) {
            tagsContainer.textContent = '-';
            return;
        }

        tagsContainer.replaceChildren();
        tags.forEach(function (tag) {
            const badge = clone(templates.tag);
            badge.textContent = tag.name;
            tagsContainer.appendChild(badge);
        });
    }

    function fillCommonFields(row, expense) {
        setText(row, '[data-field="date"]', formatDateBR(expense.date));
        setText(row, '[data-field="supplier"]', expense.supplier_name || '-');
        fillDescription(row, expense);

        const editLink = row.querySelector('[data-action="edit"]');
        editLink.href = '/expenses/' + encodeURIComponent(expense.id) + '/edit';

        row.querySelectorAll('[data-action="delete"], [data-action="confirm"]').forEach(function (button) {
            button.dataset.id = expense.id;
        });
    }

    function fillAmount(row, field, value) {
        setText(row, '[data-field="' + field + '"]', formatCurrencyBRFromNumber(value));
    }

    function renderMissingNfTable(expenses) {
        const section = clone(templates.missingTable);
        const rows = section.querySelector('[data-rows]');

        expenses.forEach(function (expense) {
            const row = clone(templates.missingRow);
            fillCommonFields(row, expense);
            fillAmount(row, 'paid', expense.amount_paid);
            rows.appendChild(row);
        });

        return section;
    }

    function renderPendingTable(expenses) {
        const section = clone(templates.pendingTable);
        const rows = section.querySelector('[data-rows]');
        let totalNf = 0;
        let totalPaid = 0;

        expenses.forEach(function (expense) {
            const row = clone(templates.pendingRow);
            fillCommonFields(row, expense);
            fillAmount(row, 'nf', expense.amount_nf);
            fillAmount(row, 'paid', expense.amount_paid);
            totalNf += numberOrZero(expense.amount_nf);
            totalPaid += numberOrZero(expense.amount_paid);
            rows.appendChild(row);
        });

        setText(section, '[data-field="total-nf"]', formatCurrencyBRFromNumber(totalNf));
        setText(section, '[data-field="total-paid"]', formatCurrencyBRFromNumber(totalPaid));
        return section;
    }

    function renderConfirmedYear(year, expenses, tablesForDataTable) {
        const section = clone(templates.confirmedTable);
        const table = section.querySelector('[data-field="table"]');
        const rows = section.querySelector('[data-rows]');
        let totalNf = 0;
        let totalPaid = 0;

        table.id = 'expenses-table-' + year;
        setText(section, '[data-field="year"]', 'Ano ' + year);
        setText(section, '[data-field="year-total-label"]', 'Total ano ' + year);

        expenses.forEach(function (expense) {
            const row = clone(templates.confirmedRow);
            const nfValue = numberOrZero(expense.amount_nf);
            const paidValue = numberOrZero(expense.amount_paid);

            if (nfValue > paidValue) row.classList.add('table-warning');
            if (nfValue < paidValue) row.classList.add('table-danger');

            fillCommonFields(row, expense);
            fillAmount(row, 'nf', expense.amount_nf);
            fillAmount(row, 'paid', expense.amount_paid);
            totalNf += nfValue;
            totalPaid += paidValue;
            rows.appendChild(row);
        });

        setText(section, '[data-field="total-nf"]', formatCurrencyBRFromNumber(totalNf));
        setText(section, '[data-field="total-paid"]', formatCurrencyBRFromNumber(totalPaid));
        tablesForDataTable.push(table);
        return section;
    }

    function showStatus(message, type) {
        $wrapper.prop('hidden', true).empty();
        $status
            .removeClass('text-muted alert-info alert-danger')
            .addClass(type)
            .text(message)
            .prop('hidden', false);
    }

    function loadExpenses() {
        $.getJSON(API_BASE + '/expenses', function (data) {
            if (!Array.isArray(data) || data.length === 0) {
                showStatus('Nenhuma despesa encontrada.', 'alert alert-info');
                return;
            }

            const pendingExpenses = [];
            const missingNfExpenses = data.filter(function (expense) {
                return isConfirmed(expense) && !hasNfValue(expense);
            });
            const expensesByYear = {};

            data.forEach(function (expense) {
                if (!isConfirmed(expense)) {
                    pendingExpenses.push(expense);
                    return;
                }

                let year = expense.calendar_year;
                if (!year && expense.date) year = expense.date.substring(0, 4);
                if (!year) year = 'Sem ano';
                if (!expensesByYear[year]) expensesByYear[year] = [];
                expensesByYear[year].push(expense);
            });

            const content = document.createDocumentFragment();
            const tablesForDataTable = [];

            if (missingNfExpenses.length > 0) {
                content.appendChild(renderMissingNfTable(missingNfExpenses));
            }

            if (pendingExpenses.length > 0) {
                content.appendChild(renderPendingTable(pendingExpenses));
            }

            const years = Object.keys(expensesByYear).sort();
            if (years.length > 0) {
                content.appendChild(clone(templates.confirmedHeading));
            }

            years.forEach(function (year) {
                content.appendChild(renderConfirmedYear(year, expensesByYear[year], tablesForDataTable));
            });

            $status.prop('hidden', true);
            $wrapper.empty().append(content).prop('hidden', false);

            tablesForDataTable.forEach(function (table) {
                $(table).DataTable({
                    order: [],
                    pageLength: 10,
                    columnDefs: [
                        { targets: 0, type: 'date-eu' },
                        { targets: [3, 4], className: 'text-end' }
                    ]
                });
            });
        }).fail(function () {
            showStatus('Erro ao carregar despesas.', 'alert alert-danger');
        });
    }

    $wrapper.on('click', '.btn-confirm-expense', function () {
        const id = $(this).data('id');
        if (!id || !confirm('Confirmar lançamento desta despesa?')) return;

        $.post(API_BASE + '/expenses/' + encodeURIComponent(id) + '/confirm')
            .done(loadExpenses)
            .fail(function () {
                alert('Erro ao confirmar despesa.');
            });
    });

    $wrapper.on('click', '.btn-delete-expense', function () {
        if (!confirm('Remover despesa?')) return;

        const id = $(this).data('id');
        $.ajax({
            url: API_BASE + '/expenses/' + encodeURIComponent(id),
            method: 'DELETE',
            success: loadExpenses,
            error: function () {
                alert('Erro ao remover despesa.');
            }
        });
    });

    loadExpenses();
});
