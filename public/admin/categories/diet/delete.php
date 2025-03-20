<?php
require_once('../../../../private/core/initialize.php');
require_admin();

if(!isset($_GET['id'])) {
    $session->message('No diet ID was provided.');
    redirect_to(url_for('/admin/categories/index.php'));
}

$id = $_GET['id'];
$diet = RecipeAttribute::find_one($id, 'diet');
if(!$diet) {
    $session->message('Diet not found.');
    redirect_to(url_for('/admin/categories/index.php'));
}

if(is_post_request()) {
    // Check if diet is being used by any recipes
    $recipe_count = RecipeAttribute::count_by_attribute_id($id, 'diet');
    if($recipe_count > 0) {
        $session->message("Cannot delete diet. It is used by {$recipe_count} recipes.", 'error');
    } else {
        if($diet->delete()) {
            $session->message('Diet deleted successfully.');
        }
    }
    redirect_to(url_for('/admin/categories/index.php'));
}

$page_title = 'Delete Diet';
$page_style = 'admin';
include(SHARED_PATH . '/member_header.php');
?>

<main class="main-content">
    <div class="admin-content">
        <a href="<?php echo url_for('/admin/categories/index.php'); ?>" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Recipe Metadata
        </a>

        <div class="breadcrumbs">
            <a href="<?php echo url_for('/'); ?>" class="breadcrumb-item">Home</a>
            <span class="breadcrumb-separator">/</span>
            <a href="<?php echo url_for('/admin/index.php'); ?>" class="breadcrumb-item">Admin</a>
            <span class="breadcrumb-separator">/</span>
            <a href="<?php echo url_for('/admin/categories/index.php'); ?>" class="breadcrumb-item">Recipe Metadata</a>
            <span class="breadcrumb-separator">/</span>
            <span class="breadcrumb-item active">Delete Diet</span>
        </div>

        <div class="admin-header">
            <h1>Delete Diet</h1>
        </div>
        
        <?php echo display_session_message(); ?>
        
        <div class="confirmation-box">
            <p>Are you sure you want to delete this diet?</p>
            <p class="item-name"><?php echo h($diet->name); ?></p>
            
            <?php
            // Check if diet is being used by any recipes
            $recipe_count = RecipeAttribute::count_by_attribute_id($id, 'diet');
            if($recipe_count > 0) { ?>
                <div class="alert warning">
                    <p>Cannot delete this diet. It is currently used by <?php echo $recipe_count; ?> recipe(s).</p>
                    <p>Please reassign these recipes to a different diet before deleting.</p>
                </div>
                
                <div class="form-buttons">
                    <a href="<?php echo url_for('/admin/categories/index.php'); ?>" class="action cancel">Back to Metadata</a>
                </div>
            <?php } else { ?>
                <p class="warning-text">This action cannot be undone.</p>
                
                <form action="<?php echo url_for('/admin/categories/diet/delete.php?id=' . h(u($id))); ?>" method="post" class="form">
                    <div class="form-buttons">
                        <button type="submit" class="action delete">Delete Diet</button>
                        <a href="<?php echo url_for('/admin/categories/index.php'); ?>" class="action cancel">Cancel</a>
                    </div>
                </form>
            <?php } ?>
        </div>
    </div>
</main>

<?php include(SHARED_PATH . '/footer.php'); ?>
