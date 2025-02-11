<?php
require_once('../../../private/core/initialize.php');

// Validate request method
require_method('GET', $_SERVER['REQUEST_METHOD']);

// Validate required parameters
$rules = [
    'action' => ['required'],
    'recipe_id' => ['required', 'number', 'min:1']
];

process_api_request([
    'method' => $_SERVER['REQUEST_METHOD'],
    'query' => $_GET,
    'body' => $_GET  // For GET requests, use query params
], $rules, function($request) {
    $action = $request['body']['action'];
    $recipe_id = $request['body']['recipe_id'];
    
    switch ($action) {
        case 'get_comments':
            $recipe = Recipe::find_by_id($recipe_id);
            if (!$recipe) {
                json_error('Recipe not found');
            }
            
            $comments = $recipe->get_comments();
            json_success(['comments' => $comments]);
            break;
            
        default:
            json_error('Invalid action');
    }
});
