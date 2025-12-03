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
        // Filtros opcionais da URL (se quiser reaproveitar)
        $query = [];
        if (!empty($_GET['year'])) {
            $query['year'] = (int)$_GET['year'];
        }
        if (!empty($_GET['supplier_id'])) {
            $query['supplier_id'] = (int)$_GET['supplier_id'];
        }
        if (!empty($_GET['tag_id'])) {
            $query['tag_id'] = (int)$_GET['tag_id'];
        }

        try {
            $options = [];
            if (!empty($query)) {
                $options['query'] = $query;
            }

            $response = $this->client->get('dashboard/category', $options);
            $body     = $response->getBody()->getContents();
            $apiData  = json_decode($body, true);

            $data = [
                'years' => $apiData['years'] ?? [],
            ];
        } catch (\Throwable $e) {
            // Em caso de erro na API, evita quebrar a página
            $data = [
                'years' => [],
                'error' => $e->getMessage(),
            ];
        }

        $pageTitle = 'Dashboard por Categoria';

        include __DIR__ . '/../../views/layout/header.php';
        include __DIR__ . '/../../views/dashboard/category.php';
        include __DIR__ . '/../../views/layout/footer.php';
    }
}
