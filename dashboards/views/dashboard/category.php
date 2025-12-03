<h1 class="h3 mb-3">Dashboard por Categoria</h1>

<form class="row g-2 mb-4" method="get" action="/dashboard/category">
    <div class="col-auto">
        <label for="year" class="col-form-label">Ano:</label>
    </div>
    <div class="col-auto">
        <input type="number" class="form-control" id="year" name="year"
               value="<?php echo htmlspecialchars($data['year']); ?>">
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-outline-secondary">Atualizar</button>
    </div>
</form>

<?php
    $totalPaid = $data['summary']['total_paid'] ?? 0;
    $totalNf   = $data['summary']['total_nf'] ?? 0;
    // Diferença entre NF e pago (NF - Pago)
    $diffNfPaid = $totalNf - $totalPaid;
?>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h6>Total NF</h6>
                <h3>R$ <?php echo number_format($totalNf, 2, ',', '.'); ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h6>Total Pago</h6>
                <h3>R$ <?php echo number_format($totalPaid, 2, ',', '.'); ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h6>Diferença (NF - Pago)</h6>
                <h3>R$ <?php echo number_format($diffNfPaid, 2, ',', '.'); ?></h3>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-6">
        <canvas id="categoryChart"></canvas>
    </div>
    <div class="col-md-6">
        <div class="table-responsive">
            <table class="table table-sm table-striped align-middle">
                <thead>
                    <tr>
                        <th>Categoria</th>
                        <th class="text-end">Pago</th>
                        <th class="text-end">% do total</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($data['data'] as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                        <td class="text-end">
                            R$ <?php echo number_format($row['total_paid'], 2, ',', '.'); ?>
                        </td>
                        <td class="text-end">
                            <?php echo number_format($row['percentage_paid'], 2, ',', '.'); ?>%
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
(function() {
    const ctx = document.getElementById('categoryChart').getContext('2d');
    const labels = <?php echo json_encode(array_column($data['data'] ?? [], 'category_name')); ?>;
    const values = <?php echo json_encode(array_map('floatval', array_column($data['data'] ?? [], 'total_paid'))); ?>;

    if (labels.length) {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Total Pago',
                    data: values
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
})();
</script>
