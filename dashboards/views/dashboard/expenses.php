<?php
$years   = $data['years']   ?? [];

$pending = $data['pending'] ?? [
        'summary' => [
                'count'      => 0,
                'total_paid' => 0,
        ],
        'years' => [],
];

$capitalIncrease = $data['capital_increase'] ?? [
        'summary' => ['count' => 0, 'total_paid' => 0],
        'years' => [],
        'items' => [],
];

$discounts = $data['discounts'] ?? [
        'items' => [],
        'totals' => [
                'amount_paid' => 0,
                'additional_discount' => 0,
                'vet' => 0,
        ],
];

// =========================
// CONFIRMADAS – TOTAIS GERAIS
// =========================
$overallPaid = 0;
$overallNf   = 0;

$chartLabels = [];
$chartValues = [];

foreach ($years as $year => $yearData) {
    $summary = $yearData['summary'] ?? [
            'total_paid' => 0,
            'total_nf'   => 0,
    ];

    $overallPaid += (float)$summary['total_paid'];
    $overallNf   += (float)$summary['total_nf'];

    $chartLabels[] = (string)$year;
    $chartValues[] = (float)$summary['total_paid'];
}

$overallDiff = $overallNf - $overallPaid;

$capitalIncreaseYears = $capitalIncrease['years'] ?? [];
$capitalIncreaseItems = $capitalIncrease['items'] ?? [];
$capitalIncreaseChartLabels = array_map('strval', array_keys($capitalIncreaseYears));
$capitalIncreaseChartValues = array_map('floatval', array_values($capitalIncreaseYears));
?>

<div class="dashboard-expenses">
<h1 class="h3 mb-4">Dashboard de Despesas</h1>

<!-- ================================================= -->
<!-- 1. DESPESAS PENDENTES -->
<!-- ================================================= -->
<h2 class="h5 mb-3">Despesas pendentes</h2>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="card dashboard-card dashboard-card--pending">
            <div class="card-body">
                <h6>Quantidade</h6>
                <h3><?php echo (int)$pending['summary']['count']; ?></h3>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card dashboard-card dashboard-card--neutral">
            <div class="card-body">
                <h6>Total</h6>
                <h3>R$ <?php echo number_format($pending['summary']['total_paid'], 2, ',', '.'); ?></h3>
            </div>
        </div>
    </div>
</div>

<?php foreach ($pending['years'] as $year => $p): ?>
    <div class="border-top pt-4 mt-4">
        <h3 class="h6 mb-3">Ano <?php echo htmlspecialchars($year); ?> – Pendentes</h3>

        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card dashboard-card dashboard-card--pending">
                    <div class="card-body">
                        <h6>Quantidade</h6>
                        <h3><?php echo (int)$p['count']; ?></h3>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card dashboard-card dashboard-card--neutral">
                    <div class="card-body">
                        <h6>Total</h6>
                        <h3>R$ <?php echo number_format($p['total_paid'], 2, ',', '.'); ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<!-- ================================================= -->
<!-- 2. DESPESAS CONFIRMADAS -->
<!-- ================================================= -->
<h2 class="h5 mt-5 mb-3">Despesas confirmadas</h2>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card dashboard-card dashboard-card--invoice">
            <div class="card-body">
                <h6>Total NF (Geral)</h6>
                <h3>R$ <?php echo number_format($overallNf, 2, ',', '.'); ?></h3>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card dashboard-card dashboard-card--paid">
            <div class="card-body">
                <h6>Total Pago (Geral)</h6>
                <h3>R$ <?php echo number_format($overallPaid, 2, ',', '.'); ?></h3>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card dashboard-card dashboard-card--difference">
            <div class="card-body">
                <h6>Diferença (NF - Pago)</h6>
                <h3>R$ <?php echo number_format($overallDiff, 2, ',', '.'); ?></h3>
            </div>
        </div>
    </div>
</div>

<h3 class="h6 mb-3">Total pago por ano (confirmadas)</h3>

