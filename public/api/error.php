<?php
/**
 * API Error Handler
 * 
 * This file handles API errors, particularly 404 Not Found errors,
 * and returns appropriate JSON responses.
 */

// Suppress all warnings and notices for error page
error_reporting(E_ERROR | E_PARSE);

// Set JSON content type
header('Content-Type: application/json');

// Set appropriate status code
$status_code = isset($_SERVER['REDIRECT_STATUS']) ? (int)$_SERVER['REDIRECT_STATUS'] : 404;
http_response_code($status_code);

// Try to initialize the application if possible
if (!defined('PRIVATE_PATH')) {
    // Try to find the initialize.php file
    $initialize_path = null;
    $possible_paths = [
        '../../private/core/initialize.php',
        '/var/www/html/private/core/initialize.php',
        $_SERVER['DOCUMENT_ROOT'] . '/private/core/initialize.php'
    ];
    
    foreach ($possible_paths as $path) {
        if (file_exists($path)) {
            $initialize_path = $path;
            break;
        }
    }
    
    // If we found initialize.php, include it
    if ($initialize_path) {
        include_once($initialize_path);
    }
}

// Prepare error response
$error = [
    'success' => false,
    'status' => $status_code,
    'error' => 'Not Found',
    'message' => 'The requested API endpoint could not be found.'
];

// Add request information for debugging
if (defined('DEBUG_MODE') && DEBUG_MODE === true) {
    $error['debug'] = [
        'request_uri' => $_SERVER['REQUEST_URI'] ?? 'Not available',
        'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'Not available',
        'redirect_status' => $_SERVER['REDIRECT_STATUS'] ?? 'Not available'
    ];
}

// Output JSON response
echo json_encode($error, JSON_PRETTY_PRINT);
exit;
?>
