<?php
/**
 * API Entry Point
 * 
 * This file serves as the main entry point for the FlavorConnect API.
 * It uses the Router class to handle API requests and return JSON responses.
 */

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
$method = $_SERVER['REQUEST_METHOD'];
$path = $_SERVER['REQUEST_URI'];

// Remove project folder from URI if in XAMPP environment
if (defined('PROJECT_FOLDER') && !empty(PROJECT_FOLDER)) {
    $path = str_replace(PROJECT_FOLDER, '', $path);
}

// Normalize the URI
$path = '/' . trim($path, '/');

$dispatched = $router->dispatch($method, $path);

// If no route was matched, return a 404 JSON response
if (!$dispatched) {
    header('HTTP/1.1 404 Not Found');
    echo json_encode([
        'status' => 'error',
        'message' => 'API endpoint not found',
        'code' => 404,
        'path' => $path,
        'method' => $method,
        'time' => date('Y-m-d H:i:s')
    ]);
}
