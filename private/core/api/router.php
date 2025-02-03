<?php
/**
 * API Router
 * Handles routing of API requests to appropriate endpoint handlers
 */

class ApiRouter {
    private $endpoints = [];
    private $request;
    private $response;

    public function __construct() {
        $this->request = $this->parseRequest();
        $this->response = [
            'success' => false,
            'data' => null,
            'error' => null
        ];
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
        return [
            'method' => $_SERVER['REQUEST_METHOD'],
            'path' => trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/'),
            'query' => $_GET,
            'body' => $this->getRequestBody()
        ];
    }

    /**
     * Get request body data
     * @return array Request body data
     */
    private function getRequestBody() {
        $body = file_get_contents('php://input');
        return json_decode($body, true) ?? [];
    }

    /**
     * Handle the API request
     * @return array Response data
     */
    public function handleRequest() {
        try {
            // Extract API path (everything after /api/)
            $pathParts = explode('api/', $this->request['path']);
            $apiPath = end($pathParts);

            // Find matching endpoint
            foreach ($this->endpoints as $path => $endpoint) {
                if (strpos($apiPath, $path) === 0) {
                    // Check HTTP method
                    if (!in_array($this->request['method'], $endpoint['methods'])) {
                        throw new Exception('Method not allowed', 405);
                    }

                    // Call handler
                    $result = call_user_func($endpoint['handler'], $this->request);
                    $this->response['success'] = true;
                    $this->response['data'] = $result;
                    return $this->response;
                }
            }

            throw new Exception('Endpoint not found', 404);
        } catch (Exception $e) {
            $this->response['error'] = [
                'message' => $e->getMessage(),
                'code' => $e->getCode() ?: 500
            ];
            return $this->response;
        }
    }

    /**
     * Send the API response
     * @param array $response Response data
     */
    public function sendResponse($response) {
        // Set response code
        if (isset($response['error'])) {
            http_response_code($response['error']['code']);
        } else {
            http_response_code(200);
        }

        // Set headers
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');

        // Send response
        echo json_encode($response);
    }
}
