<?php
require_once('../../private/core/initialize.php');

require_login();

$page_title = 'User Favorites';
$page_style = 'recipe-gallery'; // Changed to match recipes/index.php
$component_styles = ['recipe-favorite', 'pagination', 'forms'];

// Get user's favorite recipes
$favorites = Recipe::find_favorites_by_user_id($session->get_user_id());

include(SHARED_PATH . '/member_header.php');
?>

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
                    <?php 
                        // Set variables for the recipe card component
                        $ref = 'favorites';
                        
                        // Include the recipe card component
                        include('../recipes/recipe-card.php'); 
                    ?>
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
