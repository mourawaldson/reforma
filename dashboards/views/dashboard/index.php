<?php
$total = $data['total'] ?? 0;
$years = $data['years'] ?? [];

$chartLabels = array_keys($years);
$chartValues = array_values($years);
?>

<h1 class="h3 mb-4">Visão Geral</h1>

<!-- ========================= -->
<!-- CARD PRINCIPAL -->
<!-- ========================= -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card dashboard-card dashboard-card--paid">
            <div class="card-body">
                <h6>Custo Total</h6>
                <h2 class="mb-0">
                    R$ <?= number_format($total, 2, ',', '.') ?>
                </h2>
            </div>
        </div>
    </div>
</div>

<!-- ========================= -->
<!-- TABELA POR ANO (CLEAN) -->
<!-- ========================= -->
<h2 class="h5 mb-3">Total por Ano</h2>

<table class="table table-sm dashboard-table dashboard-table--narrow">
    <thead>
    <tr>
        <th>Ano</th>
        <th class="text-end">Valor Total</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($years as $year => $value): ?>
        <tr>
            <td><?= htmlspecialchars($year) ?></td>
            <td class="text-end">
                R$ <?= number_format($value, 2, ',', '.') ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<!-- ========================= -->
<!-- GRÁFICO -->
<!-- ========================= -->
<div class="card mb-4 mt-4">
    <div class="card-body">
        <canvas id="overviewChart"
                data-chart-labels="<?= htmlspecialchars(json_encode($chartLabels, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT), ENT_QUOTES, 'UTF-8') ?>"
                data-chart-values="<?= htmlspecialchars(json_encode($chartValues, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT), ENT_QUOTES, 'UTF-8') ?>"
                data-chart-label="Valor Total"
                data-chart-color="#86efac"></canvas>
    </div>
</div>

<!-- ========================= -->
<!-- NOTA EXPLICATIVA -->
<!-- ========================= -->
<p class="text-muted small">
    Os valores apresentados correspondem exclusivamente a despesas
    <strong>confirmadas e efetivamente pagas</strong>, relacionadas a
    reformas e benfeitorias do imóvel, podendo ser utilizados para
    atualização do custo do imóvel na declaração do Imposto de Renda.
</p>
