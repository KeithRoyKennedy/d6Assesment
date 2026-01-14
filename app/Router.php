<?php

/**
 * Router Class
 *
 * Handles HTTP routing for the application.
 * Supports RESTful routes with dynamic parameters and custom 404 handlers.
 *
 * @package Keith\D6assesment
 */

namespace Keith\D6assesment;

/**
 * Router class for handling HTTP requests
 */
class Router
{
    /**
     * Array of registered routes
     *
     * @var array
     */
    private $routes = [];

    /**
     * Callback function for 404 not found responses
     *
     * @var callable|null
     */
    private $notFoundCallback;

    /**
     * Register a GET route
     *
     * @param string $path Route path (supports {param} placeholders)
     * @param callable|array $callback Controller method or closure
     * @return void
     */
    public function get(string $path, $callback): void
    {
        $this->addRoute('GET', $path, $callback);
    }

    /**
     * Register a POST route
     *
     * @param string $path Route path (supports {param} placeholders)
     * @param callable|array $callback Controller method or closure
     * @return void
     */
    public function post(string $path, $callback): void
    {
        $this->addRoute('POST', $path, $callback);
    }

    /**
     * Register a PUT route
     *
     * @param string $path Route path (supports {param} placeholders)
     * @param callable|array $callback Controller method or closure
     * @return void
     */
    public function put(string $path, $callback): void
    {
        $this->addRoute('PUT', $path, $callback);
    }

    /**
     * Register a DELETE route
     *
     * @param string $path Route path (supports {param} placeholders)
     * @param callable|array $callback Controller method or closure
     * @return void
     */
    public function delete(string $path, $callback): void
    {
        $this->addRoute('DELETE', $path, $callback);
    }

    /**
     * Add a route to the routes array
     *
     * @param string $method HTTP method (GET, POST, PUT, DELETE)
     * @param string $path Route path
     * @param callable|array $callback Controller method or closure
     * @return void
     */
    private function addRoute(string $method, string $path, $callback): void
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'callback' => $callback
        ];
    }

    /**
     * Set custom 404 not found handler
     *
     * @param callable $callback Function to call when route is not found
     * @return void
     */
    public function setNotFound(callable $callback): void
    {
        $this->notFoundCallback = $callback;
    }

    /**
     * Process the current request and execute matching route
     *
     * Matches the current HTTP method and URI against registered routes.
     * Extracts route parameters and calls the appropriate controller method.
     *
     * @return mixed Result of the route callback
     */
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

    /**
     * Convert route path to regex pattern
     *
     * Converts route parameters in {param} format to regex capture groups
     *
     * @param string $path Route path with {param} placeholders
     * @return string Regex pattern for matching
     */
    private function convertToRegex($path)
    {
        $path = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([a-zA-Z0-9_-]+)', $path);
        return '#^' . $path . '$#';
    }
}
