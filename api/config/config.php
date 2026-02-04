<?php
return [
    'db' => [
        'host' => $_ENV['DB_HOST'] ?? 'db',
        'name' => $_ENV['DB_NAME'] ?? 'controle_reforma',
        'user' => $_ENV['DB_USER'] ?? 'reforma',
        'pass' => $_ENV['DB_PASS'] ?? 'reforma',
        'charset' => $_ENV['DB_CHARSET'] ?? 'utf8mb4',
    ],
    'base_path' => '',
];
