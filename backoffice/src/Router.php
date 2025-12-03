<?php
class Router
{
    private array $routes = [];

    public function add(string $method, string $pattern, callable $handler): void
    {
        $this->routes[] = [$method, $pattern, $handler];
    }

    public function dispatch(string $method, string $uriPath)
    {
        foreach ($this->routes as [$routeMethod, $pattern, $handler]) {
            if (strtoupper($method) !== strtoupper($routeMethod)) {
                continue;
            }
            $regex = '@^' . $pattern . '$@';
            if (preg_match($regex, $uriPath, $matches)) {
                array_shift($matches);
                return call_user_func_array($handler, $matches);
            }
        }
        http_response_code(404);
        echo 'Página não encontrada';
    }
}