<div class="card mb-5">
    <div class="card-body">
        <canvas id="confirmedYearChart"></canvas>
    </div>
</div>

<?php foreach ($years as $year => $yearData): ?>
    <?php
    $summary = $yearData['summary'] ?? [
            'total_paid' => 0,
            'total_nf'   => 0,
    ];
    $diff = (float)$summary['total_nf'] - (float)$summary['total_paid'];
    ?>
    <div class="border-top pt-4 mt-4">
        <h3 class="h6 mb-3">Ano <?php echo htmlspecialchars($year); ?> – Confirmadas</h3>

        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card dashboard-card dashboard-card--invoice">
                    <div class="card-body">
                        <h6>Total NF</h6>
                        <h3>R$ <?php echo number_format($summary['total_nf'], 2, ',', '.'); ?></h3>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card dashboard-card dashboard-card--paid">
                    <div class="card-body">
                        <h6>Total Pago</h6>
                        <h3>R$ <?php echo number_format($summary['total_paid'], 2, ',', '.'); ?></h3>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card dashboard-card dashboard-card--attention">
                    <div class="card-body">
                        <h6>Diferença</h6>
                        <h3>R$ <?php echo number_format($diff, 2, ',', '.'); ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<!-- ================================================= -->
<!-- GASTOS COM A TAG AUMENTO DE CAPITAL -->
<!-- ================================================= -->
<section class="border-top pt-4 mt-5">
    <h2 class="h5 mb-3">Gastos com a tag Aumento de Capital</h2>
    <p class="text-muted">Somente despesas confirmadas.</p>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card dashboard-card dashboard-card--paid">
                <div class="card-body">
                    <h6>Total pago</h6>
                    <h3>R$ <?php echo number_format((float)($capitalIncrease['summary']['total_paid'] ?? 0), 2, ',', '.'); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card dashboard-card dashboard-card--neutral">
                <div class="card-body">
                    <h6>Despesas confirmadas</h6>
                    <h3><?php echo (int)($capitalIncrease['summary']['count'] ?? 0); ?></h3>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($capitalIncreaseYears)): ?>
        <div class="row mb-4">
            <?php foreach ($capitalIncreaseYears as $year => $totalPaid): ?>
                <div class="col-sm-6 col-lg-4 mb-3">
                    <div class="card dashboard-card dashboard-card--paid h-100">
                        <div class="card-body">
                            <h6>Ano <?php echo htmlspecialchars((string)$year); ?></h6>
                            <h3>R$ <?php echo number_format((float)$totalPaid, 2, ',', '.'); ?></h3>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <h3 class="h6 mb-3">Total pago por ano</h3>
    <?php if (!empty($capitalIncreaseYears)): ?>
        <div class="card mb-4">
            <div class="card-body">
                <canvas id="capitalIncreaseChart"></canvas>
            </div>
        </div>
    <?php else: ?>
        <p class="text-muted">Não há despesas confirmadas com essa tag.</p>
    <?php endif; ?>

    <?php if (!empty($capitalIncreaseItems)): ?>
        <div class="table-responsive expenses-table-shell mb-5">
            <table class="table table-sm table-capital-increase">
                <colgroup>
                    <col class="col-date">
                    <col class="col-supplier">
                    <col class="col-description">
                    <col class="col-paid">
                </colgroup>
                <thead class="table-light">
                <tr>
                    <th class="col-date">Data</th>
                    <th class="col-supplier text-start">Fornecedor</th>
                    <th class="col-description">Descrição</th>
                    <th class="text-end col-paid">Pago</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($capitalIncreaseItems as $item): ?>
                    <tr>
                        <td><?php echo !empty($item['date']) ? date('d/m/Y', strtotime($item['date'])) : '-'; ?></td>
                        <td class="col-supplier text-start"><?php echo htmlspecialchars($item['supplier_name'] ?? '-') ?: '-'; ?></td>
                        <td class="col-description"><?php echo htmlspecialchars($item['description'] ?? '-') ?: '-'; ?></td>
                        <td class="text-end col-paid">R$ <?php echo number_format((float)($item['amount_paid'] ?? 0), 2, ',', '.'); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot>
                <tr>
                    <th colspan="3">Total pago</th>
                    <th class="text-end col-paid">R$ <?php echo number_format((float)($capitalIncrease['summary']['total_paid'] ?? 0), 2, ',', '.'); ?></th>
                </tr>
                </tfoot>
            </table>
        </div>
    <?php endif; ?>
