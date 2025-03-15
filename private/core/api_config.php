<?php
/**
 * API Configuration for FlavorConnect
 * 
 * This file contains configuration settings for the FlavorConnect API
 */

// Database configuration for API
define('API_DB_HOST', 'localhost'); // Bluehost database host
define('API_DB_USER', 'swbhdnmy_user'); // Bluehost database username
define('API_DB_PASS', 'Divided_4union'); // Bluehost database password
define('API_DB_NAME', 'swbhdnmy_db_flavorconnect'); // Bluehost database name

// API URL configuration
define('API_BASE_URL', '/api'); // Local API endpoint

// Error reporting for API
define('API_DEBUG', false); // Set to false in production

/**
 * Get database connection for API
 * 
 * @return mysqli Database connection
 */
function get_api_db_connection() {
    static $connection;
    
    if (!isset($connection)) {
        $connection = mysqli_connect(API_DB_HOST, API_DB_USER, API_DB_PASS, API_DB_NAME);
        
        if (mysqli_connect_errno()) {
            $error = mysqli_connect_error();
            error_log("Database connection failed: " . $error);
            
            if (API_DEBUG) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Database connection failed',
                    'error' => $error
                ]);
                exit;
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Database connection failed'
                ]);
                exit;
            }
        }
        
        mysqli_set_charset($connection, 'utf8');
    }
    
    return $connection;
}

/**
 * Send JSON response
 * 
 * @param array $data Data to send as JSON
 * @param int $status HTTP status code
 */
function send_json_response($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
