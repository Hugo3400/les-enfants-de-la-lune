<?php
declare(strict_types=1);

namespace App\Core;

final class Router
{
    private array $routes = [];

    public function get(string $path, string $action): void { $this->add('GET', $path, $action); }
    public function post(string $path, string $action): void { $this->add('POST', $path, $action); }

    private function add(string $method, string $path, string $action): void
    {
        $this->routes[] = [$method, $this->normalize($path), $action];
    }

    public function dispatch(string $method, string $uri): void
    {
        $method = strtoupper($method);
        $uri = $this->normalize($uri);

        foreach ($this->routes as [$m, $route, $action]) {
            $params = [];
            if ($m === $method && $this->match($route, $uri, $params)) {
                [$class, $call] = explode('@', $action, 2);
                $controller = new $class();
                $controller->$call(...array_values($params));
                return;
            }
        }

        ErrorHandler::notFound();
    }

    private function normalize(string $path): string
    {
        $path = '/' . trim($path, '/');
        return $path === '//' ? '/' : $path;
    }

    private function match(string $route, string $uri, array &$params): bool
    {
        $pattern = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $route);
        $pattern = '#^' . $pattern . '$#';

        if (preg_match($pattern, $uri, $matches)) {
            foreach ($matches as $k => $v) {
                if (!is_int($k)) {
                    $params[$k] = ctype_digit((string) $v) ? (int) $v : $v;
                }
            }
            return true;
        }
        return false;
    }
}
