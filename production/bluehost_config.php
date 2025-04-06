<?php
/**
 * Bluehost-specific Configuration
 * This file contains hardcoded configuration values for the Bluehost environment
 */

// Bluehost-specific configurations
$config = [
    // Default settings
    'development_mode' => false,
    'session_expiry' => 86400,  // 24 hours in seconds
    'max_file_size' => 10485760,  // 10MB in bytes
    'allowed_image_types' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
    'timezone' => 'America/New_York',
    
    // Bluehost database settings
    'db_host' => 'localhost',
    'db_port' => 3306,
    'db_user' => 'swbhdnmy_user',
    'db_pass' => '@Connect4establish',
    'db_name' => 'swbhdnmy_db_flavorconnect'
];

// Define database constants
define('DB_HOST', $config['db_host']);
define('DB_PORT', $config['db_port']);
define('DB_USER', $config['db_user']);
define('DB_PASS', $config['db_pass']);
define('DB_NAME', $config['db_name']);

// Path Configuration - Hardcoded for Bluehost
define('PROJECT_ROOT', '/home2/swbhdnmy/public_html/website_7135c1f5');
define('PUBLIC_PATH', PROJECT_ROOT . '/public');
define('PRIVATE_PATH', PROJECT_ROOT . '/private');
define('SHARED_PATH', PRIVATE_PATH . '/shared');
define('UPLOADS_PATH', PUBLIC_PATH . '/uploads');
define('ASSETS_PATH', PUBLIC_PATH . '/assets');

// URL Configuration
define('WWW_ROOT', 'https://flavorconnect.space');
define('ENVIRONMENT', 'production');

// Upload Settings
define('MAX_FILE_SIZE', $config['max_file_size']);
define('ALLOWED_IMAGE_TYPES', $config['allowed_image_types']);

// Session Settings
define('SESSION_EXPIRY', $config['session_expiry']);

// Error Reporting - Production settings
error_reporting(0);
ini_set('display_errors', '0');

// Time Zone
date_default_timezone_set($config['timezone']);
?>
