<nav class="mb-4">
    <ul class="nav nav-pills">
        <li class="nav-item">
            <a class="nav-link <?php echo ($_SERVER['REQUEST_URI'] === '/dashboard' ? 'active' : ''); ?>"
               href="/dashboard">Visão geral</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo (str_contains($_SERVER['REQUEST_URI'], '/dashboard/expenses') ? 'active' : ''); ?>"
               href="/dashboard/expenses">Despesas</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo (str_contains($_SERVER['REQUEST_URI'], '/dashboard/tags') ? 'active' : ''); ?>"
               href="/dashboard/tags">Tags</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo (str_contains($_SERVER['REQUEST_URI'], '/dashboard/suppliers') ? 'active' : ''); ?>"
               href="/dashboard/suppliers">Fornecedores</a>
        </li>
    </ul>
</nav>
