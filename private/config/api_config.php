<?php
/**
 * API Configuration for FlavorConnect
 * 
 * This file contains configuration settings for the FlavorConnect API
 * It follows the same modular approach as the main configuration
 */

// Include main config if not already included
if (!defined('ENVIRONMENT')) {
    require_once dirname(__FILE__) . '/config.php';
}

// API configuration based on environment
$api_config = [
    // Default/common settings
    'default' => [
        'debug' => DEVELOPMENT_MODE,
        'charset' => 'utf8',
        'timeout' => 30, // API request timeout in seconds
        'max_retries' => 3, // Maximum number of API request retries
        'cache_time' => 300, // Cache time for API responses in seconds
        'log_api_calls' => DEVELOPMENT_MODE // Whether to log API calls
    ],
    
    // Docker environment
    'docker' => [
        'db_host' => 'db',
        'db_port' => 3306,
        'db_user' => DB_USER, // Use the same credentials as main DB
        'db_pass' => DB_PASS,
        'db_name' => DB_NAME,
        'api_base_url' => '/api',
        'api_local_url' => '/api',
        'favorites_toggle_endpoint' => '/toggle_favorite.php',
        'favorites_check_endpoint' => '/toggle_favorite.php',
        'favorites_get_all_endpoint' => '/get_favorites.php'
    ],
    
    // XAMPP/local environment
    'xampp' => [
        'db_host' => 'localhost',
        'db_port' => 3306,
        'db_user' => DB_USER, // Use the same credentials as main DB
        'db_pass' => DB_PASS,
        'db_name' => DB_NAME,
        'api_base_url' => '/api',
        'api_local_url' => '/api',
        'favorites_toggle_endpoint' => '/toggle_favorite.php',
        'favorites_check_endpoint' => '/toggle_favorite.php',
        'favorites_get_all_endpoint' => '/get_favorites.php'
    ],
    
    // Bluehost/production environment
    'bluehost' => [
        'db_host' => 'localhost',
        'db_port' => 3306,
        'db_user' => 'swbhdnmy_user',
        'db_pass' => '@Connect4establish',
        'db_name' => 'swbhdnmy_db_flavorconnect',
        'api_base_url' => 'https://flavorconnect-api-f240ecae55b2.herokuapp.com',
        'api_local_url' => '/api',
        'favorites_toggle_endpoint' => '/api/favorites/toggle',
        'favorites_check_endpoint' => '/api/favorites/{userId}/{recipeId}',
        'favorites_get_all_endpoint' => '/api/favorites/{userId}',
        'log_api_calls' => true // Always log API calls in production for monitoring
    ]
];

// Load environment-specific configuration
$env_api_config = array_merge($api_config['default'], $api_config[ENVIRONMENT]);

// Define API database constants
define('API_DB_HOST', $env_api_config['db_host']);
define('API_DB_PORT', $env_api_config['db_port']);
define('API_DB_USER', $env_api_config['db_user']);
define('API_DB_PASS', $env_api_config['db_pass']);
define('API_DB_NAME', $env_api_config['db_name']);

// API URL configuration
define('API_BASE_URL', $env_api_config['api_base_url']);
define('API_LOCAL_URL', $env_api_config['api_local_url']);

// Determine if we're in production mode
$is_production = ENVIRONMENT === 'bluehost';

// API endpoints
define('API_FAVORITES_TOGGLE', $is_production ? 
    API_BASE_URL . $env_api_config['favorites_toggle_endpoint'] : 
    API_LOCAL_URL . $env_api_config['favorites_toggle_endpoint']);
    
define('API_FAVORITES_CHECK', $is_production ? 
    API_BASE_URL . $env_api_config['favorites_check_endpoint'] : 
    API_LOCAL_URL . $env_api_config['favorites_check_endpoint']);
    
define('API_FAVORITES_GET_ALL', $is_production ? 
    API_BASE_URL . $env_api_config['favorites_get_all_endpoint'] : 
    API_LOCAL_URL . $env_api_config['favorites_get_all_endpoint']);

// Additional API settings
define('API_DEBUG', $env_api_config['debug']);
define('API_CHARSET', $env_api_config['charset']);
define('API_TIMEOUT', $env_api_config['timeout']);
define('API_MAX_RETRIES', $env_api_config['max_retries']);
define('API_CACHE_TIME', $env_api_config['cache_time']);
define('API_LOG_CALLS', $env_api_config['log_api_calls']);

// Log the API environment for debugging
if (API_DEBUG) {
    error_log('FlavorConnect API running in ' . ENVIRONMENT . ' environment');
}

/**
 * Get database connection for API
 * 
 * @return mysqli|false Database connection or false on failure
 */
function get_api_db_connection() {
    static $connection = null;
    
    // Return existing connection if already established
    if ($connection !== null) {
        return $connection;
    }
    
    try {
        $connection = mysqli_connect(
            API_DB_HOST, 
            API_DB_USER, 
            API_DB_PASS, 
            API_DB_NAME, 
            API_DB_PORT
        );
        
        if (!$connection) {
            throw new Exception(mysqli_connect_error(), mysqli_connect_errno());
        }
        
        // Set charset
        mysqli_set_charset($connection, API_CHARSET);
        
        return $connection;
    } catch (Exception $e) {
        if (API_DEBUG) {
            error_log('API Database connection error: ' . $e->getMessage() . ' (Code: ' . $e->getCode() . ')');
        }
        $connection = null;
        return false;
    }
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
    
    // Log API response if enabled
    if (API_LOG_CALLS) {
        error_log('API Response: ' . json_encode($data) . ' (Status: ' . $status . ')');
    }
    
    echo json_encode($data);
    exit;
}

/**
 * Make an API request
 * 
 * @param string $endpoint API endpoint
 * @param array $data Request data
 * @param string $method HTTP method (GET, POST, PUT, DELETE)
 * @return array|false Response data or false on failure
 */
function api_request($endpoint, $data = [], $method = 'GET') {
    $url = (strpos($endpoint, 'http') === 0) ? $endpoint : API_BASE_URL . $endpoint;
    
    $options = [
        'http' => [
            'header' => "Content-Type: application/json\r\n",
            'method' => $method,
            'content' => json_encode($data),
            'timeout' => API_TIMEOUT
        ]
    ];
    
    $context = stream_context_create($options);
    
    // Log API request if enabled
    if (API_LOG_CALLS) {
        error_log('API Request: ' . $url . ' (Method: ' . $method . ', Data: ' . json_encode($data) . ')');
    }
    
    // Try the request with retries
    $attempts = 0;
    $response = false;
    
    while ($attempts < API_MAX_RETRIES && $response === false) {
        $attempts++;
        $response = @file_get_contents($url, false, $context);
        
        if ($response === false && $attempts < API_MAX_RETRIES) {
            // Wait before retrying (exponential backoff)
            usleep(100000 * pow(2, $attempts)); // 200ms, 400ms, 800ms
        }
    }
    
    if ($response === false) {
        if (API_DEBUG) {
            error_log('API Request failed after ' . $attempts . ' attempts: ' . $url);
        }
        return false;
    }
    
    return json_decode($response, true);
}
