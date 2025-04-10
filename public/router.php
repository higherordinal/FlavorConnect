<?php
/**
 * Simple Router for FlavorConnect
 * 
 * This file serves as a front controller for the application.
 * It handles basic routing with fallbacks to direct file inclusion.
 */

require_once('../private/core/initialize.php');

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

// Define route mappings - static routes
$routes = [
    // Basic pages
    '/' => 'index.php',
    '/index.php' => 'index.php',
    
    // Recipe routes
    '/recipes' => 'recipes/index.php',
    '/recipes/' => 'recipes/index.php',
    '/recipes/index.php' => 'recipes/index.php',
    '/recipes/new' => 'recipes/new.php',
    '/recipes/new/' => 'recipes/new.php',
    '/recipes/new.php' => 'recipes/new.php',
    '/recipes/show.php' => 'recipes/show.php',
    '/recipes/edit.php' => 'recipes/edit.php',
    '/recipes/delete.php' => 'recipes/delete.php',
    
    // User routes
    '/users/profile' => 'users/profile.php',
    '/users/profile/' => 'users/profile.php',
    '/users/profile.php' => 'users/profile.php',
    '/users/favorites' => 'users/favorites.php',
    '/users/favorites/' => 'users/favorites.php',
    '/users/favorites.php' => 'users/favorites.php',
    
    // Authentication routes
    '/auth/login' => 'auth/login.php',
    '/auth/login/' => 'auth/login.php',
    '/auth/login.php' => 'auth/login.php',
    '/auth/register' => 'auth/register.php',
    '/auth/register/' => 'auth/register.php',
    '/auth/register.php' => 'auth/register.php',
    '/auth/logout' => 'auth/logout.php',
    '/auth/logout/' => 'auth/logout.php',
    '/auth/logout.php' => 'auth/logout.php',
    
    // API routes
    '/api/recipes' => 'api/recipes/index.php',
    '/api/recipes/' => 'api/recipes/index.php',
    '/api/recipes/index.php' => 'api/recipes/index.php',
];

/**
 * Helper function to generate URLs for named routes
 * 
 * @param string $route_name Name of the route
 * @param array $params Parameters for the route
 * @param string $page_param Optional name of the pagination parameter
 * @return string Generated URL
 */
function route($route_name, $params = [], $page_param = 'page') {
    // Define route name to path mappings
    $named_routes = [
        // Basic pages
        'home' => '/',
        
        // Recipe routes
        'recipes.index' => '/recipes/index.php',
        'recipes.new' => '/recipes/new.php',
        'recipes.show' => '/recipes/show.php',
        'recipes.edit' => '/recipes/edit.php',
        'recipes.delete' => '/recipes/delete.php',
        
        // User routes
        'users.profile' => '/users/profile.php',
        'users.favorites' => '/users/favorites.php',
        
        // Authentication routes
        'auth.login' => '/auth/login.php',
        'auth.register' => '/auth/register.php',
        'auth.logout' => '/auth/logout.php',
        
        // API routes
        'api.recipes' => '/api/recipes/index.php',
    ];
    
    if (!isset($named_routes[$route_name])) {
        error_log("Route name not found: {$route_name}");
        return ''; // Route name not found
    }
    
    $path = $named_routes[$route_name];
    
    // Add query parameters if provided
    if (!empty($params)) {
        $path .= '?' . http_build_query($params);
    }
    
    return url_for($path);
}

/**
 * Helper function for pagination links in the Pagination class
 * 
 * @param string $route_name Name of the route
 * @param array $params Parameters for the route
 * @param string $page_param Name of the pagination parameter
 * @return string URL pattern with {page} placeholder
 */
function route_links($route_name, $params = [], $page_param = 'page') {
    // Get base URL without pagination parameter
    $filtered_params = array_filter($params, function($key) use ($page_param) {
        return $key !== $page_param;
    }, ARRAY_FILTER_USE_KEY);
    
    $base_url = route($route_name, $filtered_params);
    
    // Add query string separator if needed
    $separator = (strpos($base_url, '?') !== false) ? '&' : '?';
    
    // Return URL pattern with {page} placeholder
    return $base_url . $separator . $page_param . '={page}';
}

// First, check if the requested file exists directly
$requested_file = PUBLIC_PATH . $request_uri;
if (file_exists($requested_file) && !is_dir($requested_file) && pathinfo($requested_file, PATHINFO_EXTENSION) != '') {
    // Direct file access (CSS, JS, images, etc.)
    include($requested_file);
    exit();
}

// Check if we have a direct route mapping
if (isset($routes[$request_uri])) {
    include(PUBLIC_PATH . '/' . $routes[$request_uri]);
    exit();
}

// Handle dynamic routes with query parameters
$dynamic_routes = [
    // Pattern => target file
    '#^/recipes/show\.php$#' => 'recipes/show.php',
    '#^/recipes/edit\.php$#' => 'recipes/edit.php',
    '#^/recipes/delete\.php$#' => 'recipes/delete.php',
    '#^/api/recipes/\d+$#' => 'api/recipes/show.php',
];

foreach ($dynamic_routes as $pattern => $target) {
    if (preg_match($pattern, $request_uri)) {
        // For recipe pages, check if the recipe exists
        if (in_array($target, ['recipes/show.php', 'recipes/edit.php', 'recipes/delete.php'])) {
            parse_str($query_string, $params);
            
            if (isset($params['id'])) {
                $recipe_id = (int)$params['id'];
                
                try {
                    $recipe = Recipe::find_by_id($recipe_id);
                    
                    if (!$recipe) {
                        error_404("The recipe you're looking for could not be found.");
                        exit();
                    }
                    
                    // Store recipe context for navigation
                    if ($target === 'recipes/show.php') {
                        $_SESSION['current_recipe_id'] = $recipe_id;
                        $_SESSION['last_recipe_page'] = $_SERVER['REQUEST_URI'];
                    }
                } catch (Exception $e) {
                    error_log("Router error: " . $e->getMessage());
                    error_404("The recipe you're looking for could not be found.");
                    exit();
                }
            }
        }
        
        include(PUBLIC_PATH . '/' . $target);
        exit();
    }
}

// If we get here, the route doesn't exist - trigger 404 error
error_404("The page you requested could not be found.");
?>
