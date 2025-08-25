<?php
namespace App\Core;

final class Router
{
    /** @var array<string, array{pattern:string, handler:callable}> */
    private array $routes = [];

    public function add(string $method, string $pattern, callable $handler): void
    {
        $key = strtoupper($method) . ' ' . $this->normalize($pattern);
        $this->routes[$key] = [
            'pattern' => $this->compile($this->normalize($pattern)),
            'handler' => $handler,
        ];
    }

    public function dispatch(string $method, string $path, Request $request): bool
    {
        $method = strtoupper($method);
        $path = $this->normalize($path);

        foreach ($this->routes as $key => $route) {
            if (!str_starts_with($key, $method . ' ')) continue;

            $regex = $route['pattern'];
            if (preg_match($regex, $path, $matches)) {
                $params = [];
                foreach ($matches as $k => $v) {
                    if (!is_int($k)) $params[$k] = $v;
                }
                $handler = $route['handler'];
                $result = $handler($request, $params);

                // If handler returned data, auto-JSON it
                if ($result !== null) {
                    Response::json($result, 200);
                }
                return true;
            }
        }
        return false;
    }

    private function normalize(string $path): string
    {
        if ($path === '') return '/';
        $path = '/' . ltrim($path, '/');
        return $path === '/' ? '/' : rtrim($path, '/');
    }

    private function compile(string $pattern): string
    {
        // Convert /api/items/{id} -> #^/api/items/(?P<id>[^/]+)$#i
        $regex = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $pattern);
        return '#^' . $regex . '$#i';
    }
}
