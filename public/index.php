<?php
require_once('../private/core/initialize.php');
$page_title = 'Home';
$page_style = 'home';
$component_styles = ['recipe-favorite', 'forms'];

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
                <?php if (!$session->is_logged_in()) { ?>
                    <a href="<?php echo url_for('/auth/register.php'); ?>" class="btn-secondary">Join Community</a>
                <?php } ?>
            </div>
        </div>
        <div class="hero-image">
            <img src="<?php echo url_for('/assets/images/hero-img.webp'); ?>" alt="Colorful array of fresh ingredients and prepared dishes" class="hero-img">
        </div>
    </section>

    <section class="cta-section">
        <h2 class="visually-hidden">What You Can Do</h2>
        <div class="cta-card">
            <h3>Share Recipes</h3>
            <p>Join our community and share your culinary creations with food enthusiasts worldwide.</p>
            <a href="<?php echo is_logged_in() ? url_for('/recipes/new.php?ref=home') : url_for('/auth/login.php'); ?>" class="btn-text">
                <?php echo is_logged_in() ? 'Start Sharing' : 'Login to Share'; ?>
            </a>
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
                <a href="<?php echo url_for('/users/favorites.php'); ?>" class="btn-text">View Collection</a>
            <?php } ?>
        </div>
    </section>

    <section class="featured-recipes">
        <h2>Featured Recipes</h2>
        <div class="recipe-grid">
            <?php
            // Get featured recipes from the database
            $featured_recipes = Recipe::find_featured(6);
            
            foreach($featured_recipes as $recipe) {
                $chef = User::find_by_id($recipe->user_id);
                $total_time = TimeUtility::format_time($recipe->prep_time + $recipe->cook_time);
                $style = $recipe->style();
                $diet = $recipe->diet();
                $type = $recipe->type();
                $rating = $recipe->get_average_rating();
            ?>
                <?php 
                    // Set variables for the recipe card component
                    $ref = 'home';
                    $user = $chef; // Maintain compatibility with the component
                    
                    // Include the recipe card component
                    include('recipes/recipe-card.php'); 
                ?>
            <?php } ?>
        </div>
    </section>
</div>

<?php include(SHARED_PATH . '/footer.php'); ?>
