<?php
require_once('../../private/core/initialize.php');

// Prevent caching
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

// Only allow GET requests for safety
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    header('Allow: GET');
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Set JSON content type and CORS headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Debug logging
error_log("API Request: " . $_SERVER['REQUEST_URI']);

// Handle different API endpoints
$action = $_GET['action'] ?? '';
error_log("Action: " . $action);

try {
    switch ($action) {
        case 'list':
            // Get all recipes
            $recipes = Recipe::find_all();
            error_log("Found " . count($recipes) . " recipes");
            $response = [];

            foreach($recipes as $recipe) {
                // Get additional data
                $user = User::find_by_id($recipe->user_id);
                $is_favorited = false;
                $rating = $recipe->get_average_rating();
                $rating_count = $recipe->rating_count();

                // Check if recipe is favorited by current user
                if($session->is_logged_in()) {
                    $is_favorited = UserFavorite::is_favorite($session->get_user_id(), $recipe->recipe_id);
                }

                $response[] = [
                    'id' => $recipe->recipe_id,
                    'title' => $recipe->title,
                    'description' => $recipe->description,
                    'style' => $recipe->style() ? $recipe->style()->name : null,
                    'style_id' => $recipe->style_id,
                    'diet' => $recipe->diet() ? $recipe->diet()->name : null,
                    'diet_id' => $recipe->diet_id,
                    'type' => $recipe->type() ? $recipe->type()->name : null,
                    'type_id' => $recipe->type_id,
                    'prep_time' => $recipe->prep_time,
                    'cook_time' => $recipe->cook_time,
                    'image' => $recipe->img_file_path,
                    'created_at' => $recipe->created_at,
                    'user_id' => $recipe->user_id,
                    'username' => $user ? $user->username : 'Unknown',
                    'is_favorited' => $is_favorited,
                    'rating' => $rating,
                    'rating_count' => $rating_count
                ];
            }
            error_log("Response: " . json_encode($response));
            echo json_encode($response);
            break;

        case 'get':
            // Get single recipe
            $id = $_GET['id'] ?? null;
            if (!$id) {
                throw new Exception('Recipe ID is required');
            }

            $recipe = Recipe::find_by_id($id);
            if (!$recipe) {
                throw new Exception('Recipe not found');
            }

            $user = User::find_by_id($recipe->user_id);
            $is_favorited = false;
            if($session->is_logged_in()) {
                $is_favorited = UserFavorite::is_favorite($session->get_user_id(), $recipe->recipe_id);
            }

            echo json_encode([
                'id' => $recipe->recipe_id,
                'title' => $recipe->title,
                'description' => $recipe->description,
                'style' => $recipe->style() ? $recipe->style()->name : null,
                'style_id' => $recipe->style_id,
                'diet' => $recipe->diet() ? $recipe->diet()->name : null,
                'diet_id' => $recipe->diet_id,
                'type' => $recipe->type() ? $recipe->type()->name : null,
                'type_id' => $recipe->type_id,
                'prep_time' => $recipe->prep_time,
                'cook_time' => $recipe->cook_time,
                'image' => $recipe->img_file_path,
                'created_at' => $recipe->created_at,
                'user_id' => $recipe->user_id,
                'username' => $user ? $user->username : 'Unknown',
                'is_favorited' => $is_favorited,
                'rating' => $recipe->get_average_rating(),
                'rating_count' => $recipe->rating_count()
            ]);
            break;

        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    error_log("API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => 'An error occurred',
        'details' => $e->getMessage()
    ]);
}
?>
