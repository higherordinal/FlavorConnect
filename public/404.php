<?php
// This file needs to work when called directly by Apache or through the error_404() function
// First, check if we need to initialize the application
if (!defined('PRIVATE_PATH')) {
    // If called directly by Apache, we need to initialize the application
    // Use a path that works in both XAMPP and Docker environments
    $init_path = file_exists('../private/initialize.php') ? '../private/initialize.php' : '/var/www/html/private/initialize.php';
    require_once($init_path);
}

// Set the page title and style
$page_title = '404 - Page Not Found';
$page_style = '404'; 

// Use member header if user is logged in, otherwise use public header
if(is_logged_in()) {
    include(SHARED_PATH . '/member_header.php');
} else {
    include(SHARED_PATH . '/public_header.php');
}
?>

<main class="error-404">
    <div class="container">
        <div class="error-content">
            <h1>404</h1>
            <h2>Oops! Page Not Found</h2>
            <p><?php 
                // Display custom message if provided, otherwise use default
                if (isset($error_message) && !empty($error_message)) {
                    echo h($error_message);
                } else {
                    echo 'The page you requested could not be found. It may have been moved or deleted.';
                }
            ?></p>
            <div class="error-actions">
                <a href="<?php echo url_for('/'); ?>" class="btn btn-primary">
                    <i class="fas fa-home"></i> Return Home
                </a>
                <a href="<?php echo url_for('/recipes'); ?>" class="btn btn-secondary">
                    <i class="fas fa-utensils"></i> Browse Recipes
                </a>
            </div>
        </div>
    </div>
</main>

<?php include(SHARED_PATH . '/footer.php'); ?>
