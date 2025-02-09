<?php
require_once('../../../private/core/initialize.php');

// Prevent PHP errors from being displayed in the response
ini_set('display_errors', 0);
error_reporting(0);

header('Content-Type: application/json');

/**
 * Returns a JSON error response
 * @param string $message Error message
 * @param int $status HTTP status code
 */
function json_error($message, $status = 400) {
    http_response_code($status);
    echo json_encode([
        'success' => false,
        'error' => $message
    ]);
    exit;
}

/**
 * Returns a JSON success response
 * @param array $data Response data
 */
function json_success($data) {
    echo json_encode(array_merge(['success' => true], $data));
    exit;
}

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
            
            json_success(['recipe' => $recipe_data]);
        } else {
            json_error('Recipe not found', 404);
        }
    } else {
        json_error('Recipe not found', 404);
    }
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get POST data
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (!$data || json_last_error() !== JSON_ERROR_NONE) {
            json_error('Invalid JSON data');
        }

        $action = $data['action'] ?? '';
        
        // Handle favorite toggle
        if ($action === 'toggle_favorite') {
            // Validate required fields
            $errors = [];
            if (!isset($data['recipe_id']) || is_blank($data['recipe_id'])) {
                $errors[] = 'Recipe ID is required';
            } elseif (!has_number_between($data['recipe_id'], 1, PHP_INT_MAX)) {
                $errors[] = 'Invalid recipe ID';
            }

            if (!empty($errors)) {
                json_error(implode(', ', $errors));
            }

            if (!$session->is_logged_in()) {
                json_error('User must be logged in', 401);
            }

            $recipe = Recipe::find_by_id($data['recipe_id']);
            if (!$recipe) {
                json_error('Recipe not found', 404);
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
                json_success([
                    'message' => $message,
                    'is_favorited' => !$is_favorited
                ]);
            } else {
                json_error('Failed to update favorite status', 500);
            }
        } else {
            json_error('Invalid action');
        }
    } catch (Exception $e) {
        json_error($e->getMessage(), 500);
    }
}

// Handle other HTTP methods
json_error('Method not allowed', 405);
