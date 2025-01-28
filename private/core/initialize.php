<?php
ob_start(); // turn on output buffering

// Load configuration first
require_once(dirname(__DIR__) . '/config/config.php');

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Debug session state
error_log("Session state in initialize.php:");
error_log("Session ID: " . session_id());
error_log("Session Data: " . print_r($_SESSION, true));

// Custom autoloader for FlavorConnect classes
spl_autoload_register(function($class) {
    // Keep the original class name for the file
    $class_file = PRIVATE_PATH . '/classes/' . $class . '.class.php';
    
    if(file_exists($class_file)) {
        require_once($class_file);
    }
});

// Load core functions
require_once(PRIVATE_PATH . '/core/functions.php');
require_once(PRIVATE_PATH . '/core/database_functions.php');
require_once(PRIVATE_PATH . '/core/validation_functions.php');
require_once(PRIVATE_PATH . '/core/error_functions.php');

// Create database connection
$db = db_connect();

// Set database connection for DatabaseObject
DatabaseObject::set_database($db);

// Initialize a session
$session = new Session();

// Debug session object
error_log("Session object created:");
error_log("Is logged in: " . ($session->is_logged_in() ? "true" : "false"));
