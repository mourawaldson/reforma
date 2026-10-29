<?php
$suppliers = $data[0] ?? [];
$suppliersNotUsed = $data[1] ?? [];

$summary = $suppliers['summary'] ?? ['total_paid' => 0];
$years   = $suppliers['years']   ?? [];

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
        <div class="card dashboard-card dashboard-card--paid">
            <div class="card-body">
                <h6>Total pago (confirmadas)</h6>
                <h3><?= \Dashboard\Support\formatCurrencyBR($summary['total_paid']) ?></h3>
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
        <canvas id="suppliersChart"
                data-chart-labels="<?= \Dashboard\Support\jsonForHtmlAttribute($chartLabels) ?>"
                data-chart-values="<?= \Dashboard\Support\jsonForHtmlAttribute($chartValues) ?>"
                data-chart-label="Total pago"
                data-chart-color="#86efac"></canvas>
    </div>
</div>

<!-- ========================= -->
<!-- ANO → FORNECEDORES -->
<!-- ========================= -->
<?php foreach ($years as $year => $yearData): ?>
    <div class="border-top pt-4 mt-4">
        <h3 class="h6 mb-3">Ano <?= \Dashboard\Support\escapeHtml($year) ?></h3>

        <table class="table table-sm dashboard-table">
            <thead>
            <tr>
                <th>Fornecedor</th>
                <th class="text-end">Qtd</th>
                <th class="text-end">Total pago</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($yearData['suppliers'] as $supplier): ?>
                <tr>
                    <td><?= \Dashboard\Support\escapeHtml($supplier['supplier']) ?></td>
                    <td class="text-end"><?= $supplier['count'] ?></td>
                    <td class="text-end">
                        <?= \Dashboard\Support\formatCurrencyBR($supplier['total_paid']) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endforeach; ?>

<!-- ========================= -->
<!-- FORNECEDORES SEM USO -->
<!-- ========================= -->
<div class="border-top pt-4 mt-4">
    <h3 class="h6 mb-3">Fornecedores sem despesas</h3>

    <table class="table table-sm dashboard-table">
        <?php if (!empty($suppliersNotUsed)): ?>
            <thead>
                <tr>
                    <th>CNPJ</th>
                    <th>Fornecedor</th>
                </tr>
            </thead>
        <?php endif; ?>

        <tbody>
            <?php if (!empty($suppliersNotUsed)): ?>
                <?php foreach ($suppliersNotUsed as $supplier): ?>
                    <tr>
                        <td><?= \Dashboard\Support\escapeHtml(\Dashboard\Support\formatCnpjCpf($supplier['cpf_cnpj'] ?? null)) ?></td>
                        <td><?= \Dashboard\Support\escapeHtml($supplier['name'] ?? '-') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="2" class="text-center text-muted">
                        Nenhum fornecedor encontrado.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
