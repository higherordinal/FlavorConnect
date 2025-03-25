<?php
ob_start(); // turn on output buffering

// Load configuration first
require_once(dirname(__DIR__) . '/config/config.php');
require_once(dirname(__DIR__) . '/config/api_config.php');

// Load Bluehost compatibility script
require_once(dirname(__DIR__) . '/core/bluehost-compatibility.php');

// Configure session parameters - extend timeout to 24 hours (86400 seconds)
// Set both PHP and session cookie parameters for maximum compatibility
ini_set('session.gc_maxlifetime', 86400); // How long to store session data on server (24 hours)
ini_set('session.cookie_lifetime', 86400); // How long to store the session cookie on client (24 hours)

// Set session cookie parameters before starting the session
session_set_cookie_params([
    'lifetime' => 86400,
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
