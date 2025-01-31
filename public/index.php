<?php
require_once('../private/core/initialize.php');
$page_title = 'Home';

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
            <img src="<?php echo url_for('/assets/images/hero-img.jpg'); ?>" alt="Delicious food spread" class="hero-img">
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
                [
                    'title' => 'Italian Pasta Carbonara',
                    'chef' => 'Chef Maria',
                    'description' => 'A classic Roman pasta dish made with eggs, cheese, pancetta, and black pepper.',
                    'time' => '30 min'
                ],
                [
                    'title' => 'Japanese Ramen Bowl',
                    'chef' => 'Chef Tanaka',
                    'description' => 'Rich and flavorful ramen with tender chashu pork, soft-boiled egg, and fresh vegetables.',
                    'time' => '45 min'
                ],
                [
                    'title' => 'Mediterranean Salad',
                    'chef' => 'Chef Alex',
                    'description' => 'Fresh and healthy salad with tomatoes, cucumbers, olives, and feta cheese.',
                    'time' => '15 min'
                ],
                [
                    'title' => 'French Croissants',
                    'chef' => 'Chef Pierre',
                    'description' => 'Buttery and flaky croissants made with traditional French techniques.',
                    'time' => '3 hrs'
                ]
            ];

            foreach($featured_recipes as $recipe) {
                echo '<div class="recipe-card">';
                echo '<img src="' . url_for('/assets/images/placeholder-recipe.jpg') . '" alt="' . h($recipe['title']) . '" class="recipe-image">';
                echo '<div class="recipe-content">';
                echo '<h3 class="recipe-title">' . h($recipe['title']) . '</h3>';
                echo '<div class="recipe-meta">';
                echo '<span>By ' . h($recipe['chef']) . '</span>';
                echo '<span>' . h($recipe['time']) . '</span>';
                echo '</div>';
                echo '<p class="recipe-description">' . h($recipe['description']) . '</p>';
                echo '</div>';
                echo '</div>';
            }
            ?>
        </div>
    </section>
</div>

<?php include(SHARED_PATH . '/footer.php'); ?>
