<?php
/**
 * Enhanced Router for FlavorConnect
 * 
 * This file serves as the front controller for the application.
 * It uses the new Router class but falls back to legacy routing
 * for backward compatibility during the transition.
 */

require_once('../private/core/initialize.php');
require_once(PRIVATE_PATH . '/classes/Router.class.php');

// Initialize the router
$router = new Router();

// Add authentication middleware
$router->addMiddleware('auth', function($params, $next) use ($session) {
    if (!$session->is_logged_in()) {
        redirect_to('/auth/login.php');
        return;
    }
    return $next($params);
});

// Add recipe context preservation middleware
$router->addMiddleware('preserve_recipe', function($params, $next) {
    if (isset($params['id'])) {
        $_SESSION['current_recipe_id'] = $params['id'];
    }
    return $next($params);
});

// Load routes from configuration file
$router->loadRoutes(PRIVATE_PATH . '/config/routes.php');

// Try to dispatch the request using the new router
$dispatched = $router->dispatch();

// If the new router didn't handle the request, fall back to legacy routing
if ($dispatched === false) {
    // Get the requested URI and query string
    $request_uri = $_SERVER['REQUEST_URI'];
    $query_string = '';
    
    // Extract query string if present
    if (strpos($request_uri, '?') !== false) {
        list($request_uri, $query_string) = explode('?', $request_uri, 2);
        $_SERVER['QUERY_STRING'] = $query_string; // Ensure query string is available
    }
    
    // Remove project folder from URI if in XAMPP environment
    if (defined('PROJECT_FOLDER') && !empty(PROJECT_FOLDER)) {
        $request_uri = str_replace(PROJECT_FOLDER, '', $request_uri);
    }
    
    // Normalize the URI
    $request_uri = '/' . trim($request_uri, '/');
    
    // Define valid routes (add more as needed)
    $valid_routes = [
        '/' => 'index.php',
        '/index.php' => 'index.php',
        '/recipes' => 'recipes/index.php',
        '/recipes/' => 'recipes/index.php',
        '/api/recipes' => 'api/recipes/index.php',
        '/api/recipes/' => 'api/recipes/index.php',
        // Add more routes as needed
    ];
    
    // Define route patterns for dynamic URLs
    $route_patterns = [
        '#^/recipes/show\.php$#' => 'recipes/show.php',
        '#^/recipes/edit\.php$#' => 'recipes/edit.php',
        '#^/api/recipes/\d+$#' => 'api/recipes/show.php',
    ];
    
    // Check if the requested file exists directly
    $requested_file = PUBLIC_PATH . $request_uri;
    if (file_exists($requested_file) && !is_dir($requested_file)) {
        // File exists, include it directly
        include($requested_file);
        exit();
    }
    
    // Check if the route is defined in the valid routes list
    if (isset($valid_routes[$request_uri])) {
        // Route exists, include the corresponding file
        include(PUBLIC_PATH . '/' . $valid_routes[$request_uri]);
        exit();
    }
    
    // Check if the route matches any of the patterns for dynamic URLs
    $matched_pattern = false;
    foreach ($route_patterns as $pattern => $target) {
        if (preg_match($pattern, $request_uri)) {
            $matched_pattern = true;
            
            // For recipe show.php and edit.php, we need to check if the recipe exists
            if ($target === 'recipes/show.php' || $target === 'recipes/edit.php') {
                // Extract the recipe ID from the query string
                $query_string = $_SERVER['QUERY_STRING'] ?? '';
                parse_str($query_string, $params);
                
                // If recipe ID is provided, check if it exists
                if (isset($params['id'])) {
                    $recipe_id = (int)$params['id'];
                    
                    // Try to find the recipe
                    require_once(PRIVATE_PATH . '/classes/Recipe.class.php'); // Ensure Recipe class is loaded
                    
                    try {
                        $recipe = Recipe::find_by_id($recipe_id);
                        
                        if (!$recipe) {
                            // Recipe not found, trigger 404 error
                            error_404("The recipe you're looking for could not be found.");
                            exit();
                        }
                    } catch (Exception $e) {
                        // Log the error and show 404
                        error_log("Error in router.php: " . $e->getMessage());
                        error_404("The recipe you're looking for could not be found.");
                        exit();
                    }
                }
            }
            
            // Include the target file
            include(PUBLIC_PATH . '/' . $target);
            exit();
        }
    }
    
    // If we get here, the route doesn't exist - trigger 404 error
    error_404("The page you requested could not be found.");
}
?>
