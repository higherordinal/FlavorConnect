<?php
// Simple test file to check if API routing is working
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'message' => 'API is working',
    'time' => date('Y-m-d H:i:s')
]);
?>
