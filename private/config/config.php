<?php
/**
 * Application Configuration
 */

// Database Configuration - Docker Development
define('DB_HOST', 'db');  // 'db' is the service name in docker-compose
define('DB_USER', 'hcvaughn');
define('DB_PASS', '@connect4Establish');
define('DB_NAME', 'flavorconnect');

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
define('MAX_FILE_SIZE', 10485760);  // 10MB in bytes
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);  // Updated to include all supported formats

// Session Settings
define('SESSION_EXPIRY', 7200);  // 2 hours in seconds

// Environment Setting
define('DEVELOPMENT_MODE', true);  // Set to false in production

// Error Reporting - Based on environment
if (DEVELOPMENT_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

// Time Zone
date_default_timezone_set('America/New_York');
