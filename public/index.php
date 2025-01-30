<?php
require_once('../private/core/initialize.php');
$page_title = 'Welcome to FlavorConnect';

if($session->is_logged_in()) {
    include(SHARED_PATH . '/member_header.php');
} else {
    include(SHARED_PATH . '/public_header.php');
}
?>

<div class="home-content">
    <section class="hero-section">
        <div class="hero-content">
            <h1>Discover Your Next Favorite Recipe</h1>
            <p class="hero-text">Connect with food enthusiasts, share your culinary creations, and explore a world of flavors.</p>
            <div class="hero-buttons">
                <a href="<?php echo url_for('/recipes/index.php'); ?>" class="btn-primary">Browse Recipes</a>
                <a href="<?php echo url_for('/auth/register.php'); ?>" class="btn-secondary">Join Community</a>
            </div>
        </div>
        <div class="hero-image">
            <!-- Image placeholder for hero section -->
            <div class="hero-img-placeholder"></div>
        </div>
    </section>

    <section class="cta-section">
        <div class="cta-card">
            <h3>Share Recipes</h3>
            <p>Share your unique recipes with our growing community of food lovers.</p>
            <a href="<?php echo url_for('/recipes/new.php'); ?>" class="btn-text">Start Sharing</a>
        </div>
        <div class="cta-card">
            <h3>Find Inspiration</h3>
            <p>Discover new recipes and cooking techniques from talented chefs.</p>
            <a href="<?php echo url_for('/recipes/index.php'); ?>" class="btn-text">Explore Now</a>
        </div>
        <div class="cta-card">
            <?php if (!$session->is_logged_in()) { ?>
                <h3>Join Our Community</h3>
                <p>Create an account to share recipes and connect with food lovers.</p>
                <a href="<?php echo url_for('/auth/register.php'); ?>" class="btn-text">Sign Up Now</a>
            <?php } else { ?>
                <h3>Your Recipe Box</h3>
                <p>Access your saved recipes and cooking collections.</p>
                <a href="<?php echo url_for('/user/recipe-box.php'); ?>" class="btn-text">View Collection</a>
            <?php } ?>
        </div>
    </section>

    <section class="featured-recipes">
        <h2>Featured Recipes</h2>
        <div class="recipe-grid">
            <?php
            // Placeholder for featured recipes
            // In reality, this would be populated from the database
            $featured_recipes = [
                ['title' => 'Italian Pasta Carbonara', 'chef' => 'Chef Maria'],
                ['title' => 'Japanese Ramen Bowl', 'chef' => 'Chef Tanaka'],
                ['title' => 'Mediterranean Salad', 'chef' => 'Chef Alex'],
                ['title' => 'French Croissants', 'chef' => 'Chef Pierre']
            ];

            foreach($featured_recipes as $recipe) {
                echo '<div class="recipe-card">';
                echo '<div class="recipe-image-placeholder"></div>';
                echo '<h3>' . h($recipe['title']) . '</h3>';
                echo '<p>By ' . h($recipe['chef']) . '</p>';
                echo '</div>';
            }
            ?>
        </div>
    </section>
</div>

<?php include(SHARED_PATH . '/footer.php'); ?>
