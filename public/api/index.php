<?php
/**
 * API Entry Point
 * 
 * This is a simplified API entry point that provides information about the available API endpoints.
 * Currently, only the toggle_favorite.php endpoint is implemented and in use.
 */

require_once('../../private/core/initialize.php');

// Set content type to JSON
header('Content-Type: application/json');

// Return API information
echo json_encode([
    'success' => true,
    'message' => 'FlavorConnect API',
    'version' => '1.0.0',
    'endpoints' => [
        'toggle_favorite' => [
            'url' => '/api/toggle_favorite.php',
            'methods' => ['GET', 'POST'],
            'description' => 'Toggle favorite status for a recipe',
            'parameters' => [
                'GET' => [
                    'recipe_id' => 'Required. The ID of the recipe to check favorite status.'
                ],
                'POST' => [
                    'recipe_id' => 'Required. The ID of the recipe to toggle favorite status.'
                ]
            ],
            'responses' => [
                'GET' => [
                    'success' => 'Boolean indicating if the request was successful',
                    'is_favorited' => 'Boolean indicating if the recipe is favorited'
                ],
                'POST' => [
                    'success' => 'Boolean indicating if the request was successful',
                    'is_favorited' => 'Boolean indicating the new favorite status'
                ]
            ]
        ]
    ],
    'production_endpoints' => [
        'toggle_favorite' => '/api/favorites/toggle',
        'check_favorite' => '/api/favorites/{userId}/{recipeId}',
        'get_all_favorites' => '/api/favorites/{userId}'
    ],
    'time' => date('Y-m-d H:i:s')
]);
