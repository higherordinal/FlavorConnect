<?php
/**
 * Simplified Favorites API Endpoint
 * 
 * This endpoint handles all favorite-related operations:
 * - GET: Check if a recipe is favorited
 * - POST: Toggle favorite status for a recipe
 */

// Include initialize.php which loads all required dependencies
require_once('../../private/core/initialize.php');

// Set content type to JSON
header('Content-Type: application/json');

// Log all requests to this endpoint for debugging
error_log("toggle_favorite.php called with method: " . $_SERVER['REQUEST_METHOD']);
error_log("GET data: " . print_r($_GET, true));
error_log("POST data: " . print_r($_POST, true));
$raw_input = file_get_contents('php://input');
error_log("Raw input: " . $raw_input);

// Ensure user is logged in for all operations
if (!$session->is_logged_in()) {
    json_error('You must be logged in to manage favorites', 401);
}

$user_id = $session->get_user_id();
$request_method = $_SERVER['REQUEST_METHOD'];

// Handle GET requests (check favorite status)
if ($request_method === 'GET') {
    // Validate request parameters
    $rules = [
        'recipe_id' => ['required', 'number', 'min:1']
    ];
    
    $errors = validate_api_request($_GET, $rules);
    if (!empty($errors)) {
        json_error(implode(', ', $errors));
    }
    
    $recipe_id = (int)$_GET['recipe_id'];
    $is_favorited = RecipeFavorite::is_favorited($user_id, $recipe_id);
    
    json_success(['is_favorited' => $is_favorited]);
}
// Handle POST requests (toggle favorite status)
elseif ($request_method === 'POST') {
    try {
        // Try to get recipe_id from POST data first
        $data = $_POST;
        
        // If not in POST, try to get from JSON input
        if (empty($data)) {
            $json_data = file_get_contents('php://input');
            $decoded = json_decode($json_data, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                json_error('Invalid JSON data');
            }
            
            $data = $decoded;
        }
        
        // Handle different parameter naming conventions
        if (isset($data['recipeId']) && !isset($data['recipe_id'])) {
            $data['recipe_id'] = $data['recipeId'];
        }
        
        // Validate request data
        $rules = [
            'recipe_id' => ['required', 'number', 'min:1']
        ];
        
        $errors = validate_api_request($data, $rules);
        if (!empty($errors)) {
            json_error(implode(', ', $errors));
        }
        
        $recipe_id = (int)$data['recipe_id'];
        $result = RecipeFavorite::toggle_favorite($user_id, $recipe_id);
        
        json_success($result);
    } catch (Exception $e) {
        json_error('Server error: ' . $e->getMessage(), 500);
    }
} else {
    // Handle other request methods
    require_method('GET, POST', $request_method);
}
