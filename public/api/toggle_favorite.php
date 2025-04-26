<?php
/**
 * Simplified Favorites API Endpoint
 * 
 * This endpoint handles all favorite-related operations:
 * - GET: Check if a recipe is favorited
 * - POST: Toggle favorite status for a recipe
 */

// Force clean output - no whitespace, notices or warnings
ob_start();
ini_set('display_errors', 0);
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

// Include initialize.php which loads all required dependencies
require_once('../../private/core/initialize.php');

// Set content type to JSON
header('Content-Type: application/json');

// Enhanced debugging for the toggle_favorite.php endpoint
error_log("========== TOGGLE FAVORITE API CALL ==========");
error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);
error_log("GET data: " . print_r($_GET, true));
error_log("POST data: " . print_r($_POST, true));
$raw_input = file_get_contents('php://input');
error_log("Raw input: " . $raw_input);
error_log("HTTP_REFERER: " . (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'Not set'));
error_log("REQUEST_URI: " . $_SERVER['REQUEST_URI']);
error_log("User ID: " . ($session->is_logged_in() ? $session->get_user_id() : 'Not logged in'));
error_log("Content-Type: " . (isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : 'Not set'));
error_log("============================================");

try {
    // Ensure user is logged in for all operations
    if (!$session->is_logged_in()) {
        ob_end_clean();
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
            ob_end_clean();
            json_error(implode(', ', $errors));
        }
        
        $recipe_id = (int)$_GET['recipe_id'];
        $is_favorited = RecipeFavorite::is_favorited($user_id, $recipe_id);
        
        ob_end_clean();
        json_success(['is_favorited' => $is_favorited]);
    }
    // Handle POST requests (toggle favorite status)
    elseif ($request_method === 'POST') {
        // Try to get recipe_id from POST data first
        $data = $_POST;
        
        // If not in POST, try to get from JSON input
        if (empty($data) || !isset($data['recipe_id'])) {
            $json_data = file_get_contents('php://input');
            $decoded = json_decode($json_data, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                ob_end_clean();
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
            ob_end_clean();
            json_error(implode(', ', $errors));
        }
        
        $recipe_id = (int)$data['recipe_id'];
        
        // Check if the request is coming from the favorites page
        // Make this more resilient to URL changes by checking for the base path
        $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
        
        // Extract the path from the referer URL
        $referer_path = parse_url($referer, PHP_URL_PATH) ?? '';
        
        // Normalize the path to handle both development and production environments
        $normalized_path = normalize_path($referer_path);
        
        // Check if this is from the favorites page (more resilient check)
        $is_from_favorites_page = (strpos($normalized_path, '/users/favorites.php') !== false);
        
        error_log("Referer: " . $referer);
        error_log("Normalized path: " . $normalized_path);
        error_log("Is from favorites page: " . ($is_from_favorites_page ? 'true' : 'false'));
        
        // If the request is from the favorites page, always remove the favorite
        if ($is_from_favorites_page) {
            error_log("Removing favorite for user $user_id, recipe $recipe_id (from favorites page)");
            
            // Check if it's already favorited
            $is_favorited = RecipeFavorite::is_favorited($user_id, $recipe_id);
            
            if ($is_favorited) {
                // Remove the favorite
                $success = RecipeFavorite::remove_favorite($user_id, $recipe_id);
                $result = [
                    'success' => $success,
                    'is_favorited' => false,
                    'message' => 'Removed from favorites'
                ];
            } else {
                // It's not favorited, so just return success
                $result = [
                    'success' => true,
                    'is_favorited' => false,
                    'message' => 'Already not favorited'
                ];
            }
        } else {
            // For other pages, use the normal toggle behavior
            error_log("Toggling favorite for user $user_id, recipe $recipe_id");
            $result = RecipeFavorite::toggle_favorite($user_id, $recipe_id);
        }
        
        // Set cache control headers to prevent caching
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // Add more debugging before returning the response
        error_log("========== TOGGLE FAVORITE RESPONSE ==========");
        error_log("Response: " . print_r($result, true));
        error_log("============================================");
        
        // Return success response
        ob_end_clean();
        json_success($result);
    } 
    else {
        // Handle other request methods
        $allowed_methods = 'GET, POST';
        $methods = array_map('trim', explode(',', $allowed_methods));
        if (!in_array($request_method, $methods)) {
            ob_end_clean();
            json_error('Method not allowed: ' . $request_method . '. Allowed methods: ' . $allowed_methods, 405);
        }
    }
} catch (Exception $e) {
    // Log the error
    error_log('Error in toggle_favorite.php: ' . $e->getMessage());
    error_log('Error trace: ' . $e->getTraceAsString());
    
    // Return a JSON error response
    ob_end_clean();
    json_error('Server error: ' . $e->getMessage(), 500);
}

// If we somehow get here, ensure we return valid JSON
if (ob_get_length()) ob_end_clean();
echo json_encode(['success' => false, 'error' => 'An unexpected error occurred']);
exit;
