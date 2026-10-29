<?php $editing = isset($tagId) && $tagId; ?>
<h1 class="h3 mb-3"><?php echo $editing ? 'Editar tag' : 'Nova tag'; ?></h1>

<!-- ALERTA -->
<div id="form-alert" class="alert d-none" role="alert"></div>

<form id="tag-form" novalidate>
    <input type="hidden" id="tag-id" value="<?php echo $editing ? (int)$tagId : ''; ?>">

    <div class="mb-3">
        <label for="name" class="form-label">Nome</label>
        <input type="text" class="form-control" id="name" required maxlength="100" autofocus>
        <div class="form-text">Máximo de 100 caracteres.</div>
        <div class="invalid-feedback">
            O nome é obrigatório e deve ter no máximo 100 caracteres.
        </div>
    </div>

    <button type="submit" class="btn btn-primary">Salvar</button>
    <a href="/tags" class="btn btn-secondary">Voltar</a>
</form>

<script src="/assets/js/form-helpers.js"></script>

<script src="/assets/js/tag-form.js"></script>
