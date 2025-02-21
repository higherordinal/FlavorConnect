<?php
require_once('../core/initialize.php');
require_once(PRIVATE_PATH . '/classes/Recipe.class.php');
require_once(PRIVATE_PATH . '/classes/RecipeAttribute.class.php');

// Require login
require_login();

$page_title = 'favorites';

// Get user's favorite recipes
$favorites = Recipe::find_favorites_by_user_id($session->get_user_id());

include(SHARED_PATH . '/member_header.php');
?>

<link rel="stylesheet" href="<?php echo url_for('/assets/css/pages/recipe-gallery.css'); ?>">

<div class="recipe-gallery">
    <div class="gallery-header">
        <h1 class="gallery-title">Your Favorited Recipes</h1>
    </div>

    <?php if(empty($favorites)) { ?>
        <div class="no-recipes">
            <p>You haven't favorited any recipes yet. Browse our recipes and click the heart icon to add them to your favorites!</p>
            <a href="<?php echo url_for('/recipes/index.php'); ?>" class="btn btn-primary">Browse Recipes</a>
        </div>
    <?php } else { ?>
        <div class="recipe-grid">
            <?php foreach($favorites as $recipe) { ?>
                <div class="recipe-card">
                    <div class="recipe-image-container">
                        <?php if($recipe->img_file_path) { ?>
                            <img src="<?php echo url_for('/assets/uploads/recipes/' . basename($recipe->img_file_path)); ?>" 
                                 alt="<?php echo h($recipe->alt_text); ?>" 
                                 class="recipe-image">
                        <?php } else { ?>
                            <img src="<?php echo url_for('/assets/images/recipe-placeholder.jpg'); ?>" 
                                 alt="Recipe placeholder image" 
                                 class="recipe-image">
                        <?php } ?>
                        <button class="favorite-btn favorited" data-recipe-id="<?php echo $recipe->recipe_id; ?>">
                            <i class="fas fa-heart"></i>
                        </button>
                    </div>
                    <a href="<?php echo url_for('/recipes/show.php?id=' . h(u($recipe->recipe_id)) . '&ref=favorites'); ?>" class="recipe-link">
                        <div class="recipe-info">
                            <h2 class="recipe-title"><?php echo h($recipe->title); ?></h2>
                            <p class="recipe-description"><?php echo h($recipe->description); ?></p>
                            <div class="recipe-meta">
                                <?php if($recipe->style()) { ?>
                                    <span class="recipe-tag"><?php echo h($recipe->style()->name); ?></span>
                                <?php } ?>
                                <?php if($recipe->diet()) { ?>
                                    <span class="recipe-tag"><?php echo h($recipe->diet()->name); ?></span>
                                <?php } ?>
                            </div>
                        </div>
                    </a>
                </div>
            <?php } ?>
        </div>
    <?php } ?>
</div>

<script type="module" src="<?php echo url_for('/assets/js/pages/favorites.js'); ?>"></script>

<?php include(SHARED_PATH . '/footer.php'); ?>
