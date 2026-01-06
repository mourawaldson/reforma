<?php
$suppliers = $data[0] ?? [];
$suppliersNoExpenses = $data[1] ?? [];

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
                <th>Fornecedor</th>
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

<?php
function formatCnpjCpf($value)
{
  $CPF_LENGTH = 11;
  $cnpj_cpf = preg_replace("/\D/", '', $value);
  
  if (strlen($cnpj_cpf) === $CPF_LENGTH) {
    return preg_replace("/(\d{3})(\d{3})(\d{3})(\d{2})/", "\$1.\$2.\$3-\$4", $cnpj_cpf);
  } 
  
  return preg_replace("/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/", "\$1.\$2.\$3/\$4-\$5", $cnpj_cpf);
}
?>

<!-- ========================= -->
<!-- FORNECEDORES SEM DESPESAS -->
<!-- ========================= -->
<div class="border-top pt-4 mt-4">
    <h3 class="h6 mb-3">Fornecedores sem despesas</h3>

    <table class="table table-sm table-striped">
        <?php if (!empty($suppliersNoExpenses)): ?>
            <thead>
                <tr>
                    <th>CNPJ</th>
                    <th>Fornecedor</th>
                </tr>
            </thead>
        <?php endif; ?>

        <tbody>
            <?php if (!empty($suppliersNoExpenses)): ?>
                <?php foreach ($suppliersNoExpenses as $supplier): ?>
                    <tr>
                        <td><?= htmlspecialchars(formatCnpjCpf($supplier['cpf_cnpj'])) ?></td>
                        <td><?= htmlspecialchars($supplier['name']) ?></td>
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
