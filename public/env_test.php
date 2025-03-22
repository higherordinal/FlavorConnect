<?php
/**
 * Environment Test Script
 * 
 * This script tests whether the application correctly detects
 * the running environment (Docker or XAMPP) and displays the
 * configuration settings for verification.
 */

// Include the configuration files
require_once '../private/config/config.php';
require_once '../private/config/api_config.php';

// Set headers for plain text output
header('Content-Type: text/plain');

// Display environment information
echo "FlavorConnect Environment Test\n";
echo "==============================\n\n";

echo "Detected Environment: " . ENVIRONMENT . "\n";
echo "Database Host: " . DB_HOST . "\n";
echo "API Database Host: " . API_DB_HOST . "\n\n";

// Test database connection
echo "Testing Database Connection...\n";
try {
    $db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($db) {
        echo "SUCCESS: Connected to main database!\n";
        mysqli_close($db);
    } else {
        echo "ERROR: Could not connect to main database.\n";
        echo "Error: " . mysqli_connect_error() . "\n";
    }
} catch (Exception $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
}

// Test API database connection
echo "\nTesting API Database Connection...\n";
$api_db = get_api_db_connection();
if ($api_db) {
    echo "SUCCESS: Connected to API database!\n";
    mysqli_close($api_db);
} else {
    echo "ERROR: Could not connect to API database.\n";
}

// Display server information
echo "\nServer Information:\n";
echo "PHP Version: " . phpversion() . "\n";
echo "Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "\n";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "Server Name: " . $_SERVER['SERVER_NAME'] . "\n";
echo "Server Port: " . $_SERVER['SERVER_PORT'] . "\n";

// Docker detection details
echo "\nDocker Detection Details:\n";
echo "/.dockerenv exists: " . (file_exists('/.dockerenv') ? 'Yes' : 'No') . "\n";

if (file_exists('/proc/1/cgroup')) {
    echo "/proc/1/cgroup contains 'docker': ";
    $contents = file_get_contents('/proc/1/cgroup');
    echo (strpos($contents, 'docker') !== false ? 'Yes' : 'No') . "\n";
    echo "First 100 chars of /proc/1/cgroup: " . substr($contents, 0, 100) . "...\n";
} else {
    echo "/proc/1/cgroup file does not exist\n";
}

// Display some environment variables
echo "\nRelevant Environment Variables:\n";
$env_vars = ['DOCUMENT_ROOT', 'HTTP_HOST', 'REQUEST_SCHEME', 'REMOTE_ADDR'];
foreach ($env_vars as $var) {
    echo "$var: " . (isset($_SERVER[$var]) ? $_SERVER[$var] : 'Not set') . "\n";
}

echo "\nTest completed at: " . date('Y-m-d H:i:s') . "\n";
