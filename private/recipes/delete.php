<?php
require_once('../core/initialize.php');
require_once(PRIVATE_PATH . '/core/validation_functions.php');
require_login();

if(!isset($_GET['id'])) {
    redirect_to(url_for('/recipes/index.php'));
}

$id = $_GET['id'];
$recipe = Recipe::find_by_id($id);

if(!$recipe) {
    redirect_to(url_for('/recipes/index.php'));
}

// Check if user has permission to delete this recipe
if($recipe->user_id != $session->get_user_id() && !$session->is_admin()) {
    $session->message('You do not have permission to delete this recipe.', 'error');
    redirect_to(url_for('/recipes/show.php?id=' . $id));
}

$page_title = 'recipe-form';
include(SHARED_PATH . '/member_header.php');

?>

<main>
    <div class="recipe-form">
        <header class="page-header">
            <h1>Delete Recipe</h1>
        </header>

        <div class="delete-confirmation">
            <p>Are you sure you want to delete <strong><?php echo h($recipe->title); ?></strong>?</p>
            <p class="warning">This action cannot be undone.</p>
        </div>

        <form action="<?php echo url_for('/private/recipes/delete.php?id=' . h(u($id))); ?>" method="post">
            <div class="form-buttons">
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash"></i>
                    Delete Recipe
                </button>
                <a href="<?php echo url_for('/recipes/show.php?id=' . h(u($id))); ?>" class="btn btn-secondary">
                    <i class="fas fa-times"></i>
                    Cancel
                </a>
            </div>
        </form>
    </div>
</main>

<?php 
if(is_post_request()) {
    // Delete recipe image if exists
    if(!empty($recipe->img_file_path)) {
        $image_path = PUBLIC_PATH . '/assets/uploads/recipes/' . $recipe->img_file_path;
        if(file_exists($image_path)) {
            unlink($image_path);
        }
    }

    // Delete recipe
    if($recipe->delete()) {
        $session->message('Recipe deleted successfully.');
        redirect_to(url_for('/recipes/index.php'));
    } else {
        $session->message('Failed to delete recipe.', 'error');
    }
}

include(SHARED_PATH . '/footer.php'); ?>