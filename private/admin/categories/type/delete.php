<?php
require_once('../../../private/initialize.php');
require_login();

if(!$session->is_admin()) {
    $session->message('Access denied. Admin privileges required.');
    redirect_to(url_for('/index.php'));
}

if(!isset($_GET['id'])) {
    $session->message('No recipe type ID was provided.');
    redirect_to(url_for('/admin/categories/recipe_metadata.php'));
}
$id = $_GET['id'];
$type = RecipeType::find_by_id($id);
if($type === false) {
    $session->message('Recipe type not found.');
    redirect_to(url_for('/admin/categories/recipe_metadata.php'));
}

if(is_post_request()) {
    // Check if type is in use
    $recipe_count = Recipe::count_by_type($type->id);
    if($recipe_count > 0) {
        $session->message("Cannot delete type. It is used by {$recipe_count} recipes.", 'error');
    } else {
        if($type->delete()) {
            $session->message('The recipe type was deleted successfully.');
            redirect_to(url_for('/admin/categories/recipe_metadata.php'));
        }
    }
    redirect_to(url_for('/admin/categories/recipe_metadata.php'));
}

$page_title = 'Delete Recipe Type';
include(SHARED_PATH . '/header.php');
?>

<link rel="stylesheet" href="<?php echo url_for('/css/admin.css'); ?>">

<div class="admin delete">
    <h1>Delete Recipe Type</h1>

    <?php echo display_session_message(); ?>

    <div class="delete-confirmation">
        <p>Are you sure you want to delete the recipe type: <strong><?php echo h($type->name); ?></strong>?</p>
        
        <?php $recipe_count = Recipe::count_by_type($type->id); ?>
        <?php if($recipe_count > 0) { ?>
            <p class="warning">Warning: This type is currently used by <?php echo $recipe_count; ?> recipe(s).</p>
            <p>You cannot delete a type that is in use. Please reassign these recipes to a different type first.</p>
        <?php } else { ?>
            <p class="warning">This action cannot be undone.</p>
            
            <form action="<?php echo url_for('/admin/categories/type/delete.php?id=' . h(u($id))); ?>" method="post">
                <div class="form-buttons delete">
                    <button type="submit" class="btn btn-danger">Delete Type</button>
                    <a class="cancel" href="<?php echo url_for('/admin/categories/recipe_metadata.php'); ?>">Cancel</a>
                </div>
            </form>
        <?php } ?>
    </div>
</div>

<?php include(SHARED_PATH . '/footer.php'); ?>
