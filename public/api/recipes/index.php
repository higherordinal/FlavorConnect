<?php
require_once('../../../private/core/initialize.php');

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

// Handle other HTTP methods
http_response_code(405);
echo json_encode([
    'success' => false,
    'error' => 'Method not allowed'
]);
