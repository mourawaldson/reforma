<?php require_once __DIR__ . '/../../src/Helpers.php'; ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title><?php echo \Backoffice\Support\escapeHtml($pageTitle ?? 'Backoffice'); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= \Backoffice\Support\escapeHtml(SessionAuth::csrfToken()) ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link href="/assets/css/backoffice.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="/assets/js/api-client.js?v=<?php echo filemtime(__DIR__ . '/../../public/assets/js/api-client.js'); ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <?php if (!empty($loadDataTables)): ?>
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.11/css/dataTables.bootstrap5.min.css">
        <script src="https://cdn.datatables.net/1.13.11/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.11/js/dataTables.bootstrap5.min.js"></script>
    <?php endif; ?>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
  <div class="container-fluid">
    <a class="navbar-brand" href="/expenses">Backoffice</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="/expenses">Despesas</a></li>
        <li class="nav-item"><a class="nav-link" href="/suppliers">Fornecedores</a></li>
        <li class="nav-item"><a class="nav-link" href="/tags">Tags</a></li>
      </ul>
      <form method="post" action="/logout" class="d-flex">
        <input type="hidden" name="csrf_token" value="<?= \Backoffice\Support\escapeHtml(SessionAuth::csrfToken()) ?>">
        <button class="btn btn-outline-light btn-sm" type="submit">Sair</button>
      </form>
    </div>
  </div>
</nav>
<div class="container mb-5">
