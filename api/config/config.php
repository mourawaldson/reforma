<?php
return [
    'cors_allowed_origins' => array_values(array_filter(array_map(
        'trim',
        explode(',', $_ENV['CORS_ALLOWED_ORIGINS'] ?? 'http://localhost:8001,http://127.0.0.1:8001')
    ))),
    'db' => [
        'host' => $_ENV['DB_HOST'],
        'name' => $_ENV['DB_NAME'],
        'user' => $_ENV['DB_USER'],
        'pass' => $_ENV['DB_PASS'],
        'charset' => $_ENV['DB_CHARSET'] ?? 'utf8mb4',
    ],
    'base_path' => '',
];
