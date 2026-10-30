<?php
use GuzzleHttp\Client;
require_once __DIR__ . '/../Helpers.php';

class DashboardPageController
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
            'auth'     => [getenv('APP_AUTH_USERNAME'), getenv('APP_AUTH_PASSWORD')],
        ]);
    }

    private function render(string $view, array $data = [])
    {
        $pageTitle = 'Dashboard';
        include __DIR__ . '/../../views/layout/header.php';
        include __DIR__ . '/../../views/dashboard/menu.php';
        include __DIR__ . '/../../views/dashboard/' . $view . '.php';
        include __DIR__ . '/../../views/layout/footer.php';
    }

    private function fetchApi(string $endpoint): array
    {
        try {
            $response = $this->client->get($endpoint);
            return json_decode($response->getBody()->getContents(), true) ?? [];
        } catch (\Throwable $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function index()
    {
        $data = $this->fetchApi('dashboard/overview');
        $this->render('index', $data);
    }

    public function expenses()
    {
        $data = $this->fetchApi('dashboard/expenses');

        $this->render('expenses', $data);
    }

    public function tags()
    {
        $tags = $this->fetchApi('dashboard/tags');
        $this->render('tags', [$tags, $tags['unused'] ?? []]);
    }

    public function suppliers()
    {
        $suppliers = $this->fetchApi('dashboard/suppliers');
        $this->render('suppliers', [$suppliers, $suppliers['unused'] ?? []]);
    }
}
