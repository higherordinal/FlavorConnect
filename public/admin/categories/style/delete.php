<?php
require_once('../../../../private/core/initialize.php');
require_login();
require_admin();

if(!isset($_GET['id'])) {
    $session->message('No style ID was provided.');
    redirect_to(url_for('/admin/categories/'));
}

$id = $_GET['id'];
$style = RecipeStyle::find_by_id($id);
if(!$style) {
    $session->message('Style not found.');
    redirect_to(url_for('/admin/categories/'));
}

if(is_post_request()) {
    // Check if style is being used by any recipes
    $recipe_count = Recipe::count_by_style_id($id);
    if($recipe_count > 0) {
        $session->message("Cannot delete style. It is used by {$recipe_count} recipes.", 'error');
    } else {
        if($style->delete()) {
            $session->message('Style deleted successfully.');
            redirect_to(url_for('/admin/categories/'));
        }
    }
    redirect_to(url_for('/admin/categories/'));
}

$page_title = 'Delete Style';
include(SHARED_PATH . '/header.php');
?>

<div class="content">
    <div class="actions">
        <h1>Delete Style</h1>
        
        <p>Are you sure you want to delete this style?</p>
        <p class="item"><?php echo h($style->name); ?></p>
        
        <?php
        // Check if style is being used by any recipes
        $recipe_count = Recipe::count_by_style_id($id);
        if($recipe_count > 0) { ?>
            <div class="alert alert-warning">
                <p>Cannot delete this style. It is currently used by <?php echo $recipe_count; ?> recipe(s).</p>
                <p>Please reassign these recipes to a different style before deleting.</p>
                <a class="btn btn-secondary" href="<?php echo url_for('/admin/categories/'); ?>">Back to Categories</a>
            </div>
        <?php } else { ?>
            <p class="warning">This action cannot be undone.</p>
            
            <form action="<?php echo url_for('/admin/categories/style/delete.php?id=' . h(u($id))); ?>" method="post">
                <div class="form-buttons delete">
                    <button type="submit" class="btn btn-danger">Delete Style</button>
                    <a class="cancel" href="<?php echo url_for('/admin/categories/'); ?>">Cancel</a>
                </div>
            </form>
        <?php } ?>
    </div>
</div>

<?php include(SHARED_PATH . '/footer.php'); ?>
