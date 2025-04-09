<?php
/**
 * API Router for FlavorConnect
 * 
 * This file handles routing for the API endpoints using the main Router class.
 */

// Initialize the application
require_once('../../private/core/initialize.php');

// Create a new router instance for API
$router = new Router();

// Add API-specific middleware
$router->addMiddleware('api_cors', function($params, $next) {
    // Set CORS headers for API requests
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    
    // Handle preflight requests
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
    
    // Set JSON content type
    header('Content-Type: application/json');
    
    return $next($params);
});

// Load routes from the main routes configuration
$router->loadRoutes(PRIVATE_PATH . '/config/routes.php');

// Try to dispatch the request
$dispatched = $router->dispatch();

// If no route was matched, return a 404 JSON response
if (!$dispatched) {
    header('HTTP/1.1 404 Not Found');
    echo json_encode([
        'status' => 'error',
        'message' => 'API endpoint not found',
        'code' => 404
    ]);
    exit;
}
?>
