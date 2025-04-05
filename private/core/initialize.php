<?php
ob_start(); // turn on output buffering

// Load configuration first
require_once(dirname(__DIR__) . '/config/config.php');
require_once(dirname(__DIR__) . '/config/api_config.php');

// Detect base path for XAMPP environment
if (ENVIRONMENT === 'xampp') {
    $script_name = $_SERVER['SCRIPT_NAME'] ?? '';
    $script_parts = explode('/', $script_name);
    
    // Extract project folder name (should be "FlavorConnect")
    if (count($script_parts) > 1) {
        $project_folder = $script_parts[1];
        if (!empty($project_folder)) {
            // Define a constant for the project folder
            define('PROJECT_FOLDER', '/' . $project_folder);
        } else {
            define('PROJECT_FOLDER', '');
        }
    } else {
        define('PROJECT_FOLDER', '');
    }
} else {
    // For Docker and other environments, no project folder is needed
    define('PROJECT_FOLDER', '');
}

// No environment-specific scripts needed

// Configure session parameters using SESSION_EXPIRY from config.php
// Set both PHP and session cookie parameters for maximum compatibility
ini_set('session.gc_maxlifetime', SESSION_EXPIRY); // How long to store session data on server
ini_set('session.cookie_lifetime', SESSION_EXPIRY); // How long to store the session cookie on client

// Set session cookie parameters before starting the session
session_set_cookie_params([
    'lifetime' => SESSION_EXPIRY,
    'path' => '/',
    'domain' => '', // Current domain
    'secure' => false, // Set to true if using HTTPS
    'httponly' => true, // Prevent JavaScript access to session cookie
    'samesite' => 'Lax' // Prevent CSRF attacks
]);

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
    
    // Regenerate session ID periodically to prevent session fixation
    if (!isset($_SESSION['last_regeneration'])) {
        $_SESSION['last_regeneration'] = time();
    } else if (time() - $_SESSION['last_regeneration'] > 3600) { // Regenerate every hour
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
}

// Custom autoloader for FlavorConnect classes
spl_autoload_register(function($class) {
    // Keep the original class name for the file
    $class_file = PRIVATE_PATH . '/classes/' . $class . '.class.php';
    
    if(file_exists($class_file)) {
        require_once($class_file);
    }
});

// Load core functions
require_once(PRIVATE_PATH . '/core/core_utilities.php');
require_once(PRIVATE_PATH . '/core/validation_functions.php');
require_once(PRIVATE_PATH . '/core/api_functions.php');
require_once(PRIVATE_PATH . '/core/auth_functions.php');
require_once(PRIVATE_PATH . '/core/database_functions.php');
require_once(PRIVATE_PATH . '/core/error_functions.php');

// Create database connection
$db = db_connect();

// Set database connection for DatabaseObject
DatabaseObject::set_database($db);

// Initialize a session
$session = new Session();
