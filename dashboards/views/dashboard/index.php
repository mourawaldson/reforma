<?php
$total = $data['total'] ?? 0;
$years = $data['years'] ?? [];

$chartLabels = array_keys($years);
$chartValues = array_values($years);
?>

<h1 class="h3 mb-4">Visão Geral – Custo do Imóvel (IR)</h1>

<!-- ========================= -->
<!-- CARD PRINCIPAL -->
<!-- ========================= -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card" style="background:#dcfce7;border:none;">
            <div class="card-body">
                <h6>Custo total acrescível ao imóvel</h6>
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
<h2 class="h5 mb-3">Valor acrescível por ano</h2>

<table class="table table-sm" style="max-width:420px;">
    <thead>
    <tr>
        <th>Ano</th>
        <th class="text-end">Valor acrescível</th>
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
        <canvas id="overviewChart"></canvas>
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

<script>
    (function () {
        const ctx = document.getElementById('overviewChart');
        if (!ctx) return;

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($chartLabels) ?>,
                datasets: [{
                    label: 'Valor acrescível',
                    data: <?= json_encode($chartValues) ?>,
                    backgroundColor: '#86efac'
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    })();
</script>
