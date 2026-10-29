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

<script src="/assets/js/supplier-form.js"></script>
