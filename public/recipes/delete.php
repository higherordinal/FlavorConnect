<?php
require_once('../../private/core/initialize.php');
require_login();

$errors = [];

// Validate recipe access with ownership check
$recipe = validate_recipe_access(null, true);
if(!$recipe) {
    exit; // validate_recipe_access already handled the error
}

$id = $recipe->recipe_id;

// Get referrer for back link
$ref = $_GET['ref'] ?? '';
$back_link_data = get_back_link('/recipes/show.php?id=' . h(u($id)));
$back_link = $back_link_data['url'];
$back_text = $back_link_data['text'];

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
$page_style = 'recipe-crud';
$component_styles = ['forms'];

// Scripts
$utility_scripts = ['common', 'form-validation', 'back-link'];
$component_scripts = ['member-header'];
include(SHARED_PATH . '/member_header.php');
?>

<main>
    <div class="container">
        <?php 
        echo unified_navigation(
            $back_link,
            [
                ['url' => '/index.php', 'label' => 'Home'],
                ['url' => '/recipes/index.php', 'label' => 'Recipes'],
                ['url' => '/recipes/show.php?id=' . h(u($id)), 'label' => h($recipe->title)],
                ['label' => 'Delete Recipe']
            ],
            $back_text
        ); 
        ?>
    </div>
    
    <div class="recipe-form">
        <header class="page-header with-recipe-banner" id="recipe-header" <?php 
            if($recipe->img_file_path) {
                echo 'style="background-image: url(\'' . url_for($recipe->get_image_path('banner')) . '\');"';
            }
        ?>>
            <h1>Delete Recipe: <?php echo h($recipe->title); ?></h1>
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