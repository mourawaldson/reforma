<?php $editing = isset($expenseId) && $expenseId; ?>
<h1 class="h3 mb-3"><?php echo $editing ? 'Editar despesa' : 'Nova despesa'; ?></h1>

<form id="expense-form" novalidate>
    <input type="hidden" id="expense-id" value="<?php echo $editing ? (int)$expenseId : ''; ?>">

    <div class="row mb-3">
        <div class="col-md-3">
            <label for="date" class="form-label">Data</label>
            <input type="date" class="form-control" id="date" required autofocus value="<?php echo date('Y-m-d'); ?>">
            <div class="invalid-feedback">Informe a data da despesa.</div>
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
        <div class="invalid-feedback">
            A descrição é obrigatória e deve ter no máximo 255 caracteres.
        </div>
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

        <div class="col-md-3">
            <label for="additional_discount" class="form-label">Desconto adicional</label>
            <input type="text" class="form-control currency" id="additional_discount" placeholder="R$ 0,00">
            <div class="form-text">Opcional. Usado apenas para conferência.</div>
        </div>

        <div class="col-md-3">
            <label for="tags_select" class="form-label">Tags</label>
            <select id="tags_select" class="form-select" multiple></select>
            <div class="form-text">
                Segure Ctrl (Windows) ou Command (Mac) para selecionar múltiplas.
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary">Salvar</button>
    <a href="/expenses" class="btn btn-secondary">Voltar</a>
</form>

<script src="/assets/js/form-helpers.js"></script>

<script src="/assets/js/expense-form.js"></script>
