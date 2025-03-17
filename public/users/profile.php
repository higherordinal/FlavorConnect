<?php
require_once('../../private/core/initialize.php');
require_login();

$user_id = $session->get_user_id();
$user = User::find_by_id($user_id);

if(!$user) {
    $session->message('Error: User not found.');
    redirect_to(url_for('/index.php'));
}

// Get all recipes created by this user
$recipes = Recipe::find_by_user_id($user_id);

$page_title = 'User Profile';
$page_style = 'profile';
include(SHARED_PATH . '/member_header.php');
?>

<link rel="stylesheet" href="<?php echo url_for('/assets/css/pages/profile.css'); ?>">

<div class="profile-container">
    <div class="profile-header">
        <h1><?php echo h($user->username); ?></h1>
        <div class="profile-stats">
            <div class="stat">
                <i class="fas fa-utensils" aria-hidden="true"></i>
                <span><?php echo count($recipes); ?> Recipes Created</span>
            </div>
        </div>
    </div>

    <div class="profile-content">
        <section class="my-recipes">
            <div class="section-header">
                <h2>My Recipes</h2>
                <a href="<?php echo url_for('/recipes/new.php?ref=profile'); ?>" class="btn btn-primary" aria-label="Create a new recipe">
                    <i class="fas fa-plus" aria-hidden="true"></i> Create New Recipe
                </a>
            </div>

            <?php if(empty($recipes)) { ?>
                <div class="empty-state">
                    <i class="fas fa-book-open" aria-hidden="true"></i>
                    <p>You haven't created any recipes yet.</p>
                    <a href="<?php echo url_for('/recipes/new.php?ref=profile'); ?>" class="btn btn-primary" aria-label="Create your first recipe">Create Your First Recipe</a>
                </div>
            <?php } else { ?>
                <div class="recipe-grid">
                    <?php foreach($recipes as $recipe) { ?>
                        <div class="recipe-card">
                            <div class="recipe-image">
                                <?php if(!empty($recipe->img_file_path)) { ?>
                                    <img src="<?php echo url_for('/assets/uploads/recipes/' . $recipe->img_file_path); ?>" 
                                         alt="<?php echo h($recipe->alt_text ?? $recipe->title); ?>">
                                <?php } else { ?>
                                    <img src="<?php echo url_for('/assets/images/recipe-placeholder.png'); ?>" 
                                         alt="<?php echo h($recipe->alt_text ?? $recipe->title); ?>">
                                <?php } ?>
                            </div>
                            <div class="recipe-content">
                                <h3><?php echo h($recipe->title); ?></h3>
                                <div class="recipe-meta">
                                    <span>
                                        <i class="fas fa-clock" aria-hidden="true"></i>
                                        <span><?php echo h(TimeUtility::format_time($recipe->prep_time + $recipe->cook_time)); ?></span>
                                    </span>
                                    <span>
                                        <i class="fas fa-utensils" aria-hidden="true"></i>
                                        <span><?php echo h($recipe->style() ? $recipe->style()->name : 'Any Style'); ?></span>
                                    </span>
                                </div>
                                <div class="recipe-actions">
                                    <a href="<?php echo url_for('/recipes/show.php?id=' . h(u($recipe->recipe_id))); ?>" 
                                       class="btn btn-secondary" aria-label="View recipe: <?php echo h($recipe->title); ?>">
                                        <i class="fas fa-eye" aria-hidden="true"></i> View
                                    </a>
                                    <a href="<?php echo url_for('/recipes/edit.php?id=' . h(u($recipe->recipe_id)) . '&ref=profile'); ?>" 
                                       class="btn btn-primary" aria-label="Edit recipe: <?php echo h($recipe->title); ?>">
                                        <i class="fas fa-edit" aria-hidden="true"></i> Edit
                                    </a>
                                    <a href="<?php echo url_for('/recipes/delete.php?id=' . h(u($recipe->recipe_id)) . '&ref=profile'); ?>" 
                                       class="btn btn-danger"
                                       aria-label="Delete recipe: <?php echo h($recipe->title); ?>"
                                       onclick="return confirm('Are you sure you want to delete this recipe?');">
                                        <i class="fas fa-trash" aria-hidden="true"></i> Delete
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>
        </section>
    </div>
</div>

<?php include(SHARED_PATH . '/footer.php'); ?>
