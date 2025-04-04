<?php
require_once('../../private/core/initialize.php');

require_login();

$page_title = 'User Favorites';
$page_style = 'user-favorites';

// Get user's favorite recipes
$favorites = Recipe::find_favorites_by_user_id($session->get_user_id());

include(SHARED_PATH . '/member_header.php');
?>

<link rel="stylesheet" href="<?php echo url_for('/assets/css/pages/recipe-gallery.css'); ?>">
<link rel="stylesheet" href="<?php echo url_for('/assets/css/pages/user-favorites.css'); ?>">

<div class="container">
    <?php 
    echo unified_navigation(
        '/recipes/index.php',
        [
            ['url' => '/index.php', 'label' => 'Home'],
            ['label' => 'My Favorites']
        ],
        'Back to Recipes'
    ); 
    ?>
    
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
                    <article class="recipe-card" role="article">
                        <a href="<?php echo url_for('/recipes/show.php?id=' . h(u($recipe->recipe_id)) . '&ref=favorites'); ?>" 
                           class="recipe-link"
                           role="link"
                           aria-labelledby="recipe-title-<?php echo h($recipe->recipe_id); ?>">
                            <div class="recipe-image-container">
                                <button class="favorite-btn favorited" 
                                        data-recipe-id="<?php echo h($recipe->recipe_id); ?>"
                                        aria-label="Remove from favorites">
                                    <i class="fas fa-heart"></i>
                                </button>
                                <?php if($recipe->get_image_path('thumb')) { ?>
                                    <img src="<?php echo url_for($recipe->get_image_path('thumb')); ?>" 
                                         alt="<?php echo h($recipe->alt_text); ?>" 
                                         class="recipe-image">
                                <?php } else { ?>
                                    <img src="<?php echo url_for('/assets/images/recipe-placeholder.png'); ?>" 
                                         alt="Recipe placeholder image" 
                                         class="recipe-image">
                                <?php } ?>
                            </div>
                            
                            <div class="recipe-content">
                                <h2 class="recipe-title" id="recipe-title-<?php echo h($recipe->recipe_id); ?>"><?php echo h($recipe->title); ?></h2>
                                
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
                                        <a href="<?php echo url_for('/recipes/index.php?style=' . h(u($style->id))); ?>" class="recipe-attribute" role="listitem"><?php echo h($style->name); ?></a>
                                    <?php } ?>
                                    <?php if($diet) { ?>
                                        <a href="<?php echo url_for('/recipes/index.php?diet=' . h(u($diet->id))); ?>" class="recipe-attribute" role="listitem"><?php echo h($diet->name); ?></a>
                                    <?php } ?>
                                    <?php if($type) { ?>
                                        <a href="<?php echo url_for('/recipes/index.php?type=' . h(u($type->id))); ?>" class="recipe-attribute" role="listitem"><?php echo h($type->name); ?></a>
                                    <?php } ?>
                                </div>
                            </div>

                            <div class="recipe-footer">
                                <div class="recipe-author">
                                    <?php $user = User::find_by_id($recipe->user_id); ?>
                                    <span class="author-name">By <?php echo h($user->username); ?></span>
                                </div>
                            </div>
                        </a>
                    </article>
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

    <script src="<?php echo url_for('/assets/js/pages/user-favorites.js?v=' . time()); ?>"></script>

    <?php include(SHARED_PATH . '/footer.php'); ?>
