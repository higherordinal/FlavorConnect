<?php
require_once('../../../../private/core/initialize.php');
require_admin();

if(!isset($_GET['id'])) {
    $session->message('No diet ID was provided.');
    redirect_to(url_for('/admin/categories/index.php' . get_ref_parameter()));
}

$id = $_GET['id'];
$diet = RecipeAttribute::find_one($id, 'diet');
if(!$diet) {
    $session->message('Diet not found.');
    redirect_to(url_for('/admin/categories/index.php' . get_ref_parameter()));
}

if(is_post_request()) {
    $args = $_POST['diet'] ?? [];
    $diet->merge_attributes($args);
    $result = $diet->save();
    if($result === true) {
        $session->message('Diet updated successfully.');
        redirect_to(url_for('/admin/categories/index.php' . get_ref_parameter()));
    } else {
        // Show errors
    }
}

$page_title = 'Edit Diet';
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
                ['label' => 'Edit Diet']
            ]
        ); 
        ?>

        <div class="admin-header">
            <h1>Edit Diet</h1>
        </div>
        
        <?php echo display_session_message(); ?>
        <?php echo display_errors($diet->errors); ?>
        
        <form action="<?php echo url_for('/admin/categories/diet/edit.php?id=' . h(u($id)) . get_ref_parameter('ref_page')); ?>" method="post" class="form">
            <div class="form-group">
                <label for="diet_name">Diet Name</label>
                <input type="text" id="diet_name" name="diet[name]" value="<?php echo h($diet->name); ?>" class="form-control" required>
            </div>
            
            <div class="form-buttons">
                <button type="submit" class="action save">Update Diet</button>
                <a href="<?php echo url_for('/admin/categories/index.php' . get_ref_parameter()); ?>" class="action cancel">Cancel</a>
            </div>
        </form>
    </div>
</main>

<?php include(SHARED_PATH . '/footer.php'); ?>
