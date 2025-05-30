<?php
require_once('../../../../private/core/initialize.php');

require_admin();

if(!isset($_GET['id'])) {
    $session->message('No recipe type ID was provided.');
    // Redirect to the categories index page
    redirect_to(url_for('/admin/categories/index.php'));
}

$id = $_GET['id'];
$type = RecipeAttribute::find_one($id, 'type');
if(!$type) {
    $session->message('Recipe type not found.');
    // Redirect to the categories index page
    redirect_to(url_for('/admin/categories/index.php'));
}

if(is_post_request()) {
    $args = $_POST['type'] ?? [];
    $type->merge_attributes($args);
    $result = $type->save();
    if($result === true) {
        $session->message('Type updated successfully.');
        // Redirect to the categories index page
    redirect_to(url_for('/admin/categories/index.php'));
    } else {
        // Show errors
    }
}

$page_title = 'Edit Recipe Type';
$page_style = 'admin';
$component_styles = ['forms'];

// Scripts
// Note: 'common' and 'back-link' are already loaded in member_header.php
$utility_scripts = ['form-validation'];
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
                ['label' => 'Edit Recipe Type']
            ]
        ); 
        ?>

        <div class="admin-header">
            <h1>Edit Recipe Type</h1>
        </div>
        
        <?php echo display_session_message(); ?>
        <?php echo display_errors($type->errors); ?>
        
        <form action="<?php echo url_for('/admin/categories/type/edit.php?id=' . h(u($id))); ?>" method="post" class="form">
            <div class="form-group">
                <label for="type_name">Type Name</label>
                <input type="text" id="type_name" name="type[name]" value="<?php echo h($type->name); ?>" class="form-control" required>
            </div>
            
            <div class="form-buttons">
                <button type="submit" class="action save">Update Type</button>
                <a href="<?php echo url_for('/admin/categories/index.php' . get_ref_parameter()); ?>" class="action cancel">Cancel</a>
            </div>
        </form>
    </div>
</main>

<?php include(SHARED_PATH . '/footer.php'); ?>
