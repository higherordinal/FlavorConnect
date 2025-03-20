<?php
require_once('../../private/core/initialize.php');
require_login();

$errors = [];

if(!isset($_GET['id'])) {
    error_404();
}

$id = $_GET['id'];
$recipe = Recipe::find_by_id($id);

if(!$recipe) {
    error_404();
}

// Check if user has permission to delete this recipe
if($recipe->user_id != $session->get_user_id() && !$session->is_admin()) {
    error_403();
}

// Get referrer for back link
$ref = $_GET['ref'] ?? '';
$back_link = get_back_link('/recipes/show.php?id=' . h(u($id)));

// Handle POST request for deletion
if(is_post_request()) {
    try {
        // Delete recipe
        if($recipe->delete()) {
            $session->message('Recipe deleted successfully.');
            redirect_to(url_for('/recipes/index.php'));
        } else {
            throw new Exception("Failed to delete recipe from database.");
        }
    } catch(Exception $e) {
        $errors[] = $e->getMessage();
        error_500();
    }
}

$page_title = 'Delete Recipe';
$page_style = 'recipe-create';
include(SHARED_PATH . '/member_header.php');
?>

<main>
    <div class="recipe-form">
        <header class="page-header">
            <?php 
            echo unified_navigation(
                $back_link,
                [
                    ['url' => '/index.php', 'label' => 'Home'],
                    ['url' => '/recipes/index.php', 'label' => 'Recipes'],
                    ['url' => '/recipes/show.php?id=' . h(u($id)), 'label' => h($recipe->title)],
                    ['label' => 'Delete Recipe']
                ],
                'Back'
            ); 
            ?>
            <h1>Delete Recipe</h1>
        </header>

        <?php echo display_errors($errors); ?>
        <?php echo display_session_message(); ?>

        <div class="delete-confirmation">
            <p>Are you sure you want to delete <strong><?php echo h($recipe->title); ?></strong>?</p>
            <p class="warning"><i class="fas fa-exclamation-triangle"></i> This action cannot be undone.</p>
        </div>

        <form action="<?php echo url_for('/recipes/delete.php?id=' . h(u($id))); ?>" method="post">
            <div class="form-buttons">
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash"></i>
                    Delete Recipe
                </button>
                <a href="<?php echo $back_link; ?>" class="btn btn-secondary">
                    <i class="fas fa-times"></i>
                    Cancel
                </a>
            </div>
        </form>
    </div>
</main>

<?php include(SHARED_PATH . '/footer.php'); ?>