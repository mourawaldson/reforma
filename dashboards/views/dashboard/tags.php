<h1 class="h3 mb-3">Dashboard de Tags</h1>

<?php if (!empty($data['error'])): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($data['error']); ?></div>
<?php else: ?>
    <pre><?php echo htmlspecialchars(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>
<?php endif; ?>
