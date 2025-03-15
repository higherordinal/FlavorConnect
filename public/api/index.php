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
            'description' => 'Toggle favorite status for a recipe'
        ]
    ],
    'time' => date('Y-m-d H:i:s')
]);