</section>

<!-- ================================================= -->
<!-- 3. DESCONTOS -->
<!-- ================================================= -->
<h2 class="h5 mt-5 mb-3">Descontos</h2>

<?php if (!empty($discounts['items'])): ?>
    <div class="table-responsive expenses-table-shell">
        <table class="table table-sm table-discounts">
            <thead class="table-light">
            <tr>
                <th>Data</th>
                <th class="text-start">Fornecedor</th>
                <th>Descrição</th>
                <th class="text-end">Pago</th>
                <th class="text-end">Desconto</th>
                <th class="text-end">VET</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($discounts['items'] as $row): ?>
                <tr>
                    <td><?php echo !empty($row['date']) ? date('d/m/Y', strtotime($row['date'])) : '-'; ?></td>
                    <td class="text-start"><?php echo htmlspecialchars($row['supplier_name'] ?? '-') ?: '-'; ?></td>
                    <td class="col-description"
                        title="<?php echo htmlspecialchars($row['description'] ?? '-'); ?>">
                        <?php echo htmlspecialchars($row['description'] ?? '-') ?: '-'; ?>
                    </td>
                    <td class="text-end">
                        <?php echo isset($row['amount_paid']) && $row['amount_paid'] !== '' ? 'R$ ' . number_format((float)$row['amount_paid'], 2, ',', '.') : '-'; ?>
                    </td>
                    <td class="text-end">
                        <?php echo isset($row['additional_discount']) && $row['additional_discount'] !== '' ? 'R$ ' . number_format((float)$row['additional_discount'], 2, ',', '.') : '-'; ?>
                    </td>
                    <td class="text-end vet-value">
                        <?php echo isset($row['vet']) && $row['vet'] !== '' ? 'R$ ' . number_format((float)$row['vet'], 2, ',', '.') : '-'; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
            <tr>
                <th colspan="3" class="text-end">Totais</th>
                <th class="text-end">
                    R$ <?php echo number_format($discounts['totals']['amount_paid'], 2, ',', '.'); ?>
                </th>
                <th class="text-end">
                    R$ <?php echo number_format($discounts['totals']['additional_discount'], 2, ',', '.'); ?>
                </th>
                <th class="text-end vet-value">
                    R$ <?php echo number_format($discounts['totals']['vet'], 2, ',', '.'); ?>
                </th>
            </tr>
            </tfoot>
        </table>
    </div>
<?php else: ?>
    <p class="text-muted">Não há despesas com desconto para exibir.</p>
<?php endif; ?>

<script>
    (function () {
        const capitalIncreaseCtx = document.getElementById('capitalIncreaseChart');
        if (capitalIncreaseCtx) {
            new Chart(capitalIncreaseCtx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($capitalIncreaseChartLabels); ?>,
                    datasets: [{
                        label: 'Total pago',
                        data: <?php echo json_encode($capitalIncreaseChartValues); ?>,
                        backgroundColor: '#86efac'
                    }]
                },
                options: {
                    responsive: true,
                    scales: { y: { beginAtZero: true } }
                }
            });
        }

        const ctx = document.getElementById('confirmedYearChart');
        if (!ctx) return;

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($chartLabels); ?>,
                datasets: [{
                    label: 'Total Pago',
                    data: <?php echo json_encode($chartValues); ?>,
                    backgroundColor: '#93c5fd'
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
</div>
