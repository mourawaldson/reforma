<?php require_once __DIR__ . '/../../src/Helpers.php'; ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Entrar - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link href="/assets/css/dashboard.css" rel="stylesheet">
</head>
<body>
<main class="container py-5" style="max-width: 26rem">
    <h1 class="h4 mb-4">Entrar no Dashboard</h1>
    <?php if (!empty($errorMessage)): ?>
        <div class="alert alert-danger" role="alert"><?= \Dashboard\Support\escapeHtml($errorMessage) ?></div>
    <?php endif; ?>
    <form method="post" action="/login">
        <input type="hidden" name="csrf_token" value="<?= \Dashboard\Support\escapeHtml(SessionAuth::csrfToken()) ?>">
        <div class="mb-3">
            <label class="form-label" for="username">Usuário</label>
            <input class="form-control" id="username" name="username" autocomplete="username" required autofocus>
        </div>
        <div class="mb-3">
            <label class="form-label" for="password">Senha</label>
            <input class="form-control" id="password" name="password" type="password" autocomplete="current-password" required>
        </div>
        <button class="btn btn-primary w-100" type="submit">Entrar</button>
    </form>
</main>
</body>
</html>
