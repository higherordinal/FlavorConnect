<?php
require_once('../../../../private/core/initialize.php');
require_admin();

if(!isset($_GET['id'])) {
    $session->message('No style ID was provided.');
    redirect_to(url_for('/admin/categories/index.php' . get_ref_parameter()));
}

$id = $_GET['id'];
$style = RecipeAttribute::find_one($id, 'style');
if(!$style) {
    $session->message('Style not found.');
    redirect_to(url_for('/admin/categories/index.php' . get_ref_parameter()));
}

if(is_post_request()) {
    // Check if style is being used by any recipes
    $recipe_count = RecipeAttribute::count_by_attribute_id($id, 'style');
    if($recipe_count > 0) {
        $session->message("Cannot delete style. It is used by {$recipe_count} recipes.", 'error');
    } else {
        if($style->delete()) {
            $session->message('Style deleted successfully.');
        }
    }
    redirect_to(url_for('/admin/categories/index.php' . get_ref_parameter()));
}

$page_title = 'Delete Recipe Style';
$page_style = 'admin';
$component_styles = ['forms'];

// Scripts
// Note: 'common' and 'back-link' are already loaded in member_header.php
$utility_scripts = [];
$component_scripts = ['recipe-favorite'];
$page_scripts = ['admin'];
include(SHARED_PATH . '/member_header.php');
?>

<main class="main-content">
    <div class="admin-content">
        <?php 
        // Use unified_navigation directly, which will call get_back_link internally
        echo unified_navigation(
            '/admin/categories/index.php',
            [
                ['url' => '/index.php', 'label' => 'Home'],
                ['url' => '/admin/index.php', 'label' => 'Admin'],
                ['url' => '/admin/categories/index.php', 'label' => 'Recipe Metadata'],
                ['label' => 'Delete Style']
            ]
        ); 
        ?>

        <div class="admin-header">
            <h1>Delete Recipe Style</h1>
        </div>
        
        <?php echo display_session_message(); ?>
        
        <div class="confirmation-box">
            <p>Are you sure you want to delete this style?</p>
            <p class="item-name"><?php echo h($style->name); ?></p>
            
            <?php
            // Check if style is being used by any recipes
            $recipe_count = RecipeAttribute::count_by_attribute_id($id, 'style');
            if($recipe_count > 0) { ?>
                <div class="alert warning">
                    <p>Cannot delete this style. It is currently used by <?php echo $recipe_count; ?> recipe(s).</p>
                    <p>Please reassign these recipes to a different style before deleting.</p>
                </div>
                
                <div class="form-buttons">
                    <a href="<?php echo url_for('/admin/categories/index.php' . get_ref_parameter()); ?>" class="action cancel">Back to Metadata</a>
                </div>
            <?php } else { ?>
                <p class="warning-text">This action cannot be undone.</p>
                
                <form action="<?php echo url_for('/admin/categories/style/delete.php?id=' . h(u($id)) . get_ref_parameter('ref_page')); ?>" method="post" class="form">
                    <div class="form-buttons">
                        <button type="submit" class="action delete">Delete Style</button>
                        <a href="<?php echo url_for('/admin/categories/index.php' . get_ref_parameter()); ?>" class="action cancel">Cancel</a>
                    </div>
                </form>
            <?php } ?>
        </div>
    </div>
</main>

<?php include(SHARED_PATH . '/footer.php'); ?>
