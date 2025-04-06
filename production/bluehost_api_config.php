<?php
/**
 * Bluehost-specific API Configuration for FlavorConnect
 * 
 * This file contains hardcoded configuration settings for the FlavorConnect API
 * in the Bluehost production environment.
 */

// API configuration for Bluehost production environment
$api_config = [
    // Debug and general settings
    'debug' => false,
    'charset' => 'utf8',
    'timeout' => 30, // API request timeout in seconds
    'max_retries' => 3, // Maximum number of API request retries
    'cache_time' => 300, // Cache time for API responses in seconds
    'log_api_calls' => true, // Always log API calls in production for monitoring
    
    // Database settings - using the same credentials as main DB
    'db_host' => 'localhost',
    'db_port' => 3306,
    'db_user' => 'swbhdnmy_user',
    'db_pass' => '@Connect4establish',
    'db_name' => 'swbhdnmy_db_flavorconnect',
    
    // API endpoints
    'api_base_url' => 'https://flavorconnect-api-f240ecae55b2.herokuapp.com',
    'api_local_url' => '/api',
    'favorites_toggle_endpoint' => '/api/favorites/toggle',
    'favorites_check_endpoint' => '/api/favorites/{userId}/{recipeId}',
    'favorites_get_all_endpoint' => '/api/favorites/{userId}'
];

// Define API database constants
define('API_DB_HOST', $api_config['db_host']);
define('API_DB_PORT', $api_config['db_port']);
define('API_DB_USER', $api_config['db_user']);
define('API_DB_PASS', $api_config['db_pass']);
define('API_DB_NAME', $api_config['db_name']);
define('API_CHARSET', $api_config['charset']);

// API URL configuration
define('API_BASE_URL', $api_config['api_base_url']);
define('API_LOCAL_URL', $api_config['api_local_url']);
define('API_FAVORITES_TOGGLE', $api_config['favorites_toggle_endpoint']);
define('API_FAVORITES_CHECK', $api_config['favorites_check_endpoint']);
define('API_FAVORITES_GET_ALL', $api_config['favorites_get_all_endpoint']);

// API settings
define('API_TIMEOUT', $api_config['timeout']);
define('API_MAX_RETRIES', $api_config['max_retries']);
define('API_CACHE_TIME', $api_config['cache_time']);
define('API_LOG_CALLS', $api_config['log_api_calls']);
define('API_DEBUG', $api_config['debug']);

// Database connection singleton
$api_connection = null;

/**
 * Get a connection to the API database
 * @return mysqli Database connection
 */
function get_api_db_connection() {
    global $api_connection;
    
    // Return existing connection if already established
    if ($api_connection !== null) {
        return $api_connection;
    }
    
    try {
        $api_connection = mysqli_connect(
            API_DB_HOST, 
            API_DB_USER, 
            API_DB_PASS, 
            API_DB_NAME, 
            API_DB_PORT
        );
        
        if (!$api_connection) {
            throw new Exception(mysqli_connect_error(), mysqli_connect_errno());
        }
        
        // Set charset
        mysqli_set_charset($api_connection, API_CHARSET);
        
        return $api_connection;
    } catch (Exception $e) {
        // Log error but don't expose details
        error_log("API Database connection error: " . $e->getMessage());
        return false;
    }
}

/**
 * Close the API database connection
 */
function close_api_db_connection() {
    global $api_connection;
    
    if ($api_connection) {
        mysqli_close($api_connection);
        $api_connection = null;
    }
}
?>
