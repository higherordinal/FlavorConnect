<?php
require_once('../../../../private/core/initialize.php');
require_admin();

// Create a new diet attribute
$diet = new RecipeAttribute(['type' => 'diet']);

if(is_post_request()) {
    // Process form submission
    $args = $_POST['diet'];
    $diet->name = $args['name'] ?? '';
    
    if($diet->save()) {
        $session->message('Diet created successfully.');
        redirect_to(url_for('/admin/categories/index.php' . get_ref_parameter('ref_page')));
    }
}

$page_title = 'Create Diet';
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
                ['label' => 'Create Diet']
            ]
        ); 
        ?>

        <div class="admin-header">
            <h1>Create Diet</h1>
        </div>
        
        <?php echo display_session_message(); ?>
        <?php echo display_errors($diet->errors); ?>
        
        <form action="<?php echo url_for('/admin/categories/diet/new.php' . get_ref_parameter('ref_page')); ?>" method="post" class="form">
            <div class="form-group">
                <label for="diet_name">Diet Name</label>
                <input type="text" id="diet_name" name="diet[name]" value="<?php echo h($diet->name); ?>" class="form-control" required>
            </div>
            
            <div class="form-buttons">
                <button type="submit" class="action save">Create Diet</button>
                <a href="<?php echo url_for('/admin/categories/index.php' . get_ref_parameter('ref_page')); ?>" class="action cancel">Cancel</a>
            </div>
        </form>
    </div>
</main>

<?php include(SHARED_PATH . '/footer.php'); ?>
