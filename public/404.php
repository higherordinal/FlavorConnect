<?php
require_once('../private/initialize.php');
$page_title = '404 - Page Not Found';

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
            <h2>Oops! Recipe Not Found</h2>
            <p>Looks like this dish isn't on our menu. Don't worry, we have plenty of other delicious recipes for you!</p>
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
