<?php
require_once('../../private/core/initialize.php');
require_once(PRIVATE_PATH . '/core/api_functions.php');

// Set content type to JSON
header('Content-Type: application/json');

// Ensure user is logged in
if (!$session->is_logged_in()) {
    echo json_encode(['success' => false, 'error' => 'You must be logged in to manage favorites']);
    exit;
}

$user_id = $session->get_user_id();
$request_method = $_SERVER['REQUEST_METHOD'];

// Debug information
error_log("Direct favorite API called with method: " . $request_method);

// Handle GET requests (check status)
if ($request_method === 'GET') {
    $recipe_id = isset($_GET['recipe_id']) ? intval($_GET['recipe_id']) : 0;
    
    if (!$recipe_id) {
        echo json_encode(['success' => false, 'error' => 'Recipe ID is required']);
        exit;
    }
    
    $is_favorited = RecipeFavorite::is_favorited($user_id, $recipe_id);
    
    echo json_encode([
        'success' => true,
        'is_favorited' => $is_favorited
    ]);
    exit;
}
// Handle POST requests (toggle favorite status)
elseif ($request_method === 'POST') {
    // Get recipe ID from POST data
    $recipe_id = isset($_POST['recipe_id']) ? intval($_POST['recipe_id']) : 0;
    
    // If no POST data, try to get from JSON input
    if (!$recipe_id) {
        $json_data = file_get_contents('php://input');
        $data = json_decode($json_data, true);
        
        if (isset($data['recipeId'])) {
            $recipe_id = intval($data['recipeId']);
        }
    }
    
    if (!$recipe_id) {
        echo json_encode(['success' => false, 'error' => 'Recipe ID is required']);
        exit;
    }
    
    $result = RecipeFavorite::toggle_favorite($user_id, $recipe_id);
    echo json_encode($result);
    exit;
}
// Handle other request methods
else {
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}
?>
