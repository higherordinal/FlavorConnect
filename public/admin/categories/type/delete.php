<?php
require_once('../../../../private/core/initialize.php');
require_admin();

if(!isset($_GET['id'])) {
    $session->message('No recipe type ID was provided.');
    redirect_to(url_for('/admin/categories/index.php'));
}

$id = $_GET['id'];
$type = RecipeAttribute::find_one($id, 'type');
if(!$type) {
    $session->message('Recipe type not found.');
    redirect_to(url_for('/admin/categories/index.php'));
}

if(is_post_request()) {
    // Check if type is being used by any recipes
    $recipe_count = RecipeAttribute::count_by_attribute_id($id, 'type');
    if($recipe_count > 0) {
        $session->message("Cannot delete type. It is used by {$recipe_count} recipes.", 'error');
    } else {
        if($type->delete()) {
            $session->message('Recipe type deleted successfully.');
        }
    }
    redirect_to(url_for('/admin/categories/index.php'));
}

$page_title = 'Delete Recipe Type';
$page_style = 'admin';
include(SHARED_PATH . '/member_header.php');
?>

<main class="main-content">
    <div class="admin-content">
        <?php 
        echo unified_navigation(
            '/admin/categories/index.php',
            [
                ['url' => '/index.php', 'label' => 'Home'],
                ['url' => '/admin/index.php', 'label' => 'Admin'],
                ['url' => '/admin/categories/index.php', 'label' => 'Recipe Metadata'],
                ['label' => 'Delete Recipe Type']
            ],
            'Back to Recipe Metadata'
        ); 
        ?>
        
        <div class="admin-header">
            <h1>Delete Recipe Type</h1>
        </div>
        
        <?php echo display_session_message(); ?>
        
        <div class="confirmation-box">
            <p>Are you sure you want to delete this recipe type?</p>
            <p class="item-name"><?php echo h($type->name); ?></p>
            
            <?php
            // Check if type is being used by any recipes
            $recipe_count = RecipeAttribute::count_by_attribute_id($id, 'type');
            if($recipe_count > 0) { ?>
                <div class="alert warning">
                    <p>Cannot delete this recipe type. It is currently used by <?php echo $recipe_count; ?> recipe(s).</p>
                    <p>Please reassign these recipes to a different type before deleting.</p>
                </div>
                
                <div class="form-buttons">
                    <a href="<?php echo url_for('/admin/categories/index.php'); ?>" class="action cancel">Back to Metadata</a>
                </div>
            <?php } else { ?>
                <p class="warning-text">This action cannot be undone.</p>
                
                <form action="<?php echo url_for('/admin/categories/type/delete.php?id=' . h(u($id))); ?>" method="post" class="form">
                    <div class="form-buttons">
                        <button type="submit" class="action delete">Delete Type</button>
                        <a href="<?php echo url_for('/admin/categories/index.php'); ?>" class="action cancel">Cancel</a>
                    </div>
                </form>
            <?php } ?>
        </div>
    </div>
</main>

<?php include(SHARED_PATH . '/footer.php'); ?>
