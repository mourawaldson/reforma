<?php $editing = isset($supplierId) && $supplierId; ?>
<h1 class="h3 mb-3"><?php echo $editing ? 'Editar fornecedor' : 'Novo fornecedor'; ?></h1>

<form id="supplier-form" novalidate>
    <input type="hidden" id="supplier-id" value="<?php echo $editing ? (int)$supplierId : ''; ?>">

    <div class="mb-3">
        <label for="name" class="form-label">Nome</label>
        <input type="text" class="form-control" id="name" required maxlength="255">
        <div class="form-text">Máximo de 255 caracteres.</div>
        <div class="invalid-feedback">O nome é obrigatório e deve ter no máximo 255 caracteres.</div>
    </div>

    <div class="mb-3">
        <label for="type" class="form-label">Tipo</label>
        <input type="text" class="form-control" id="type" placeholder="Ex.: loja, prestador, profissional" maxlength="100">
        <div class="form-text">Máximo de 100 caracteres (opcional).</div>
        <div class="invalid-feedback">O tipo deve ter no máximo 100 caracteres.</div>
    </div>

    <div class="mb-3">
        <label for="cpf_cnpj" class="form-label">CPF/CNPJ</label>
        <input type="text" class="form-control" id="cpf_cnpj" maxlength="20">
        <div class="form-text">Máximo de 20 caracteres. Se preenchido, será validado como CPF ou CNPJ.</div>
        <div class="invalid-feedback">CPF/CNPJ inválido.</div>
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

    function loadSupplier(id) {
        $.getJSON(API_BASE + '/suppliers/' + id, function(s) {
            $('#name').val(s.name);
            $('#type').val(s.type);
            $('#cpf_cnpj').val(s.cpf_cnpj);
        });
    }

    if (id) {
        loadSupplier(id);
    }

    FH.clearInvalidOnChange('#supplier-form');
    FH.attachCpfCnpjMask('#cpf_cnpj');

    $('#supplier-form').on('submit', function(e) {
        e.preventDefault();

        let hasError = false;
        let firstInvalid = null;

        function markInvalid($el) {
            $el.addClass('is-invalid');
            if (!firstInvalid) firstInvalid = $el;
            hasError = true;
        }

        $('#supplier-form').find('.is-invalid').removeClass('is-invalid');

        const nameCheck = FH.validateTextField($('#name').val(), {
            required: true,
            maxLength: 255
        });
        const typeCheck = FH.validateTextField($('#type').val(), {
            required: false,
            maxLength: 100
        });
        const cpfVal = ($('#cpf_cnpj').val() || '').trim();

        if (!nameCheck.valid) {
            markInvalid($('#name'));
        }
        if (!typeCheck.valid) {
            markInvalid($('#type'));
        }

        if (cpfVal && !FH.validateCpfCnpj(cpfVal)) {
            markInvalid($('#cpf_cnpj'));
        }

        if (hasError) {
            if (firstInvalid) firstInvalid.focus();
            return;
        }

        const payload = {
            name: nameCheck.value,
            type: typeCheck.value,
            cpf_cnpj: cpfVal
        };
        const url = id ? API_BASE + '/suppliers/' + id : API_BASE + '/suppliers';
        const method = id ? 'PUT' : 'POST';

        $.ajax({
            url,
            method,
            data: JSON.stringify(payload),
            contentType: 'application/json',
            success: function() {
                window.location.href = '/suppliers';
            },
            error: function(xhr) {
                alert('Erro ao salvar fornecedor: ' + xhr.responseText);
            }
        });
    });
});
</script>
