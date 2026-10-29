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
                return digits.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
            }
            if (digits.length === 14) {
                return digits.replace(
                    /(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/,
                    '$1.$2.$3/$4-$5'
                );
            }
            return value;
        }

        function loadOptions() {
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
                $('#supplier_id').val(e.supplier_id);
                $('#description').val(e.description);

                $('#amount_nf').val(FH.formatCurrencyBRFromNumber(e.amount_nf));
                $('#amount_paid').val(FH.formatCurrencyBRFromNumber(e.amount_paid));
                $('#additional_discount').val(
                    FH.formatCurrencyBRFromNumber(e.additional_discount)
                );

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

            const $date        = $('#date');
            const $description = $('#description');
            const $amountPaid  = $('#amount_paid');

            const dateVal = $date.val();

            const descCheck = FH.validateTextField($description.val(), {
                required: true,
                maxLength: 255
            });

            const amountPaidVal = FH.parseCurrencyToFloat($amountPaid.val());

            if (!dateVal) markInvalid($date);
            if (!descCheck.valid) markInvalid($description);
            if (amountPaidVal === null) markInvalid($amountPaid);

            if (hasError) {
                if (firstInvalid) firstInvalid.focus();
                return;
            }

            const payload = {
                supplier_id: $('#supplier_id').val()
                    ? parseInt($('#supplier_id').val(), 10)
                    : null,
                date: dateVal,
                description: descCheck.value,
                amount_nf: $('#amount_nf').val()
                    ? FH.parseCurrencyToFloat($('#amount_nf').val())
                    : null,
                amount_paid: amountPaidVal,
                additional_discount: $('#additional_discount').val()
                    ? FH.parseCurrencyToFloat($('#additional_discount').val())
                    : null,
                tags: ($('#tags_select').val() || []).map(t => parseInt(t, 10))
            };

            const url = id
                ? API_BASE + '/expenses/' + id
                : API_BASE + '/expenses';
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
