<?php
require_once('../../../private/core/initialize.php');
require_login();

// Only admins and super admins can access this page
if(!$session->is_admin() && !$session->is_super_admin()) {
    $session->message('Access denied. Admin privileges required.');
    redirect_to(url_for('/'));
}

if(!isset($_GET['id'])) {
    $session->message('No diet ID was provided.');
    redirect_to(url_for('/admin/categories/recipe_metadata.php'));
}
$id = $_GET['id'];
$diet = RecipeDiet::find_by_id($id);
if(!$diet) {
    $session->message('Diet not found.');
    redirect_to(url_for('/admin/categories/recipe_metadata.php'));
}

if(is_post_request()) {
    // Check if diet is in use
    $recipe_count = Recipe::count_by_diet($diet->id);
    if($recipe_count > 0) {
        $session->message("Cannot delete diet. It is used by {$recipe_count} recipes.", 'error');
    } else {
        if($diet->delete()) {
            $session->message('The diet was deleted successfully.');
            redirect_to(url_for('/admin/categories/recipe_metadata.php'));
        }
    }
    redirect_to(url_for('/admin/categories/recipe_metadata.php'));
}

$page_title = 'Delete Diet Type';
include(SHARED_PATH . '/header.php');
?>

<link rel="stylesheet" href="<?php echo url_for('/css/admin.css'); ?>">

<div class="admin delete">
    <h1>Delete Diet Type</h1>

    <?php echo display_session_message(); ?>

    <div class="delete-confirmation">
        <p>Are you sure you want to delete the diet type: <strong><?php echo h($diet->name); ?></strong>?</p>
        
        <?php $recipe_count = Recipe::count_by_diet($diet->id); ?>
        <?php if($recipe_count > 0) { ?>
            <p class="warning">Warning: This diet type is currently used by <?php echo $recipe_count; ?> recipe(s).</p>
            <p>You cannot delete a diet type that is in use. Please reassign these recipes to a different diet type first.</p>
        <?php } else { ?>
            <p class="warning">This action cannot be undone.</p>
            
            <form action="<?php echo url_for('/admin/categories/diet/delete.php?id=' . h(u($id))); ?>" method="post">
                <div class="form-buttons delete">
                    <button type="submit" class="btn btn-danger">Delete Diet</button>
                    <a class="cancel" href="<?php echo url_for('/admin/categories/recipe_metadata.php'); ?>">Cancel</a>
                </div>
            </form>
        <?php } ?>
    </div>
</div>

<?php include(SHARED_PATH . '/footer.php'); ?>
