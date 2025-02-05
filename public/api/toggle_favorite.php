<?php
require_once('../../private/initialize.php');
require_once(PRIVATE_PATH . '/classes/RecipeFavorite.class.php');

// Ensure request is POST and user is logged in
if(!is_post_request() || !$session->is_logged_in()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Get recipe ID from POST data
$recipe_id = $_POST['recipe_id'] ?? '';
if(!$recipe_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Recipe ID is required']);
    exit;
}

// Toggle favorite status
$is_favorited = RecipeFavorite::toggle_favorite($session->get_user_id(), $recipe_id);

// Return new status
echo json_encode([
    'success' => true,
    'is_favorited' => $is_favorited
]);
?>
