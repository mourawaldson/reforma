<?php
$tags = $data[0] ?? [];
$tagsNotUsed = $data[1] ?? [];

$summary = $tags['summary'] ?? ['total_paid' => 0];
$years   = $tags['years']   ?? [];

/*
=====================================
CONSOLIDADO + DADOS DO GRÁFICO
=====================================
*/
$chartTotals = [];
$consolidated = [];

foreach ($years as $yearData) {
    foreach ($yearData['tags'] as $tag) {

        $name = $tag['tag'];

        if (!isset($chartTotals[$name])) {
            $chartTotals[$name] = 0;
        }

        if (!isset($consolidated[$name])) {
            $consolidated[$name] = [
                'count' => 0,
                'total_paid' => 0
            ];
        }

        $chartTotals[$name] += $tag['total_paid'];

        $consolidated[$name]['count'] += $tag['count'];
        $consolidated[$name]['total_paid'] += $tag['total_paid'];
    }
}

/*
=====================================
ORDENA POR MAIOR GASTO
=====================================
*/
uasort($chartTotals, fn($a, $b) => $b <=> $a);

uasort($consolidated, fn($a, $b) =>
    $b['total_paid'] <=> $a['total_paid']
);

$chartLabels = array_keys($chartTotals);
$chartValues = array_values($chartTotals);
?>

<h1 class="h3 mb-4">Dashboard de Tags</h1>

<!-- ========================= -->
<!-- CARD GLOBAL -->
<!-- ========================= -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card dashboard-card dashboard-card--paid">
            <div class="card-body">
                <h6>Total pago (confirmadas)</h6>
                <h3><?= \Dashboard\Support\formatCurrencyBR($summary['total_paid']) ?></h3>
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
        <canvas id="tagsChart"
                data-chart-labels="<?= \Dashboard\Support\jsonForHtmlAttribute($chartLabels) ?>"
                data-chart-values="<?= \Dashboard\Support\jsonForHtmlAttribute($chartValues) ?>"
                data-chart-label="Total pago"
                data-chart-color="#86efac"></canvas>
    </div>
</div>

<!-- ========================= -->
<!-- TABELA CONSOLIDADA -->
<!-- ========================= -->
<h2 class="h5 mb-3">Consolidado geral (todos os anos)</h2>

<table class="table table-sm dashboard-table mb-5">
    <thead>
    <tr>
        <th>Tag</th>
        <th class="text-end">Qtd</th>
        <th class="text-end">Total pago</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($consolidated as $tagName => $values): ?>
        <tr>
            <td><?= \Dashboard\Support\escapeHtml($tagName) ?></td>
            <td class="text-end"><?= $values['count'] ?></td>
            <td class="text-end">
                <?= \Dashboard\Support\formatCurrencyBR($values['total_paid']) ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<!-- ========================= -->
<!-- ANO → TAGS -->
<!-- ========================= -->
<?php foreach ($years as $year => $yearData): ?>
    <div class="border-top pt-4 mt-4">
        <h3 class="h6 mb-3">Ano <?= \Dashboard\Support\escapeHtml($year) ?></h3>

        <table class="table table-sm dashboard-table">
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
                    <td><?= \Dashboard\Support\escapeHtml($tag['tag']) ?></td>
                    <td class="text-end"><?= $tag['count'] ?></td>
                    <td class="text-end">
                        <?= \Dashboard\Support\formatCurrencyBR($tag['total_paid']) ?>
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

    <table class="table table-sm dashboard-table">
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
                        <td><?= \Dashboard\Support\escapeHtml($tag['name']) ?></td>
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
