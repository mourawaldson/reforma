<?php
// Estrutura esperada de $data:
// $data = [
//   'years' => [
//      2024 => [
//          'summary' => ['total_paid' => ..., 'total_nf' => ...],
//          'data'    => [...],
//      ],
//      2025 => [...],
//   ],
//   'pending' => [
//      'count'        => 3,
//      'total_paid'   => 1000.00,
//      'total_nf'     => 1500.00,
//      'diff_nf_paid' => 500.00,
//   ],
//   'supplier_summary' => [
//      [
//          'supplier_name' => 'Fornecedor X',
//          'total_nf'      => 123.45,
//          'total_paid'    => 100.00,
//      ],
//      ...
//   ],
// ];

$years           = $data['years']           ?? [];
$pending         = $data['pending']         ?? [
    'count'        => 0,
    'total_paid'   => 0,
    'total_nf'     => 0,
    'diff_nf_paid' => 0,
];
$supplierSummary = $data['supplier_summary'] ?? [];

// Totais gerais somando todos os anos (apenas confirmadas)
$overallPaid  = 0;
$overallNf    = 0;
$chartConfigs = [];

foreach ($years as $year => $yearData) {
    $summary = $yearData['summary'] ?? ['total_paid' => 0, 'total_nf' => 0];

    $overallPaid += (float)($summary['total_paid'] ?? 0);
    $overallNf   += (float)($summary['total_nf'] ?? 0);

    $rows   = $yearData['data'] ?? [];
    $labels = array_column($rows, 'category_name');
    $values = array_map('floatval', array_column($rows, 'total_paid'));

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

<!-- ======================== -->
<!-- 1. VISÃO GERAL (CONFIRMADAS) -->
<!-- ======================== -->
<h2 class="h5 mb-3">Visão geral (despesas confirmadas)</h2>

<div class="row mb-4">
    <!-- NF primeiro - Azul pastel -->
    <div class="col-md-4">
        <div class="card" style="background-color:#dbeafe;border:none;">
            <div class="card-body">
                <h6 class="mb-2">Total NF (Geral)</h6>
                <h3 class="mb-0">
                    R$ <?php echo number_format($overallNf, 2, ',', '.'); ?>
                </h3>
            </div>
        </div>
    </div>

    <!-- Pago - Verde pastel -->
    <div class="col-md-4">
        <div class="card" style="background-color:#dcfce7;border:none;">
            <div class="card-body">
                <h6 class="mb-2">Total Pago (Geral)</h6>
                <h3 class="mb-0">
                    R$ <?php echo number_format($overallPaid, 2, ',', '.'); ?>
                </h3>
            </div>
        </div>
    </div>

    <!-- Diferença - Amarelo pastel -->
    <div class="col-md-4">
        <div class="card" style="background-color:#fef9c3;border:none;">
            <div class="card-body">
                <h6 class="mb-2">Diferença (NF - Pago) Geral</h6>
                <h3 class="mb-0">
                    R$ <?php echo number_format($overallDiff, 2, ',', '.'); ?>
                </h3>
            </div>
        </div>
    </div>
</div>

<!-- ======================== -->
<!-- 2. PENDENTES DE CONFIRMAÇÃO -->
<!-- ======================== -->
<h2 class="h5 mb-3">Pendentes de confirmação</h2>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card" style="background-color:#fee2e2;border:none;">
            <div class="card-body">
                <h6 class="mb-2">Quantidade de lançamentos pendentes</h6>
                <h3 class="mb-0">
                    <?php echo (int)($pending['count'] ?? 0); ?>
                </h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card" style="background-color:#fef9c3;border:none;">
            <div class="card-body">
                <h6 class="mb-2">Total NF (Pendentes)</h6>
                <h3 class="mb-0">
                    R$ <?php echo number_format((float)($pending['total_nf'] ?? 0), 2, ',', '.'); ?>
                </h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card" style="background-color:#e0f2fe;border:none;">
            <div class="card-body">
                <h6 class="mb-2">Total Pago (Pendentes)</h6>
                <h3 class="mb-0">
                    R$ <?php echo number_format((float)($pending['total_paid'] ?? 0), 2, ',', '.'); ?>
                </h3>
            </div>
        </div>
    </div>
</div>

<!-- ======================== -->
<!-- 3. RESUMO POR FORNECEDOR -->
<!-- ======================== -->
<?php if (!empty($supplierSummary)): ?>
    <h2 class="h5 mb-3">Resumo por fornecedor (confirmadas - todos os anos)</h2>

    <div class="card mb-4">
        <div class="card-body">
            <div class="table-responsive mb-0">
                <table class="table table-sm table-striped align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Fornecedor</th>
                            <th class="text-end">Total NF</th>
                            <th class="text-end">Total Pago</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($supplierSummary as $row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['supplier_name']); ?></td>
                            <td class="text-end">
                                R$ <?php echo number_format((float)$row['total_nf'], 2, ',', '.'); ?>
                            </td>
                            <td class="text-end">
                                R$ <?php echo number_format((float)$row['total_paid'], 2, ',', '.'); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- ======================== -->
<!-- 4. DETALHAMENTO POR ANO -->
<!-- ======================== -->
<h2 class="h5 mb-3">Detalhamento por ano (despesas confirmadas)</h2>

<?php if (empty($years)): ?>
    <div class="alert alert-info">Nenhuma despesa confirmada para exibir na dashboard.</div>
<?php endif; ?>

<?php foreach ($years as $year => $yearData): ?>
    <?php
        $summary    = $yearData['summary'] ?? ['total_paid' => 0, 'total_nf' => 0];
        $totalPaid  = (float)($summary['total_paid'] ?? 0);
        $totalNf    = (float)($summary['total_nf'] ?? 0);
        $diffNfPaid = $totalNf - $totalPaid;

        $rows    = $yearData['data'] ?? [];
        $chartId = 'categoryChart_' . $year;

        $labels  = array_column($rows, 'category_name');
        $values  = array_map('floatval', array_column($rows, 'total_paid'));
    ?>

    <div class="border-top pt-4 mt-4">
        <h3 class="h6 mb-3">Ano <?php echo htmlspecialchars($year); ?></h3>

        <!-- Cards por ano -->
        <div class="row mb-4">
            <!-- NF - Azul pastel -->
            <div class="col-md-4">
                <div class="card" style="background-color:#dbeafe;border:none;">
                    <div class="card-body">
                        <h6 class="mb-2">Total NF (<?php echo htmlspecialchars($year); ?>)</h6>
                        <h3 class="mb-0">
                            R$ <?php echo number_format($totalNf, 2, ',', '.'); ?>
                        </h3>
                    </div>
                </div>
            </div>

            <!-- Pago - Verde pastel -->
            <div class="col-md-4">
                <div class="card" style="background-color:#dcfce7;border:none;">
                    <div class="card-body">
                        <h6 class="mb-2">Total Pago (<?php echo htmlspecialchars($year); ?>)</h6>
                        <h3 class="mb-0">
                            R$ <?php echo number_format($totalPaid, 2, ',', '.'); ?>
                        </h3>
                    </div>
                </div>
            </div>

            <!-- Diferença - Amarelo pastel -->
            <div class="col-md-4">
                <div class="card" style="background-color:#fef9c3;border:none;">
                    <div class="card-body">
                        <h6 class="mb-2">Diferença (NF - Pago) (<?php echo htmlspecialchars($year); ?>)</h6>
                        <h3 class="mb-0">
                            R$ <?php echo number_format($diffNfPaid, 2, ',', '.'); ?>
                        </h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráfico + tabela -->
        <div class="row mb-4">
            <div class="col-md-6">
                <canvas id="<?php echo $chartId; ?>"></canvas>
            </div>

            <div class="col-md-6">
                <div class="table-responsive">
                    <table class="table table-sm table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Categoria</th>
                                <th class="text-end">Pago</th>
                                <th class="text-end">% do total (<?php echo htmlspecialchars($year); ?>)</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($rows)): ?>
                            <?php foreach ($rows as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                                    <td class="text-end">
                                        R$ <?php echo number_format((float)$row['total_paid'], 2, ',', '.'); ?>
                                    </td>
                                    <td class="text-end">
                                        <?php echo number_format((float)$row['percentage_paid'], 2, ',', '.'); ?>%
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="text-center text-muted">Sem dados para este ano.</td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<script>
(function() {
    const chartsData = <?php echo json_encode($chartConfigs); ?>;

    chartsData.forEach(function(cfg) {
        const canvas = document.getElementById(cfg.id);
        if (!canvas) return;

        const ctx = canvas.getContext('2d');

        if (cfg.labels && cfg.labels.length) {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: cfg.labels,
                    datasets: [{
                        label: 'Total Pago (' + cfg.year + ')',
                        data: cfg.values,
                        backgroundColor: '#93c5fd' // azul pastel suave
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        }
    });
})();
</script>
