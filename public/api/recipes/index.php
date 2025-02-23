<?php
require_once('../../../private/core/initialize.php');

// Load and use the router
require_once('router.js');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    exit;
}

// Set response headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Ensure user is logged in for certain actions
$protected_actions = ['toggle_favorite'];
if (in_array($_POST['action'] ?? '', $protected_actions) && !$session->is_logged_in()) {
    echo json_encode([
        'success' => false,
        'message' => 'Please log in to perform this action'
    ]);
    exit;
}

// Forward request to router
$router->handle($_SERVER['REQUEST_METHOD'], $_POST);
