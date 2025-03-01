<?php
require_once('../../private/core/initialize.php');
require_once(PRIVATE_PATH . '/classes/Recipe.class.php');
require_once(PRIVATE_PATH . '/classes/RecipeAttribute.class.php');
require_once(PRIVATE_PATH . '/classes/TimeUtility.class.php');

require_login();

$page_title = 'User Favorites';
$page_style = 'favorites';

// Get user's favorite recipes
$favorites = Recipe::find_favorites_by_user_id($session->get_user_id());

include(SHARED_PATH . '/member_header.php');
?>

<link rel="stylesheet" href="<?php echo url_for('/assets/css/pages/recipe-gallery.css'); ?>">
<link rel="stylesheet" href="<?php echo url_for('/assets/css/pages/favorites.css'); ?>">

<div class="container">
    <a href="<?php echo url_for('/recipes/index.php'); ?>" class="back-link">
        <i class="fas fa-arrow-left"></i>
        Back to Recipes
    </a>
</div>

<div class="recipe-gallery">
    <div class="gallery-header">
        <h1 class="gallery-title">Your Favorite Recipes</h1>
    </div>

    <?php if(empty($favorites)) { ?>
        <div class="no-recipes">
            <p>You haven't favorited any recipes yet. Browse our recipes and click the heart icon to add them to your favorites!</p>
            <a href="<?php echo url_for('/recipes/index.php'); ?>" class="btn btn-primary">Browse Recipes</a>
        </div>
    <?php } else { ?>
        <div class="recipe-grid">
            <?php foreach($favorites as $recipe) { 
                $style = $recipe->style();
                $diet = $recipe->diet();
                $type = $recipe->type();
                $rating = $recipe->get_average_rating();
                $total_time = TimeUtility::format_time($recipe->prep_time + $recipe->cook_time);
            ?>
                <div class="recipe-card">
                    <div class="recipe-image-container">
                        <?php if($recipe->img_file_path) { ?>
                            <img src="<?php echo url_for('/assets/uploads/recipes/' . basename($recipe->img_file_path)); ?>" 
                                 alt="<?php echo h($recipe->alt_text); ?>" 
                                 class="recipe-image">
                        <?php } else { ?>
                            <img src="<?php echo url_for('/assets/images/recipe-placeholder.png'); ?>" 
                                 alt="Recipe placeholder image" 
                                 class="recipe-image">
                        <?php } ?>
                        <button class="favorite-btn favorited" data-recipe-id="<?php echo $recipe->recipe_id; ?>">
                            <i class="fas fa-heart"></i>
                        </button>
                    </div>
                    <div class="recipe-content">
                        <a href="<?php echo url_for('/recipes/show.php?id=' . h(u($recipe->recipe_id)) . '&ref=favorites'); ?>" class="recipe-link">
                            <h2 class="recipe-title"><?php echo h($recipe->title); ?></h2>
                        </a>
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
                                <span class="recipe-attribute"><?php echo h($style->name); ?></span>
                            <?php } ?>
                            <?php if($diet) { ?>
                                <span class="recipe-attribute"><?php echo h($diet->name); ?></span>
                            <?php } ?>
                            <?php if($type) { ?>
                                <span class="recipe-attribute"><?php echo h($type->name); ?></span>
                            <?php } ?>
                        </div>
                        <p class="recipe-description"><?php echo h($recipe->description); ?></p>
                    </div>
                </div>
            <?php } ?>
        </div>
    <?php } ?>
</div>

<?php
// Prepare data for JavaScript
$favoritesData = array_map(function($recipe) use ($session) {
    return [
        'recipe_id' => $recipe->recipe_id,
        'user_id' => $session->get_user_id()
    ];
}, $favorites);

$userData = [
    'isLoggedIn' => $session->is_logged_in(),
    'userId' => $session->get_user_id(),
    'apiBaseUrl' => 'http://localhost:3000'
];
?>

<script>
    // Initialize data for JavaScript
    window.initialUserData = <?php echo json_encode($userData); ?>;
    window.initialFavoritesData = <?php echo json_encode($favoritesData); ?>;
</script>

<script type="module" src="<?php echo url_for('/assets/js/pages/favorites.js'); ?>"></script>

<?php include(SHARED_PATH . '/footer.php'); ?>
