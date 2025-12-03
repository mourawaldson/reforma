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
        $query = [];
        if (!empty($_GET['supplier_id'])) {
            $query['supplier_id'] = (int)$_GET['supplier_id'];
        }
        if (!empty($_GET['tag_id'])) {
            $query['tag_id'] = (int)$_GET['tag_id'];
        }

        try {
            $options = [];
            if ($query) {
                $options['query'] = $query;
            }

            $response = $this->client->get('dashboard/category', $options);
            $body     = $response->getBody()->getContents();
            $apiData  = json_decode($body, true);

            $data = $apiData;
        } catch (\Throwable $e) {
            $data = [
                'year'    => date('Y'),
                'summary' => ['total_paid' => 0, 'total_nf' => 0],
                'data'    => [],
                'pending' => ['count' => 0, 'total_paid' => 0, 'total_nf' => 0, 'diff_nf_paid' => 0],
                'error'   => $e->getMessage(),
            ];
        }

        $pageTitle = 'Dashboard por Categoria';

        include __DIR__ . '/../../views/layout/header.php';
        include __DIR__ . '/../../views/dashboard/category.php';
        include __DIR__ . '/../../views/layout/footer.php';
    }
}
