<?php $editing = isset($tagId) && $tagId; ?>
<h1 class="h3 mb-3"><?php echo $editing ? 'Editar tag' : 'Nova tag'; ?></h1>

<form id="tag-form" novalidate>
    <input type="hidden" id="tag-id" value="<?php echo $editing ? (int)$tagId : ''; ?>">

    <div class="mb-3">
        <label for="name" class="form-label">Nome</label>
        <input type="text" class="form-control" id="name" required maxlength="100" autofocus>
        <div class="form-text">Máximo de 100 caracteres.</div>
        <div class="invalid-feedback">O nome é obrigatório e deve ter no máximo 100 caracteres.</div>
    </div>

    <button type="submit" class="btn btn-primary">Salvar</button>
    <a href="/tags" class="btn btn-secondary">Voltar</a>
</form>

<script src="/assets/js/form-helpers.js"></script>

<script>
$(function() {
    const API_BASE = 'http://localhost:8000';
    const id = $('#tag-id').val();
    const FH = window.FormHelpers;

    function loadTag(id) {
        $.getJSON(API_BASE + '/tags/' + id, function(t) {
            $('#name').val(t.name);
        });
    }

    if (id) {
        loadTag(id);
    }

    FH.clearInvalidOnChange('#tag-form');

    $('#tag-form').on('submit', function(e) {
        e.preventDefault();

        let hasError = false;
        let firstInvalid = null;

        function markInvalid($el) {
            $el.addClass('is-invalid');
            if (!firstInvalid) firstInvalid = $el;
            hasError = true;
        }

        $('#tag-form').find('.is-invalid').removeClass('is-invalid');

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
        const url = id ? API_BASE + '/tags/' + id : API_BASE + '/tags';
        const method = id ? 'PUT' : 'POST';

        $.ajax({
            url,
            method,
            data: JSON.stringify(payload),
            contentType: 'application/json',
            success: function() {
                window.location.href = '/tags';
            },
            error: function(xhr) {
                alert('Erro ao salvar tag: ' + xhr.responseText);
            }
        });
    });
});
</script>
