<?php
/**
 * Application Configuration
 */

// Database Configuration - Local Development
define('DB_HOST', 'localhost:3306');
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
define('WWW_ROOT', '/FlavorConnect/public');  // Base URL for the application
define("PRIVATE_WWW_ROOT", str_replace('/public', '/private', WWW_ROOT));

// Upload Settings
define('MAX_FILE_SIZE', 5242880);  // 5MB in bytes
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png']);  // Updated to match validation_functions.php

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
