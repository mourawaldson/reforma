<?php
$tags = $data[0] ?? [];
$tagsNotUsed = $data[1] ?? [];

$summary = $tags['summary'] ?? ['total_paid' => 0];
$years   = $tags['years']   ?? [];

// =========================
// DADOS PARA O GRÁFICO
// =========================
$chartTotals = [];

foreach ($years as $yearData) {
    foreach ($yearData['tags'] as $tag) {
        $name = $tag['tag'];
        if (!isset($chartTotals[$name])) {
            $chartTotals[$name] = 0;
        }
        $chartTotals[$name] += $tag['total_paid'];
    }
}

$chartLabels = array_keys($chartTotals);
$chartValues = array_values($chartTotals);
?>

<h1 class="h3 mb-4">Dashboard de Tags</h1>

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
<!-- GRÁFICO POR TAG -->
<!-- ========================= -->
<h2 class="h5 mb-3">Total pago por tag</h2>

<div class="card mb-5">
    <div class="card-body">
        <canvas id="tagsChart"></canvas>
    </div>
</div>

<!-- ========================= -->
<!-- ANO → TAGS -->
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
            <?php foreach ($yearData['tags'] as $tag): ?>
                <tr>
                    <td><?= htmlspecialchars($tag['tag']) ?></td>
                    <td class="text-end"><?= $tag['count'] ?></td>
                    <td class="text-end">
                        R$ <?= number_format($tag['total_paid'], 2, ',', '.') ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endforeach; ?>

<!-- ========================= -->
<!-- TAGS SEM USO -->
<!-- ========================= -->
<div class="border-top pt-4 mt-4">
    <h3 class="h6 mb-3">Tags sem uso</h3>

    <table class="table table-sm table-striped">
        <?php if (!empty($tagsNotUsed)): ?>
            <thead>
                <tr>
                    <th>Tag</th>
                </tr>
            </thead>
        <?php endif; ?>

        <tbody>
            <?php if (!empty($tagsNotUsed)): ?>
                <?php foreach ($tagsNotUsed as $tag): ?>
                    <tr>
                        <td><?= htmlspecialchars($tag['name']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="2" class="text-center text-muted">
                        Nenhuma tag encontrada.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
    (function () {
        const ctx = document.getElementById('tagsChart');
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
