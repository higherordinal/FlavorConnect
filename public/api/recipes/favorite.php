<?php
require_once('../../../private/initialize.php');

// Ensure this is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Ensure user is logged in
if (!$session->is_logged_in()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// Get recipe ID from URL
$url_parts = explode('/', $_SERVER['REQUEST_URI']);
$recipe_id = intval($url_parts[count($url_parts) - 2]); // Get second to last part of URL

if (!$recipe_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid recipe ID']);
    exit;
}

// Toggle favorite status
$user_id = $session->get_user_id();
$success = Recipe::toggle_favorite($recipe_id, $user_id);

if ($success) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to toggle favorite']);
}
