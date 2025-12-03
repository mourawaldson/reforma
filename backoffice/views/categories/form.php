<?php $editing = isset($categoryId) && $categoryId; ?>
<h1 class="h3 mb-3"><?php echo $editing ? 'Editar categoria' : 'Nova categoria'; ?></h1>

<form id="category-form" novalidate>
    <input type="hidden" id="category-id" value="<?php echo $editing ? (int)$categoryId : ''; ?>">

    <div class="mb-3">
        <label for="name" class="form-label">Nome</label>
        <input type="text" class="form-control" id="name" required maxlength="100">
        <div class="form-text">Máximo de 100 caracteres.</div>
        <div class="invalid-feedback">O nome é obrigatório e deve ter no máximo 100 caracteres.</div>
    </div>

    <button type="submit" class="btn btn-primary">Salvar</button>
    <a href="/categories" class="btn btn-secondary">Voltar</a>
</form>

<script src="/assets/js/form-helpers.js"></script>

<script>
$(function() {
    const API_BASE = 'http://localhost:8000';
    const id = $('#category-id').val();
    const FH = window.FormHelpers;

    function loadCategory(id) {
        $.getJSON(API_BASE + '/categories/' + id, function(c) {
            $('#name').val(c.name);
        });
    }

    if (id) {
        loadCategory(id);
    }

    FH.clearInvalidOnChange('#category-form');

    $('#category-form').on('submit', function(e) {
        e.preventDefault();

        let hasError = false;
        let firstInvalid = null;

        function markInvalid($el) {
            $el.addClass('is-invalid');
            if (!firstInvalid) firstInvalid = $el;
            hasError = true;
        }

        $('#category-form').find('.is-invalid').removeClass('is-invalid');

        const nameCheck = FH.validateTextField($('#name').val(), {
            required: true,
            maxLength: 100
        });

        if (!nameCheck.valid) {
            markInvalid($('#name'));
        }

        if (hasError) {
            if (firstInvalid) firstInvalid.focus();
            return;
        }

        const payload = { name: nameCheck.value };
        const url = id ? API_BASE + '/categories/' + id : API_BASE + '/categories';
        const method = id ? 'PUT' : 'POST';

        $.ajax({
            url,
            method,
            data: JSON.stringify(payload),
            contentType: 'application/json',
            success: function() {
                window.location.href = '/categories';
            },
            error: function(xhr) {
                alert('Erro ao salvar categoria: ' + xhr.responseText);
            }
        });
    });
});
</script>
