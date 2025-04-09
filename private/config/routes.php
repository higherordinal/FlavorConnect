<?php
/**
 * Routes Configuration
 * 
 * This file defines all the routes for the FlavorConnect application.
 * Routes are registered with the Router instance passed to this file.
 */

// Home routes
$router->get('/', 'index.php', 'home');

// Recipe routes
$router->group('/recipes', function($router) {
    $router->get('/', 'recipes/index.php', 'recipes.index');
    
    // Apply recipe context preservation to show page
    $router->get('/show.php', 'recipes/show.php', 'recipes.show')
           ->middleware('preserve_recipe_context');
    
    $router->get('/new.php', 'recipes/new.php', 'recipes.new');
    $router->post('/new.php', 'recipes/new.php', 'recipes.create');
    
    $router->get('/edit.php', 'recipes/edit.php', 'recipes.edit')
           ->middleware('preserve_recipe_context');
    $router->post('/edit.php', 'recipes/edit.php', 'recipes.update')
           ->middleware('preserve_recipe_context');
           
    $router->post('/delete.php', 'recipes/delete.php', 'recipes.delete');
    $router->get('/toggle_favorite.php', 'recipes/toggle_favorite.php', 'recipes.favorites.toggle');
});

// Authentication routes
$router->group('/auth', function($router) {
    $router->get('/login.php', 'auth/login.php', 'auth.login');
    $router->post('/login.php', 'auth/login.php');
    $router->get('/register.php', 'auth/register.php', 'auth.register');
    $router->post('/register.php', 'auth/register.php');
    $router->get('/logout.php', 'auth/logout.php', 'auth.logout');
});

// User routes
$router->group('/users', function($router) {
    $router->get('/favorites.php', 'users/favorites.php', 'users.favorites');
    $router->get('/profile.php', 'users/profile.php', 'users.profile');
    $router->post('/profile.php', 'users/profile.php', 'users.profile.update');
    $router->post('/toggle_favorite.php', 'users/toggle_favorite.php', 'users.favorites.toggle');
}, ['auth']);

// API routes
$router->group('/api', function($router) {
    $router->get('/', 'api/index.php', 'api.index');
    
    // Favorites API
    $router->get('/toggle_favorite.php', 'api/toggle_favorite.php', 'api.favorites.check');
    $router->post('/toggle_favorite.php', 'api/toggle_favorite.php', 'api.favorites.toggle');
    $router->get('/favorites', 'api/favorites/index.php', 'api.favorites.index');
    $router->post('/favorites/toggle', 'api/favorites/toggle.php', 'api.favorites.toggle');
    
    // Recipes API
    $router->get('/recipes', 'api/recipes/index.php', 'api.recipes.index');
    $router->get('/recipes/{id}', 'api/recipes/show.php', 'api.recipes.show');
    
    // Apply CORS middleware to all API routes
    $router->addMiddleware('api_cors', function($params, $next) {
        // Set CORS headers for API requests
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        
        // Handle preflight requests
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
        
        // Set JSON content type
        header('Content-Type: application/json');
        
        return $next($params);
    });
});

// Admin routes (protected by auth middleware)
$router->group('/admin', function($router) {
    $router->get('/', 'admin/index.php', 'admin.index');
    
    // Categories management
    $router->group('/categories', function($router) {
        $router->get('/', 'admin/categories/index.php', 'admin.categories');
        
        // Diet categories
        $router->group('/diet', function($router) {
            $router->get('/', 'admin/categories/diet/index.php', 'admin.categories.diet');
            $router->get('/new.php', 'admin/categories/diet/new.php', 'admin.categories.diet.new');
            $router->post('/new.php', 'admin/categories/diet/new.php', 'admin.categories.diet.create');
            $router->get('/edit.php', 'admin/categories/diet/edit.php', 'admin.categories.diet.edit');
            $router->post('/edit.php', 'admin/categories/diet/edit.php', 'admin.categories.diet.update');
            $router->post('/delete.php', 'admin/categories/diet/delete.php', 'admin.categories.diet.delete');
        });
        
        // Measurement categories
        $router->group('/measurement', function($router) {
            $router->get('/', 'admin/categories/measurement/index.php', 'admin.categories.measurement');
            $router->get('/new.php', 'admin/categories/measurement/new.php', 'admin.categories.measurement.new');
            $router->post('/new.php', 'admin/categories/measurement/new.php', 'admin.categories.measurement.create');
            $router->get('/edit.php', 'admin/categories/measurement/edit.php', 'admin.categories.measurement.edit');
            $router->post('/edit.php', 'admin/categories/measurement/edit.php', 'admin.categories.measurement.update');
            $router->post('/delete.php', 'admin/categories/measurement/delete.php', 'admin.categories.measurement.delete');
        });
        
        // Style categories
        $router->group('/style', function($router) {
            $router->get('/', 'admin/categories/style/index.php', 'admin.categories.style');
            $router->get('/new.php', 'admin/categories/style/new.php', 'admin.categories.style.new');
            $router->post('/new.php', 'admin/categories/style/new.php', 'admin.categories.style.create');
            $router->get('/edit.php', 'admin/categories/style/edit.php', 'admin.categories.style.edit');
            $router->post('/edit.php', 'admin/categories/style/edit.php', 'admin.categories.style.update');
            $router->post('/delete.php', 'admin/categories/style/delete.php', 'admin.categories.style.delete');
        });
        
        // Type categories
        $router->group('/type', function($router) {
            $router->get('/', 'admin/categories/type/index.php', 'admin.categories.type');
            $router->get('/new.php', 'admin/categories/type/new.php', 'admin.categories.type.new');
            $router->post('/new.php', 'admin/categories/type/new.php', 'admin.categories.type.create');
            $router->get('/edit.php', 'admin/categories/type/edit.php', 'admin.categories.type.edit');
            $router->post('/edit.php', 'admin/categories/type/edit.php', 'admin.categories.type.update');
            $router->post('/delete.php', 'admin/categories/type/delete.php', 'admin.categories.type.delete');
        });
    });
    
    // User management
    $router->group('/users', function($router) {
        $router->get('/', 'admin/users/index.php', 'admin.users');
        $router->get('/new.php', 'admin/users/new.php', 'admin.users.new');
        $router->post('/new.php', 'admin/users/new.php', 'admin.users.create');
        $router->get('/edit.php', 'admin/users/edit.php', 'admin.users.edit');
        $router->post('/edit.php', 'admin/users/edit.php', 'admin.users.update');
        $router->post('/delete.php', 'admin/users/delete.php', 'admin.users.delete');
    });
}, ['auth']); // Apply auth middleware to all admin routes

// Static pages
$router->get('/about.php', 'about.php', 'about');

// Error pages
$router->get('/404.php', '404.php', '404');

// Catch-all route for handling 404 errors
$router->any('*', '404.php', 'not_found');
