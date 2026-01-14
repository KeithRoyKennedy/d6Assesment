<?php

namespace Keith\D6assesment;

class Router
{
    private $routes = [];
    private $notFoundCallback;

    public function get($path, $callback)
    {
        $this->addRoute('GET', $path, $callback);
    }

    public function post($path, $callback)
    {
        $this->addRoute('POST', $path, $callback);
    }

    public function put($path, $callback)
    {
        $this->addRoute('PUT', $path, $callback);
    }

    public function delete($path, $callback)
    {
        $this->addRoute('DELETE', $path, $callback);
    }

    private function addRoute($method, $path, $callback)
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'callback' => $callback
        ];
    }

    public function setNotFound($callback)
    {
        $this->notFoundCallback = $callback;
    }

    public function run()
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestUri = $_SERVER['REQUEST_URI'];
        
        $requestUri = strtok($requestUri, '?');
        $requestUri = rtrim($requestUri, '/');
        
        if (empty($requestUri)) {
            $requestUri = '/';
        }

        foreach ($this->routes as $route) {
            $pattern = $this->convertToRegex($route['path']);
            
            if ($route['method'] === $requestMethod && preg_match($pattern, $requestUri, $matches)) {
                array_shift($matches);
                
                if (is_array($route['callback'])) {
                    $controller = new $route['callback'][0]();
                    $method = $route['callback'][1];
                    return call_user_func_array([$controller, $method], $matches);
                } else {
                    return call_user_func_array($route['callback'], $matches);
                }
            }
        }

        if ($this->notFoundCallback) {
            return call_user_func($this->notFoundCallback);
        }

        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Route not found']);
    }

    private function convertToRegex($path)
    {
        $path = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([a-zA-Z0-9_-]+)', $path);
        return '#^' . $path . '$#';
    }
}
