<?php $editing = isset($expenseId) && $expenseId; ?>
<h1 class="h3 mb-3"><?php echo $editing ? 'Editar despesa' : 'Nova despesa'; ?></h1>

<form id="expense-form" novalidate>
    <input type="hidden" id="expense-id" value="<?php echo $editing ? (int)$expenseId : ''; ?>">

    <div class="row mb-3">
        <div class="col-md-3">
            <label for="date" class="form-label">Data</label>
            <input type="date" class="form-control" id="date" required autofocus>
            <div class="invalid-feedback">Informe a data da despesa.</div>
        </div>
        <div class="col-md-3">
            <label for="category_id" class="form-label">Categoria</label>
            <select class="form-select" id="category_id" required>
                <option value="">Selecione...</option>
            </select>
            <div class="invalid-feedback">Selecione uma categoria.</div>
        </div>
        <div class="col-md-3">
            <label for="supplier_id" class="form-label">Fornecedor</label>
            <select class="form-select" id="supplier_id">
                <option value="">(Opcional)</option>
            </select>
            <div class="invalid-feedback">Fornecedor inválido.</div>
        </div>
    </div>

    <div class="mb-3">
        <label for="description" class="form-label">Descrição</label>
        <input type="text" class="form-control" id="description" required maxlength="255">
        <div class="form-text">Máximo de 255 caracteres.</div>
        <div class="invalid-feedback">A descrição é obrigatória e deve ter no máximo 255 caracteres.</div>
    </div>

    <div class="row mb-3">
        <div class="col-md-3">
            <label for="amount_nf" class="form-label">Valor NF</label>
            <input type="text" class="form-control currency" id="amount_nf" placeholder="R$ 0,00">
            <div class="invalid-feedback">Valor de NF inválido.</div>
        </div>
        <div class="col-md-3">
            <label for="amount_paid" class="form-label">Valor Pago</label>
            <input type="text" class="form-control currency" id="amount_paid" placeholder="R$ 0,00" required>
            <div class="invalid-feedback">Informe um valor pago válido.</div>
        </div>
        <div class="col-md-6">
            <label for="tags_select" class="form-label">Tags</label>
            <select id="tags_select" class="form-select" multiple></select>
            <div class="form-text">Segure Ctrl (Windows) ou Command (Mac) para selecionar múltiplas.</div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary">Salvar</button>
    <a href="/expenses" class="btn btn-secondary">Voltar</a>
</form>

<script src="/assets/js/form-helpers.js"></script>

<script>
$(function() {
    const API_BASE = 'http://localhost:8000';
    const id = $('#expense-id').val();
    const FH = window.FormHelpers;

    FH.attachCurrencyMask('.currency');
    FH.clearInvalidOnChange('#expense-form');

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

    function loadOptions() {
        $.getJSON(API_BASE + '/categories', function(data) {
            const $sel = $('#category_id');
            data.forEach(function(c) {
                $sel.append('<option value="' + c.id + '">' + c.name + '</option>');
            });
        });

        $.getJSON(API_BASE + '/suppliers', function(data) {
            const $sel = $('#supplier_id');

            data.forEach(function(s) {
                let label = s.display_name || '';

                if (s.cpf_cnpj) {
                    label += ' — ' + formatCpfCnpj(s.cpf_cnpj);
                }

                $sel.append(
                    '<option value="' + s.id + '">' + label + '</option>'
                );
            });
        });

        $.getJSON(API_BASE + '/tags', function(data) {
            const $sel = $('#tags_select');
            data.forEach(function(t) {
                $sel.append('<option value="' + t.id + '">' + t.name + '</option>');
            });
        });
    }

    function loadExpense(id) {
        $.getJSON(API_BASE + '/expenses/' + id, function(e) {
            $('#date').val(e.date);
            $('#category_id').val(e.category_id);
            $('#supplier_id').val(e.supplier_id);
            $('#description').val(e.description);

            $('#amount_nf').val(FH.formatCurrencyBRFromNumber(e.amount_nf));
            $('#amount_paid').val(FH.formatCurrencyBRFromNumber(e.amount_paid));

            const tagIds = (e.tags || []).map(t => String(t.id));
            $('#tags_select option').each(function() {
                if (tagIds.includes($(this).val())) {
                    $(this).prop('selected', true);
                }
            });
        });
    }

    loadOptions();
    if (id) {
        setTimeout(function() { loadExpense(id); }, 300);
    }

    $('#expense-form').on('submit', function(e) {
        e.preventDefault();

        let hasError = false;
        let firstInvalid = null;

        function markInvalid($el) {
            $el.addClass('is-invalid');
            if (!firstInvalid) firstInvalid = $el;
            hasError = true;
        }

        $('#expense-form').find('.is-invalid').removeClass('is-invalid');

        const $date = $('#date');
        const $category = $('#category_id');
        const $description = $('#description');
        const $amountPaid = $('#amount_paid');
        const $amountNf = $('#amount_nf');

        const dateVal = $date.val();
        const categoryVal = $category.val();

        const descCheck = FH.validateTextField($description.val(), {
            required: true,
            maxLength: 255
        });

        const amountPaidVal = FH.parseCurrencyToFloat($amountPaid.val());
        const amountNfVal = $amountNf.val() ? FH.parseCurrencyToFloat($amountNf.val()) : null;

        if (!dateVal) {
            markInvalid($date);
        }

        if (!categoryVal) {
            markInvalid($category);
        }

        if (!descCheck.valid) {
            markInvalid($description);
        }

        if (amountPaidVal === null) {
            markInvalid($amountPaid);
        }

        if (hasError) {
            if (firstInvalid) firstInvalid.focus();
            return;
        }

        const selectedTags = $('#tags_select').val() || [];

        const payload = {
            category_id: parseInt(categoryVal, 10),
            supplier_id: $('#supplier_id').val() ? parseInt($('#supplier_id').val(), 10) : null,
            date: dateVal,
            description: descCheck.value,
            amount_nf: amountNfVal,
            amount_paid: amountPaidVal,
            tags: selectedTags.map(t => parseInt(t, 10))
        };

        const url = id ? API_BASE + '/expenses/' + id : API_BASE + '/expenses';
        const method = id ? 'PUT' : 'POST';

        $.ajax({
            url,
            method,
            data: JSON.stringify(payload),
            contentType: 'application/json',
            success: function() {
                window.location.href = '/expenses';
            },
            error: function(xhr) {
                alert('Erro ao salvar despesa: ' + xhr.responseText);
            }
        });
    });
});
</script>
