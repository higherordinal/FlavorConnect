<?php
require_once('../core/initialize.php');
require_once(PRIVATE_PATH . '/core/validation_functions.php');
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

// Handle POST request for deletion
if(is_post_request()) {
    try {
        // Delete recipe image if exists
        if(!empty($recipe->img_file_path)) {
            $image_path = PUBLIC_PATH . '/assets/uploads/recipes/' . $recipe->img_file_path;
            if(file_exists($image_path)) {
                if(!unlink($image_path)) {
                    throw new Exception("Failed to delete recipe image.");
                }
            }
        }

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

$page_title = 'recipe-form'; // This needs to match the condition in member_header.php
include(SHARED_PATH . '/member_header.php');
?>

<main>
    <div class="recipe-form">
        <header class="page-header">
            <h1>Delete Recipe</h1>
        </header>

        <?php echo display_errors($errors); ?>
        <?php echo display_session_message(); ?>

        <div class="delete-confirmation">
            <p>Are you sure you want to delete <strong><?php echo h($recipe->title); ?></strong>?</p>
            <p class="warning">This action cannot be undone.</p>
        </div>

        <form action="<?php echo private_url_for('/recipes/delete.php?id=' . h(u($id))); ?>" method="post">
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

<?php include(SHARED_PATH . '/footer.php'); ?>