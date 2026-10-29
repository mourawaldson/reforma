$(function() {
        const API_BASE = 'http://localhost:8000';
        const id = $('#supplier-id').val();
        const FH = window.FormHelpers;
        const $alert = $('#form-alert');

        function showAlert(type, message) {
            $alert
                .removeClass('d-none alert-danger alert-warning alert-info')
                .addClass('alert-' + type)
                .text(message);

            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function hideAlert() {
            $alert.addClass('d-none').empty();
        }

        // Máscara
        FH.attachCpfCnpjMask('#cpf_cnpj');

        function toggleFields() {
            const digits = ($('#cpf_cnpj').val() || '').replace(/\D/g, '');
            const isPJ = digits.length === 14;

            $('.pf-only').toggle(!isPJ);
            $('.pj-only').toggleClass('d-none', !isPJ);
        }

        $('#cpf_cnpj').on('input', function() {
            hideAlert();
            toggleFields();
        });

        $('#name, #trade_name, #company_name').on('input', hideAlert);

        // Edição
        if (id) {
            $.getJSON(API_BASE + '/suppliers/' + id, function(s) {
                $('#cpf_cnpj').val(s.cpf_cnpj || '').trigger('input');
                $('#name').val(s.name || '');
                $('#company_name').val(s.company_name || '');
                $('#trade_name').val(s.name || '');
                toggleFields();
            });
        }

        // Submit
        $('#supplier-form').on('submit', function(e) {
            e.preventDefault();
            hideAlert();

            const cpf = ($('#cpf_cnpj').val() || '').trim();

            if (cpf && !FH.validateCpfCnpj(cpf)) {
                showAlert('warning', 'O CPF ou CNPJ informado é inválido. Verifique os números digitados.');
                return;
            }

            const isPJ = cpf.replace(/\D/g, '').length === 14;

            const payload = {
                cpf_cnpj: cpf,
                name: isPJ ? $('#trade_name').val().trim() : $('#name').val().trim(),
                company_name: isPJ ? $('#company_name').val().trim() : null
            };

            $.ajax({
                url: id ? API_BASE + '/suppliers/' + id : API_BASE + '/suppliers',
                method: id ? 'PUT' : 'POST',
                data: JSON.stringify(payload),
                contentType: 'application/json'
            })
                .done(function() {
                    window.location.href = '/suppliers';
                })
                .fail(function(xhr) {
                    let message = 'Não foi possível salvar o fornecedor. Verifique os dados e tente novamente.';

                    // 1) JSON correto
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        message = xhr.responseJSON.error;
                    }
                    // 2) JSON como string (unicode escapado)
                    else if (xhr.responseText) {
                        try {
                            const parsed = JSON.parse(xhr.responseText);
                            if (parsed && parsed.error) {
                                message = parsed.error;
                            } else {
                                message = xhr.responseText;
                            }
                        } catch (e) {
                            // 3) texto simples / HTML
                            message = xhr.responseText.replace(/<[^>]*>?/gm, '').trim();
                        }
                    }

                    showAlert('danger', message);
                });


        });
    });
