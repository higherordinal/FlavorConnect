<?php
require_once('../../../private/core/initialize.php');

// Prevent PHP errors from being displayed in the response
ini_set('display_errors', 0);
error_reporting(0);

header('Content-Type: application/json');

// Build request object
$request = [
    'session' => $session,
    'method' => $_SERVER['REQUEST_METHOD'],
    'query' => $_GET,
    'raw_body' => file_get_contents('php://input'),
    'body' => []
];

// Parse JSON body if present
if (!empty($request['raw_body'])) {
    $request['body'] = json_decode($request['raw_body'], true) ?? [];
}

// Handle GET requests
if ($request['method'] === 'GET') {
    require_method('GET', $request['method']);
    
    $action = $request['query']['action'] ?? '';
    
    if ($action === 'get') {
        $rules = [
            'id' => ['required', 'number', 'min:1']
        ];
        
        process_api_request($request, $rules, function($request) {
            $recipe = Recipe::find_by_id($request['query']['id']);
            if (!$recipe) {
                json_error('Recipe not found', 404);
            }
            
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
        });
    }
    
    json_error('Invalid action');
}

// Handle POST requests
if ($request['method'] === 'POST') {
    require_method('POST', $request['method']);
    
    $rules = [
        'action' => ['required'],
        'recipe_id' => ['required', 'number', 'min:1']
    ];
    
    process_api_request($request, $rules, function($request) {
        if ($request['body']['action'] === 'toggle_favorite') {
            if (!$request['session']->is_logged_in()) {
                json_error('You must be logged in', 401);
            }
            
            $recipe = Recipe::find_by_id($request['body']['recipe_id']);
            if (!$recipe) {
                json_error('Recipe not found', 404);
            }
            
            $user_id = $request['session']->get_user_id();
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
            
            if (!$result) {
                json_error('Failed to update favorite status', 500);
            }
            
            json_success([
                'message' => $message,
                'is_favorited' => !$is_favorited
            ]);
        }
        
        json_error('Invalid action');
    });
}

// Handle other HTTP methods
json_error('Method not allowed', 405);
