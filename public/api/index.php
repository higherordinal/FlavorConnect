<?php
/**
 * API Entry Point
 * All API requests are routed through this file
 */

require_once('../../private/core/initialize.php');
require_once(PRIVATE_PATH . '/core/api/router.php');
require_once(PRIVATE_PATH . '/core/api/endpoints/recipes.php');
require_once(PRIVATE_PATH . '/core/api/endpoints/favorites.php');

// Create router instance
$router = new ApiRouter();

// Register endpoints
$router->register('recipes', [RecipeEndpoints::class, 'handle'], ['GET']);
$router->register('favorites', [FavoriteEndpoints::class, 'handle'], ['POST']);

// Handle request and send response
$response = $router->handleRequest();
$router->sendResponse($response);
