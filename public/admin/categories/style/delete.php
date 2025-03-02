<?php
require_once('../../../../private/core/initialize.php');
require_login();
require_admin();

if(!isset($_GET['id'])) {
    $session->message('No style ID was provided.');
    redirect_to(url_for('/admin/categories/index.php'));
}

$id = $_GET['id'];
$style = RecipeAttribute::find_one($id, 'style');
if(!$style) {
    $session->message('Style not found.');
    redirect_to(url_for('/admin/categories/index.php'));
}

if(is_post_request()) {
    // Check if style is being used by any recipes
    $recipe_count = Recipe::count_by_attribute_id($id, 'style');
    if($recipe_count > 0) {
        $session->message("Cannot delete style. It is used by {$recipe_count} recipes.", 'error');
    } else {
        if($style->delete()) {
            $session->message('Style deleted successfully.');
        }
    }
    redirect_to(url_for('/admin/categories/index.php'));
}

$page_title = 'Delete Recipe Style';
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
            <span class="breadcrumb-item active">Delete Style</span>
        </div>

        <div class="admin-header">
            <h1>Delete Recipe Style</h1>
        </div>
        
        <?php echo display_session_message(); ?>
        
        <div class="confirmation-box">
            <p>Are you sure you want to delete this style?</p>
            <p class="item-name"><?php echo h($style->name); ?></p>
            
            <?php
            // Check if style is being used by any recipes
            $recipe_count = Recipe::count_by_attribute_id($id, 'style');
            if($recipe_count > 0) { ?>
                <div class="alert warning">
                    <p>Cannot delete this style. It is currently used by <?php echo $recipe_count; ?> recipe(s).</p>
                    <p>Please reassign these recipes to a different style before deleting.</p>
                </div>
                
                <div class="form-buttons">
                    <a href="<?php echo url_for('/admin/categories/index.php'); ?>" class="action cancel">Back to Metadata</a>
                </div>
            <?php } else { ?>
                <p class="warning-text">This action cannot be undone.</p>
                
                <form action="<?php echo url_for('/admin/categories/style/delete.php?id=' . h(u($id))); ?>" method="post" class="form">
                    <div class="form-buttons">
                        <button type="submit" class="action delete">Delete Style</button>
                        <a href="<?php echo url_for('/admin/categories/index.php'); ?>" class="action cancel">Cancel</a>
                    </div>
                </form>
            <?php } ?>
        </div>
    </div>
</main>

<?php include(SHARED_PATH . '/footer.php'); ?>
