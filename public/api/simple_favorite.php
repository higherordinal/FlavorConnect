<?php
// Force display of errors for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set content type to JSON
header('Content-Type: application/json');

// Simple response to test if API is working
echo json_encode([
    'success' => true,
    'message' => 'Simple favorite API is working',
    'is_favorited' => true,
    'time' => date('Y-m-d H:i:s')
]);
?>
