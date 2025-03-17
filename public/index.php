<?php
require_once('../private/core/initialize.php');
$page_title = 'Home';
$page_style = 'home';

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
            <img src="<?php echo url_for('/assets/images/hero-img.jpg'); ?>" alt="Delicious food spread" class="hero-img">
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
            $featured_recipes = Recipe::find_featured();
            
            foreach($featured_recipes as $recipe) {
                $chef = User::find_by_id($recipe->user_id);
                $total_time = TimeUtility::format_time($recipe->prep_time + $recipe->cook_time);
                $style = $recipe->style();
                $diet = $recipe->diet();
                $type = $recipe->type();
                $rating = $recipe->get_average_rating();
            ?>
                <article class="recipe-card" role="article">
                    <a href="<?php echo url_for('/recipes/show.php?id=' . h(u($recipe->recipe_id)) . '&ref=home'); ?>" 
                       class="recipe-link"
                       aria-labelledby="recipe-title-<?php echo h($recipe->recipe_id); ?>">
                        <div class="recipe-image-container">
                            <?php if($session->is_logged_in()) { ?>
                            <button class="favorite-btn <?php echo $recipe->is_favorited ? 'favorited' : ''; ?>"
                                    data-recipe-id="<?php echo h($recipe->recipe_id); ?>"
                                    aria-label="<?php echo $recipe->is_favorited ? 'Remove from' : 'Add to'; ?> favorites">
                                <i class="<?php echo $recipe->is_favorited ? 'fas' : 'far'; ?> fa-heart"></i>
                            </button>
                            <?php } ?>
                            <img src="<?php echo url_for($recipe->get_image_path()); ?>" 
                                 alt="<?php echo h($recipe->title); ?>" 
                                 class="recipe-image">
                        </div>
                        
                        <div class="recipe-content">
                            <h3 class="recipe-title" id="recipe-title-<?php echo h($recipe->recipe_id); ?>"><?php echo h($recipe->title); ?></h3>
                            
                            <div class="recipe-meta">
                                <span class="rating" aria-label="Rating: <?php echo $rating; ?> out of 5 stars">
                                    <?php 
                                        // Full stars
                                        for ($i = 1; $i <= floor($rating); $i++) {
                                            echo '&#9733;';
                                        }
                                        // Half star if needed
                                        if ($rating - floor($rating) >= 0.5) {
                                            echo '&#189;';
                                        }
                                        // Empty stars
                                        $remaining = 5 - ceil($rating);
                                        for ($i = 1; $i <= $remaining; $i++) {
                                            echo '&#9734;';
                                        }
                                        echo ' <span class="review-count" aria-label="' . $recipe->rating_count() . ' reviews">(' . $recipe->rating_count() . ')</span>';
                                    ?>
                                </span>
                                <span class="time" aria-label="Total time: <?php echo $total_time; ?>">
                                    <?php echo $total_time; ?>
                                </span>
                            </div>

                            <div class="recipe-attributes" role="list">
                                <?php if($style) { ?>
                                    <a href="<?php echo url_for('/recipes/index.php?style=' . h(u($style->id))); ?>" class="recipe-attribute"><?php echo h($style->name); ?></a>
                                <?php } ?>
                                <?php if($diet) { ?>
                                    <a href="<?php echo url_for('/recipes/index.php?diet=' . h(u($diet->id))); ?>" class="recipe-attribute"><?php echo h($diet->name); ?></a>
                                <?php } ?>
                                <?php if($type) { ?>
                                    <a href="<?php echo url_for('/recipes/index.php?type=' . h(u($type->id))); ?>" class="recipe-attribute"><?php echo h($type->name); ?></a>
                                <?php } ?>
                            </div>
                        </div>

                        <div class="recipe-footer">
                            <div class="recipe-author">
                                <span class="author-name">By <?php echo h($chef->username); ?></span>
                            </div>
                        </div>
                    </a>
                </article>
            <?php } ?>
        </div>
    </section>
</div>

<?php include(SHARED_PATH . '/footer.php'); ?>
