<?php
// Simple version of toggle_favorite.php with minimal dependencies
// Force display of errors for debugging but capture them instead of displaying
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Log all requests to this endpoint
error_log("toggle_favorite.php called with method: " . $_SERVER['REQUEST_METHOD']);
error_log("GET data: " . print_r($_GET, true));
error_log("POST data: " . print_r($_POST, true));
$raw_input = file_get_contents('php://input');
error_log("Raw input: " . $raw_input);

// Set content type to JSON before any output
header('Content-Type: application/json');

// Include initialize.php which has its own ob_start()
require_once('../../private/core/initialize.php');
    
try {
    // Check if user is logged in
    if (!$session->is_logged_in()) {
        error_log("User not logged in");
        echo json_encode([
            'success' => false,
            'error' => 'User not logged in',
            'login_required' => true
        ]);
        exit;
    }
    
    $user_id = $session->get_user_id();
    error_log("User ID: " . $user_id);
    $request_method = $_SERVER['REQUEST_METHOD'];
    
    // Handle GET requests (check favorite status)
    if ($request_method === 'GET') {
        $recipe_id = isset($_GET['recipe_id']) ? (int)$_GET['recipe_id'] : 0;
        error_log("GET request with recipe_id: " . $recipe_id);
        
        if (!$recipe_id) {
            echo json_encode(['success' => false, 'error' => 'Recipe ID is required']);
            exit;
        }
        
        $is_favorited = RecipeFavorite::is_favorited($user_id, $recipe_id);
        error_log("Is favorited: " . ($is_favorited ? 'yes' : 'no'));
        echo json_encode(['success' => true, 'is_favorited' => $is_favorited]);
        exit;
    }
    // Handle POST requests (toggle favorite status)
    elseif ($request_method === 'POST') {
        // Try to get recipe_id from POST data
        $recipe_id = isset($_POST['recipe_id']) ? (int)$_POST['recipe_id'] : 0;
        error_log("POST request with recipe_id from POST: " . $recipe_id);
        
        // If not in POST, try to get from JSON input
        if (!$recipe_id) {
            $json_data = file_get_contents('php://input');
            $data = json_decode($json_data, true);
            error_log("Decoded JSON: " . print_r($data, true));
            
            if (isset($data['recipeId'])) {
                $recipe_id = (int)$data['recipeId'];
                error_log("Recipe ID from JSON recipeId: " . $recipe_id);
            } elseif (isset($data['recipe_id'])) {
                $recipe_id = (int)$data['recipe_id'];
                error_log("Recipe ID from JSON recipe_id: " . $recipe_id);
            }
        }
        
        if (!$recipe_id) {
            error_log("No recipe ID found in request");
            echo json_encode(['success' => false, 'error' => 'Recipe ID is required']);
            exit;
        }
        
        // Toggle favorite status
        error_log("Toggling favorite for user " . $user_id . " and recipe " . $recipe_id);
        $result = RecipeFavorite::toggle_favorite($user_id, $recipe_id);
        error_log("Toggle result: " . print_r($result, true));
        
        // Clear any existing output buffers
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        
        // Set headers again after clearing buffers
        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }
    // Handle other request methods
    else {
        error_log("Method not allowed: " . $request_method);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
        exit;
    }
} catch (Exception $e) {
    // Log the error
    error_log('Error in toggle_favorite.php: ' . $e->getMessage());
    error_log('Error trace: ' . $e->getTraceAsString());
    
    // Clear any existing output buffers
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    
    // Set headers again after clearing buffers
    header('Content-Type: application/json');
    
    // Return a JSON error response
    echo json_encode([
        'success' => false,
        'error' => 'Server error: ' . $e->getMessage()
    ]);
    exit;
}

// If we get here, something unexpected happened
// Clear any existing output buffers
while (ob_get_level() > 0) {
    ob_end_clean();
}

// Set headers again after clearing buffers
header('Content-Type: application/json');

// Ensure we always return JSON
echo json_encode([
    'success' => false,
    'error' => 'An unexpected error occurred'
]);
?>
