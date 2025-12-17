<?php
$years   = $data['years']   ?? [];

$pending = $data['pending'] ?? [
        'summary' => [
                'count'      => 0,
                'total_paid' => 0,
        ],
        'years' => [],
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
?>

<style>
    .table-discounts th {
        white-space: nowrap;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: .03em;
    }

    .table-discounts td {
        vertical-align: middle;
    }

    .table-discounts .col-description {
        max-width: 420px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .table-discounts tfoot th {
        background-color: #f8fafc;
        font-weight: 600;
    }

    .vet-value {
        font-weight: 600;
        color: #0f172a;
    }
</style>

<h1 class="h3 mb-4">Dashboard de Despesas</h1>

<!-- ================================================= -->
<!-- 1. DESPESAS PENDENTES -->
<!-- ================================================= -->
<h2 class="h5 mb-3">Despesas pendentes</h2>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="card" style="background-color:#fee2e2;border:none;">
            <div class="card-body">
                <h6>Quantidade</h6>
                <h3><?php echo (int)$pending['summary']['count']; ?></h3>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card" style="background-color:#f3f4f6;border:none;">
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
                <div class="card" style="background-color:#fee2e2;border:none;">
                    <div class="card-body">
                        <h6>Quantidade</h6>
                        <h3><?php echo (int)$p['count']; ?></h3>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card" style="background-color:#f3f4f6;border:none;">
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
        <div class="card" style="background-color:#dbeafe;border:none;">
            <div class="card-body">
                <h6>Total NF (Geral)</h6>
                <h3>R$ <?php echo number_format($overallNf, 2, ',', '.'); ?></h3>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card" style="background-color:#dcfce7;border:none;">
            <div class="card-body">
                <h6>Total Pago (Geral)</h6>
                <h3>R$ <?php echo number_format($overallPaid, 2, ',', '.'); ?></h3>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card" style="background-color:#ede9fe;border:none;">
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
                <div class="card" style="background-color:#dbeafe;border:none;">
                    <div class="card-body">
                        <h6>Total NF</h6>
                        <h3>R$ <?php echo number_format($summary['total_nf'], 2, ',', '.'); ?></h3>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card" style="background-color:#dcfce7;border:none;">
                    <div class="card-body">
                        <h6>Total Pago</h6>
                        <h3>R$ <?php echo number_format($summary['total_paid'], 2, ',', '.'); ?></h3>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card" style="background-color:#fef9c3;border:none;">
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
<!-- 3. DESCONTOS -->
<!-- ================================================= -->
<h2 class="h5 mt-5 mb-3">Descontos</h2>

<?php if (!empty($discounts['items'])): ?>
    <div class="table-responsive">
        <table class="table table-sm table-striped table-hover table-discounts">
            <thead class="table-light">
            <tr>
                <th>Data</th>
                <th>Fornecedor</th>
                <th>Descrição</th>
                <th class="text-end">Pago</th>
                <th class="text-end">Desconto</th>
                <th class="text-end">VET</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($discounts['items'] as $row): ?>
                <tr>
                    <td><?php echo date('d/m/Y', strtotime($row['date'])); ?></td>
                    <td><?php echo htmlspecialchars($row['supplier_name']); ?></td>
                    <td class="col-description"
                        title="<?php echo htmlspecialchars($row['description']); ?>">
                        <?php echo htmlspecialchars($row['description']); ?>
                    </td>
                    <td class="text-end">
                        R$ <?php echo number_format($row['amount_paid'], 2, ',', '.'); ?>
                    </td>
                    <td class="text-end">
                        R$ <?php echo number_format($row['additional_discount'], 2, ',', '.'); ?>
                    </td>
                    <td class="text-end vet-value">
                        R$ <?php echo number_format($row['vet'], 2, ',', '.'); ?>
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
