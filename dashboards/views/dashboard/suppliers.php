<?php
$summary = $data['summary'] ?? ['total_paid' => 0];
$years   = $data['years']   ?? [];

// =========================
// DADOS PARA O GRÁFICO
// =========================
$chartTotals = [];
$total = 10;
foreach ($years as $yearData) {
    $counter = 0;
    foreach ($yearData['suppliers'] as $supplier) {
        if ($counter < $total) {
            $name = $supplier['supplier'];
            if (!isset($chartTotals[$name])) {
                $chartTotals[$name] = 0;
            }
            $chartTotals[$name] += $supplier['total_paid'];
            $counter++;
        } else {
            break;
        }
    }
}

$chartLabels = array_keys($chartTotals);
$chartValues = array_values($chartTotals);
?>

<h1 class="h3 mb-4">Dashboard de Fornecedores</h1>

<!-- ========================= -->
<!-- CARD GLOBAL -->
<!-- ========================= -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card" style="background:#dcfce7;border:none;">
            <div class="card-body">
                <h6>Total pago (confirmadas)</h6>
                <h3>R$ <?= number_format($summary['total_paid'], 2, ',', '.') ?></h3>
            </div>
        </div>
    </div>
</div>

<!-- ========================= -->
<!-- GRÁFICO POR FORNECEDOR -->
<!-- ========================= -->
<h2 class="h5 mb-3">Total pago por fornecedor (Top <?php echo $total; ?>)</h2>

<div class="card mb-5">
    <div class="card-body">
        <canvas id="suppliersChart"></canvas>
    </div>
</div>

<!-- ========================= -->
<!-- ANO → FORNECEDORES -->
<!-- ========================= -->
<?php foreach ($years as $year => $yearData): ?>
    <div class="border-top pt-4 mt-4">
        <h3 class="h6 mb-3">Ano <?= htmlspecialchars($year) ?></h3>

        <table class="table table-sm table-striped">
            <thead>
            <tr>
                <th>Tag</th>
                <th class="text-end">Qtd</th>
                <th class="text-end">Total pago</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($yearData['suppliers'] as $supplier): ?>
                <tr>
                    <td><?= htmlspecialchars($supplier['supplier']) ?></td>
                    <td class="text-end"><?= $supplier['count'] ?></td>
                    <td class="text-end">
                        R$ <?= number_format($supplier['total_paid'], 2, ',', '.') ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endforeach; ?>

<script>
    (function () {
        const ctx = document.getElementById('suppliersChart');
        if (!ctx) return;

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($chartLabels) ?>,
                datasets: [{
                    label: 'Total pago',
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
