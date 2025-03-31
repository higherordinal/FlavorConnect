<?php
/**
 * Application Configuration
 */

// Environment Detection
function detect_environment() {
    // Check if running in Docker
    if (file_exists('/.dockerenv') || (file_exists('/proc/1/cgroup') && strpos(file_get_contents('/proc/1/cgroup'), 'docker') !== false)) {
        return 'docker';
    }
    
    // Check if running on Bluehost
    if (file_exists('/home/swbhdnmy') || (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'flavorconnect.com') !== false)) {
        return 'bluehost';
    }
    
    // Default to XAMPP/local environment
    return 'xampp';
}

// Define the environment
define('ENVIRONMENT', detect_environment());

// Environment-specific configurations
$config = [
    // Default/common settings
    'default' => [
        'development_mode' => true,
        'session_expiry' => 7200,  // 2 hours in seconds
        'max_file_size' => 10485760,  // 10MB in bytes
        'allowed_image_types' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
        'timezone' => 'America/New_York'
    ],
    
    // Docker environment
    'docker' => [
        'db_host' => 'db',
        'db_port' => 3306,
        'db_user' => 'user',
        'db_pass' => '@connect4Establish',
        'db_name' => 'flavorconnect'
    ],
    
    // XAMPP/local environment
    'xampp' => [
        'db_host' => 'localhost',
        'db_port' => 3308,
        'db_user' => 'user',
        'db_pass' => '@connect4Establish',
        'db_name' => 'flavorconnect'
    ],
    
    // Bluehost/production environment
    'bluehost' => [
        'db_host' => 'localhost',
        'db_port' => 3306,
        'db_user' => 'swbhdnmy_user',
        'db_pass' => '@Connect4establish',
        'db_name' => 'swbhdnmy_db_flavorconnect',
        'development_mode' => false  // Override development mode for production
    ]
];

// Load environment-specific configuration
$env_config = array_merge($config['default'], $config[ENVIRONMENT]);

// Define database constants
define('DB_HOST', $env_config['db_host']);
define('DB_PORT', $env_config['db_port']);
define('DB_USER', $env_config['db_user']);
define('DB_PASS', $env_config['db_pass']);
define('DB_NAME', $env_config['db_name']);

// Path Configuration
define('PROJECT_ROOT', dirname(dirname(__DIR__)));  // Go up two levels from config.php to get to project root
define('PUBLIC_PATH', PROJECT_ROOT . '/public');
define('PRIVATE_PATH', PROJECT_ROOT . '/private');
define('SHARED_PATH', PRIVATE_PATH . '/shared');
define('UPLOADS_PATH', PUBLIC_PATH . '/uploads');
define('ASSETS_PATH', PUBLIC_PATH . '/assets');

// URL Configuration
define('WWW_ROOT', isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] : '');  // Base URL for the application

// Upload Settings
define('MAX_FILE_SIZE', $env_config['max_file_size']);
define('ALLOWED_IMAGE_TYPES', $env_config['allowed_image_types']);

// Session Settings
define('SESSION_EXPIRY', $env_config['session_expiry']);

// Environment Setting
define('DEVELOPMENT_MODE', $env_config['development_mode']);

// Error Reporting - Based on environment
if (DEVELOPMENT_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

// Log the environment for debugging
if (DEVELOPMENT_MODE) {
    error_log('FlavorConnect running in ' . ENVIRONMENT . ' environment');
}

// Time Zone
date_default_timezone_set($env_config['timezone']);
