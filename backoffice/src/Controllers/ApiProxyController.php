<?php
declare(strict_types=1);

require_once __DIR__ . '/../Auth.php';

class ApiProxyController
{
    public function proxy(string $path): void
    {
        if (!SessionAuth::isAuthenticated()) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Autenticação necessária']);
            return;
        }

        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        if (!in_array($method, ['GET', 'POST', 'PUT', 'DELETE'], true)) {
            http_response_code(405);
            return;
        }

        if ($method !== 'GET' && !SessionAuth::verifyCsrf($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) {
            http_response_code(403);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Token de segurança inválido']);
            return;
        }

        if (!preg_match('~^[A-Za-z0-9_/-]+$~D', $path)) {
            http_response_code(400);
            return;
        }

        $config = require __DIR__ . '/../../config/config.php';
        $apiBaseUrl = rtrim($config['api_internal_url'], '/');
        $queryString = $_SERVER['QUERY_STRING'] ?? '';
        $url = $apiBaseUrl . '/' . ltrim($path, '/') . ($queryString !== '' ? '?' . $queryString : '');
        $authUsername = getenv('APP_AUTH_USERNAME');
        $authPassword = getenv('APP_AUTH_PASSWORD');
        if (!is_string($authUsername) || !is_string($authPassword)) {
            http_response_code(503);
            return;
        }

        $headers = [
            'Authorization: Basic ' . base64_encode($authUsername . ':' . $authPassword),
            'Accept: application/json',
        ];
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if ($contentType !== '') {
            $headers[] = 'Content-Type: ' . $contentType;
        }

        $options = [
            'method' => $method,
            'header' => implode("\r\n", $headers),
            'ignore_errors' => true,
            'timeout' => 10,
        ];
        if ($method !== 'GET') {
            $body = file_get_contents('php://input');
            $options['content'] = $body === false ? '' : $body;
        }

        $responseBody = @file_get_contents($url, false, stream_context_create(['http' => $options]));
        $responseHeaders = $http_response_header ?? [];
        if ($responseBody === false && $responseHeaders === []) {
            http_response_code(502);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Não foi possível acessar a API']);
            return;
        }

        foreach ($responseHeaders as $responseHeader) {
            if (preg_match('/^HTTP\/\S+\s+(\d{3})/', $responseHeader, $matches)) {
                http_response_code((int) $matches[1]);
            } elseif (stripos($responseHeader, 'Content-Type:') === 0) {
                header($responseHeader);
            }
        }

        header('Cache-Control: no-store, private');
        echo $responseBody === false ? '' : $responseBody;
    }
}
