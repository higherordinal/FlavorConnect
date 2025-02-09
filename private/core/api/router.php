<?php
/**
 * API Router
 * Handles routing of API requests to appropriate endpoint handlers
 */

class ApiRouter {
    private $endpoints = [];
    private $request;

    public function __construct() {
        $this->request = $this->parseRequest();
    }

    /**
     * Register an endpoint handler
     * @param string $path Endpoint path (e.g., 'recipes')
     * @param callable $handler Handler function
     * @param array $methods Allowed HTTP methods
     */
    public function register($path, $handler, $methods = ['GET']) {
        $this->endpoints[$path] = [
            'handler' => $handler,
            'methods' => $methods
        ];
    }

    /**
     * Parse the incoming request
     * @return array Request data
     */
    private function parseRequest() {
        $json = file_get_contents('php://input');
        $body = !empty($json) ? json_decode($json, true) : [];
        
        if (!empty($json) && json_last_error() !== JSON_ERROR_NONE) {
            json_error('Invalid JSON data');
        }

        return [
            'method' => $_SERVER['REQUEST_METHOD'],
            'path' => trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/'),
            'query' => $_GET,
            'body' => $body
        ];
    }

    /**
     * Handle the incoming request
     */
    public function handleRequest() {
        try {
            // Get endpoint path
            $parts = explode('/', $this->request['path']);
            $endpoint = $parts[1] ?? '';

            // Check if endpoint exists
            if (!isset($this->endpoints[$endpoint])) {
                json_error('Endpoint not found', 404);
            }

            // Check if method is allowed
            $handler = $this->endpoints[$endpoint];
            if (!in_array($this->request['method'], $handler['methods'])) {
                json_error('Method not allowed', 405);
            }

            // Call handler
            $result = call_user_func($handler['handler'], $this->request);
            json_success($result);

        } catch (Exception $e) {
            json_error($e->getMessage(), $e->getCode() ?: 500);
        }
    }
}
