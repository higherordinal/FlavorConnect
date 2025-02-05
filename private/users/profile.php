<?php
require_once('../core/initialize.php');
require_login();

$user_id = $session->get_user_id();
$user = User::find_by_id($user_id);

if(!$user) {
    $session->message('Error: User not found.');
    redirect_to(url_for('/index.php'));
}

// Get all recipes created by this user
$recipes = Recipe::find_by_user_id($user_id);

$page_title = 'My Profile';
$page_style = 'profile';
include(SHARED_PATH . '/member_header.php');
?>

<link rel="stylesheet" href="<?php echo url_for('/assets/css/pages/profile.css'); ?>">

<div class="profile-container">
    <div class="profile-header">
        <h1>My Profile</h1>
        <div class="profile-stats">
            <div class="stat">
                <i class="fas fa-utensils"></i>
                <span><?php echo count($recipes); ?> Recipes Created</span>
            </div>
        </div>
    </div>

    <div class="profile-content">
        <section class="my-recipes">
            <div class="section-header">
                <h2>My Recipes</h2>
                <a href="<?php echo private_url_for('/recipes/new.php'); ?>" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create New Recipe
                </a>
            </div>

            <?php if(empty($recipes)) { ?>
                <div class="empty-state">
                    <i class="fas fa-book-open"></i>
                    <p>You haven't created any recipes yet.</p>
                    <a href="<?php echo private_url_for('/recipes/new.php'); ?>" class="btn btn-primary">Create Your First Recipe</a>
                </div>
            <?php } else { ?>
                <div class="recipe-grid">
                    <?php foreach($recipes as $recipe) { ?>
                        <div class="recipe-card">
                            <div class="recipe-image">
                                <img src="<?php echo url_for('/assets/images/recipe-placeholder.jpg'); ?>" 
                                     alt="<?php echo h($recipe->alt_text ?? $recipe->title); ?>">
                            </div>
                            <div class="recipe-content">
                                <h3><?php echo h($recipe->title); ?></h3>
                                <div class="recipe-meta">
                                    <span>
                                        <i class="fas fa-clock"></i>
                                        <?php echo h(TimeUtility::format_time($recipe->prep_time + $recipe->cook_time)); ?>
                                    </span>
                                    <span>
                                        <i class="fas fa-utensils"></i>
                                        <?php echo h($recipe->style() ? $recipe->style()->name : 'Any Style'); ?>
                                    </span>
                                </div>
                                <div class="recipe-actions">
                                    <a href="<?php echo url_for('/recipes/show.php?id=' . h(u($recipe->recipe_id))); ?>" 
                                       class="btn btn-secondary">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <a href="<?php echo private_url_for('/recipes/edit.php?id=' . h(u($recipe->recipe_id))); ?>" 
                                       class="btn btn-primary">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="<?php echo private_url_for('/recipes/delete.php?id=' . h(u($recipe->recipe_id))); ?>" 
                                       class="btn btn-danger"
                                       onclick="return confirm('Are you sure you want to delete this recipe?');">
                                        <i class="fas fa-trash"></i> Delete
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
