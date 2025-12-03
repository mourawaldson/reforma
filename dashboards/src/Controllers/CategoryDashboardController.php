<?php
use GuzzleHttp\Client;

class CategoryDashboardController
{
    private Client $client;
    private string $apiBaseUrl;

    public function __construct()
    {
        $config = require __DIR__ . '/../../config/config.php';
        $this->apiBaseUrl = rtrim($config['api_base_url'], '/') . '/';
        $this->client = new Client([
            'base_uri' => $this->apiBaseUrl,
            'timeout'  => 5.0,
        ]);
    }

    public function index()
    {
        $year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
        $query = ['year' => $year];

        $response = $this->client->get('dashboard/category', ['query' => $query]);
        $body = $response->getBody()->getContents();
        $data = json_decode($body, true);

        $pageTitle = 'Dashboard por Categoria';
        include __DIR__ . '/../../views/layout/header.php';
        include __DIR__ . '/../../views/dashboard/category.php';
        include __DIR__ . '/../../views/layout/footer.php';
    }
}
