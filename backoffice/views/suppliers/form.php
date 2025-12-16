<?php $editing = isset($supplierId) && $supplierId; ?>
<h1 class="h3 mb-3"><?php echo $editing ? 'Editar fornecedor' : 'Novo fornecedor'; ?></h1>

<!-- ALERTA -->
<div id="form-alert" class="alert d-none" role="alert"></div>

<form id="supplier-form" novalidate>
    <input type="hidden" id="supplier-id" value="<?php echo $editing ? (int)$supplierId : ''; ?>">

    <div class="mb-3">
        <label class="form-label">CPF/CNPJ</label>
        <input type="text" class="form-control" id="cpf_cnpj" autofocus>
    </div>

    <!-- PF -->
    <div class="mb-3 pf-only">
        <label class="form-label">Nome</label>
        <input type="text" class="form-control" id="name">
    </div>

    <!-- PJ -->
    <div class="pj-only d-none">
        <div class="mb-3">
            <label class="form-label">Razão Social</label>
            <input type="text" class="form-control" id="company_name">
        </div>
        <div class="mb-3">
            <label class="form-label">Nome Fantasia</label>
            <input type="text" class="form-control" id="trade_name">
        </div>
    </div>

    <button type="submit" class="btn btn-primary">Salvar</button>
    <a href="/suppliers" class="btn btn-secondary">Voltar</a>
</form>

<script src="/assets/js/form-helpers.js"></script>

<script>
    $(function() {
        const API_BASE = 'http://localhost:8000';
        const id = $('#supplier-id').val();
        const FH = window.FormHelpers;
        const $alert = $('#form-alert');

        function showAlert(type, message) {
            $alert
                .removeClass('d-none alert-danger alert-warning alert-info')
                .addClass('alert-' + type)
                .html(message);

            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function hideAlert() {
            $alert.addClass('d-none').html('');
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
</script>
