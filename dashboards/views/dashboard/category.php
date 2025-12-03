<?php
// Espera-se que $data['years'] seja um array associativo:
// [
//   2024 => ['summary' => ['total_paid'=>..., 'total_nf'=>...], 'data' => [...]],
//   2025 => ['summary' => ['total_paid'=>..., 'total_nf'=>...], 'data' => [...]],
// ]

$years = $data['years'] ?? [];

// Totais gerais somando todos os anos
$overallPaid = 0;
$overallNf   = 0;
$chartConfigs = [];

foreach ($years as $year => $yearData) {
    $summary = $yearData['summary'] ?? ['total_paid' => 0, 'total_nf' => 0];
    $overallPaid += (float)($summary['total_paid'] ?? 0);
    $overallNf   += (float)($summary['total_nf'] ?? 0);

    $labels = array_column($yearData['data'] ?? [], 'category_name');
    $values = array_map('floatval', array_column($yearData['data'] ?? [], 'total_paid'));

    $chartConfigs[] = [
        'id'     => 'categoryChart_' . $year,
        'year'   => $year,
        'labels' => $labels,
        'values' => $values,
    ];
}

$overallDiff = $overallNf - $overallPaid;
?>

<h1 class="h3 mb-3">Dashboard por Categoria</h1>

<!-- Cards de Total Geral (todos os anos) -->
<div class="row mb-4">

    <!-- NF primeiro - Azul pastel -->
    <div class="col-md-4">
        <div class="card" style="background-color:#dbeafe;border:none;">
            <div class="card-body">
                <h6 class="mb-2">Total NF (Geral)</h6>
                <h3 class="mb-0">R$ <?= number_format($overallNf, 2, ',', '.') ?></h3>
            </div>
        </div>
    </div>

    <!-- Pago - Verde pastel -->
    <div class="col-md-4">
        <div class="card" style="background-color:#dcfce7;border:none;">
            <div class="card-body">
                <h6 class="mb-2">Total Pago (Geral)</h6>
                <h3 class="mb-0">R$ <?= number_format($overallPaid, 2, ',', '.') ?></h3>
            </div>
        </div>
    </div>

    <!-- Diferença - Amarelo pastel -->
    <div class="col-md-4">
        <div class="card" style="background-color:#fef9c3;border:none;">
            <div class="card-body">
                <h6 class="mb-2">Diferença (NF - Pago) Geral</h6>
                <h3 class="mb-0">R$ <?= number_format($overallDiff, 2, ',', '.') ?></h3>
            </div>
        </div>
    </div>

</div>

<?php foreach ($years as $year => $yearData): ?>

<?php
    $summary    = $yearData['summary'];
    $totalPaid  = (float)$summary['total_paid'];
    $totalNf    = (float)$summary['total_nf'];
    $diffNfPaid = $totalNf - $totalPaid;

    $rows       = $yearData['data'] ?? [];
    $chartId    = 'categoryChart_' . $year;

    $labels     = array_column($rows, 'category_name');
    $values     = array_map('floatval', array_column($rows, 'total_paid'));
?>

<hr class="my-4">

<h2 class="h5 mb-3">Ano <?= htmlspecialchars($year) ?></h2>

<!-- Cards por ano -->
<div class="row mb-4">

    <!-- NF - Azul pastel -->
    <div class="col-md-4">
        <div class="card" style="background-color:#dbeafe;border:none;">
            <div class="card-body">
                <h6 class="mb-2">Total NF (<?= $year ?>)</h6>
                <h3 class="mb-0">R$ <?= number_format($totalNf, 2, ',', '.') ?></h3>
            </div>
        </div>
    </div>

    <!-- Pago - Verde pastel -->
    <div class="col-md-4">
        <div class="card" style="background-color:#dcfce7;border:none;">
            <div class="card-body">
                <h6 class="mb-2">Total Pago (<?= $year ?>)</h6>
                <h3 class="mb-0">R$ <?= number_format($totalPaid, 2, ',', '.') ?></h3>
            </div>
        </div>
    </div>

    <!-- Diferença - Amarelo pastel -->
    <div class="col-md-4">
        <div class="card" style="background-color:#fef9c3;border:none;">
            <div class="card-body">
                <h6 class="mb-2">Diferença (NF - Pago) (<?= $year ?>)</h6>
                <h3 class="mb-0">R$ <?= number_format($diffNfPaid, 2, ',', '.') ?></h3>
            </div>
        </div>
    </div>

</div>

<!-- Gráfico + tabela -->
<div class="row mb-4">
    <div class="col-md-6">
        <canvas id="<?= $chartId ?>"></canvas>
    </div>

    <div class="col-md-6">
        <div class="table-responsive">
            <table class="table table-sm table-striped align-middle">
                <thead>
                    <tr>
                        <th>Categoria</th>
                        <th class="text-end">Pago</th>
                        <th class="text-end">% do total (<?= $year ?>)</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($rows): ?>
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['category_name']) ?></td>
                            <td class="text-end">R$ <?= number_format($row['total_paid'], 2, ',', '.') ?></td>
                            <td class="text-end"><?= number_format($row['percentage_paid'], 2, ',', '.') ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="3" class="text-center text-muted">Sem dados.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php endforeach; ?>

<script>
(function() {
    const chartsData = <?= json_encode($chartConfigs) ?>;

    chartsData.forEach(function(cfg) {
        const canvas = document.getElementById(cfg.id);
        if (!canvas) return;

        const ctx = canvas.getContext('2d');

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: cfg.labels,
                datasets: [{
                    label: 'Total Pago (' + cfg.year + ')',
                    data: cfg.values,
                    backgroundColor: '#93c5fd' // barra azul pastel suave
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    });
})();
</script>
