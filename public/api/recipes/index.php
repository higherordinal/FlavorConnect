<?php
require_once('../../../private/core/initialize.php');

// Prevent PHP errors from being displayed in the response
ini_set('display_errors', 0);
error_reporting(0);

header('Content-Type: application/json');

// Handle GET requests
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
    
    if ($action === 'get' && isset($_GET['id'])) {
        $recipe = Recipe::find_by_id($_GET['id']);
        
        if ($recipe) {
            $ingredients = RecipeIngredient::find_by_recipe_id($recipe->recipe_id);
            $steps = RecipeStep::find_by_recipe_id($recipe->recipe_id);
            
            // Convert to array and add related data
            $recipe_data = [
                'recipe_id' => $recipe->recipe_id,
                'title' => $recipe->title,
                'description' => $recipe->description,
                'prep_time' => $recipe->prep_time,
                'cook_time' => $recipe->cook_time,
                'img_file_path' => $recipe->img_file_path,
                'video_url' => $recipe->video_url,
                'alt_text' => $recipe->alt_text,
                'ingredients' => array_map(function($ing) {
                    return [
                        'ingredient_id' => $ing->ingredient_id,
                        'name' => $ing->name,
                        'amount' => $ing->amount,
                        'unit' => $ing->unit
                    ];
                }, $ingredients),
                'steps' => array_map(function($step) {
                    return [
                        'step_id' => $step->step_id,
                        'instruction' => $step->instruction,
                        'order_num' => $step->order_num
                    ];
                }, $steps)
            ];
            
            echo json_encode([
                'success' => true,
                'recipe' => $recipe_data
            ]);
            exit;
        }
    }
    
    // If we get here, something went wrong
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'error' => 'Recipe not found'
    ]);
    exit;
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get POST data
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (!$data) {
            throw new Exception('Invalid JSON data');
        }

        $action = $data['action'] ?? '';
        
        // Handle favorite toggle
        if ($action === 'toggle_favorite' && isset($data['recipe_id'])) {
            if (!$session->is_logged_in()) {
                throw new Exception('User must be logged in');
            }

            $recipe = Recipe::find_by_id($data['recipe_id']);
            if (!$recipe) {
                throw new Exception('Recipe not found');
            }

            $user_id = $session->get_user_id();
            $is_favorited = $recipe->is_favorited_by($user_id);

            if ($is_favorited) {
                // Remove from favorites
                $result = $recipe->remove_from_favorites($user_id);
                $message = 'Recipe removed from favorites';
            } else {
                // Add to favorites
                $result = $recipe->add_to_favorites($user_id);
                $message = 'Recipe added to favorites';
            }

            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => $message,
                    'is_favorited' => !$is_favorited
                ]);
                exit;
            } else {
                throw new Exception('Failed to update favorite status');
            }
        } else {
            throw new Exception('Invalid action');
        }
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
        exit;
    }
}

// Handle other HTTP methods
http_response_code(405);
echo json_encode([
    'success' => false,
    'error' => 'Method not allowed'
]);
