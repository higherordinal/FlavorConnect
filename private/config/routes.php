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
    $router->get('/show.php', 'recipes/show.php', 'recipes.show');
    $router->get('/new.php', 'recipes/new.php', 'recipes.new');
    $router->get('/edit.php', 'recipes/edit.php', 'recipes.edit');
    $router->post('/delete.php', 'recipes/delete.php', 'recipes.delete');
    $router->get('/toggle_favorite.php', 'recipes/toggle_favorite.php', 'recipes.toggle_favorite');
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
});

// API routes
$router->group('/api', function($router) {
    $router->get('/', 'api/index.php', 'api.index');
    $router->get('/toggle_favorite.php', 'api/toggle_favorite.php', 'api.check_favorite');
    $router->post('/toggle_favorite.php', 'api/toggle_favorite.php', 'api.toggle_favorite');
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
            $router->post('/create.php', 'admin/categories/diet/create.php', 'admin.categories.diet.create');
            $router->get('/edit.php', 'admin/categories/diet/edit.php', 'admin.categories.diet.edit');
            $router->post('/update.php', 'admin/categories/diet/update.php', 'admin.categories.diet.update');
            $router->post('/delete.php', 'admin/categories/diet/delete.php', 'admin.categories.diet.delete');
        });
        
        // Measurement categories
        $router->group('/measurement', function($router) {
            $router->get('/', 'admin/categories/measurement/index.php', 'admin.categories.measurement');
            $router->get('/new.php', 'admin/categories/measurement/new.php', 'admin.categories.measurement.new');
            $router->post('/create.php', 'admin/categories/measurement/create.php', 'admin.categories.measurement.create');
            $router->get('/edit.php', 'admin/categories/measurement/edit.php', 'admin.categories.measurement.edit');
            $router->post('/update.php', 'admin/categories/measurement/update.php', 'admin.categories.measurement.update');
            $router->post('/delete.php', 'admin/categories/measurement/delete.php', 'admin.categories.measurement.delete');
        });
        
        // Style categories
        $router->group('/style', function($router) {
            $router->get('/', 'admin/categories/style/index.php', 'admin.categories.style');
            $router->get('/new.php', 'admin/categories/style/new.php', 'admin.categories.style.new');
            $router->post('/create.php', 'admin/categories/style/create.php', 'admin.categories.style.create');
            $router->get('/edit.php', 'admin/categories/style/edit.php', 'admin.categories.style.edit');
            $router->post('/update.php', 'admin/categories/style/update.php', 'admin.categories.style.update');
            $router->post('/delete.php', 'admin/categories/style/delete.php', 'admin.categories.style.delete');
        });
        
        // Type categories
        $router->group('/type', function($router) {
            $router->get('/', 'admin/categories/type/index.php', 'admin.categories.type');
            $router->get('/new.php', 'admin/categories/type/new.php', 'admin.categories.type.new');
            $router->post('/create.php', 'admin/categories/type/create.php', 'admin.categories.type.create');
            $router->get('/edit.php', 'admin/categories/type/edit.php', 'admin.categories.type.edit');
            $router->post('/update.php', 'admin/categories/type/update.php', 'admin.categories.type.update');
            $router->post('/delete.php', 'admin/categories/type/delete.php', 'admin.categories.type.delete');
        });
    });
    
    // User management
    $router->group('/users', function($router) {
        $router->get('/', 'admin/users/index.php', 'admin.users');
        $router->get('/new.php', 'admin/users/new.php', 'admin.users.new');
        $router->post('/create.php', 'admin/users/create.php', 'admin.users.create');
        $router->get('/edit.php', 'admin/users/edit.php', 'admin.users.edit');
        $router->post('/update.php', 'admin/users/update.php', 'admin.users.update');
        $router->post('/delete.php', 'admin/users/delete.php', 'admin.users.delete');
    });
}, ['auth']); // Apply auth middleware to all admin routes

// Static pages
$router->get('/about.php', 'about.php', 'about');
$router->get('/contact.php', 'contact.php', 'contact');

// Error pages
$router->get('/404.php', '404.php', '404');
