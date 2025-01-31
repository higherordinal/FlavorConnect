<?php
require_once('../../private/core/initialize.php');

// Ensure user is logged in
if (!$session->is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to favorite recipes']);
    exit;
}

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get POST data
$recipe_id = $_POST['recipe_id'] ?? '';
$is_favorited = $_POST['is_favorited'] === 'true';
$user_id = $session->get_user_id();

if (!$recipe_id) {
    echo json_encode(['success' => false, 'message' => 'Recipe ID is required']);
    exit;
}

try {
    if ($is_favorited) {
        // Add favorite
        $result = UserFavorite::add_favorite($user_id, $recipe_id);
    } else {
        // Remove favorite
        $result = UserFavorite::remove_favorite($user_id, $recipe_id);
    }

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
