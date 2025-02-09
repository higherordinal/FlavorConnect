<?php
/**
 * API Entry Point
 * All API requests are routed through this file
 * 
 * Note: Favorite-related functionality has been moved to the Node.js API
 * running on port 3000. See /api/routes/favorites.js for those endpoints.
 */

require_once('../../private/core/initialize.php');
require_once(PRIVATE_PATH . '/core/api/router.php');
require_once(PRIVATE_PATH . '/core/api/endpoints/recipes.php');

// Create router instance
$router = new ApiRouter();

// Register endpoints
$router->register('recipes', [RecipeEndpoints::class, 'handle'], ['GET']);

// Handle request and send response
$response = $router->handleRequest();
$router->sendResponse($response);
