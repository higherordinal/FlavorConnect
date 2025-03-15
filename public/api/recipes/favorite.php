<?php
require_once('../../../private/core/initialize.php');
require_once(PRIVATE_PATH . '/core/api_functions.php');

/**
 * Consolidated Favorites API Endpoint
 * 
 * Handles all favorite-related operations:
 * - GET /api/recipes/favorite/{recipeId} - Check if a recipe is favorited
 * - GET /api/recipes/favorites - Get all favorites for the current user
 * - POST /api/recipes/favorite - Toggle favorite status
 */

// Ensure user is logged in for all operations
if (!$session->is_logged_in()) {
    json_error('You must be logged in to manage favorites', 401);
}

$user_id = $session->get_user_id();
$request_method = $_SERVER['REQUEST_METHOD'];
$request_uri = $_SERVER['REQUEST_URI'];

// Debug information
error_log("Request URI: " . $request_uri);
error_log("Request Method: " . $request_method);

// Parse URL to determine the action
$path = parse_url($request_uri, PHP_URL_PATH);
$path = trim($path, '/');
$uri_parts = explode('/', $path);

// Debug information
error_log("URI Parts: " . json_encode($uri_parts));

// Check if we're dealing with a specific recipe ID
$recipe_id = null;
$is_check = false;
$is_favorites_list = false;

// Look for 'favorite' or 'favorites' in the URL
foreach ($uri_parts as $i => $part) {
    if ($part === 'favorite' && isset($uri_parts[$i + 1]) && is_numeric($uri_parts[$i + 1])) {
        $recipe_id = intval($uri_parts[$i + 1]);
        $is_check = true;
    } elseif ($part === 'favorites') {
        $is_favorites_list = true;
    }
}

// Also check query parameters for recipe_id
if (isset($_GET['recipe_id'])) {
    $recipe_id = intval($_GET['recipe_id']);
    $is_check = true;
}

// Handle GET requests (check status or get all favorites)
if ($request_method === 'GET') {
    // Get all favorites
    if ($is_favorites_list) {
        $sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'date';
        $favorites = RecipeFavorite::get_user_favorites($user_id);
        
        echo json_encode([
            'success' => true,
            'favorites' => $favorites
        ]);
        exit;
    }
    
    // Check if a specific recipe is favorited
    if ($is_check && $recipe_id) {
        $is_favorited = RecipeFavorite::is_favorited($user_id, $recipe_id);
        
        echo json_encode([
            'success' => true,
            'is_favorited' => $is_favorited
        ]);
        exit;
    }
    
    // If we got here, the URL pattern didn't match any of our expected patterns
    json_error('Invalid API endpoint', 404);
}
// Handle POST requests (toggle favorite status)
elseif ($request_method === 'POST') {
    // Get recipe ID from POST data or query parameters
    if (!$recipe_id) {
        $json_data = file_get_contents('php://input');
        $data = json_decode($json_data, true);
        
        if (isset($data['recipeId'])) {
            $recipe_id = intval($data['recipeId']);
        } elseif (isset($_POST['recipe_id'])) {
            $recipe_id = intval($_POST['recipe_id']);
        }
    }
    
    if (!$recipe_id) {
        json_error('Recipe ID is required', 400);
    }
    
    // Toggle favorite status
    $result = RecipeFavorite::toggle_favorite($user_id, $recipe_id);
    
    echo json_encode($result);
    exit;
}
// Handle other request methods
else {
    json_error('Method not allowed', 405);
}
