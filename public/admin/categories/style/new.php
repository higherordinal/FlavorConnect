<?php
require_once('../../../../private/core/initialize.php');
require_admin();

$style = new RecipeAttribute(['type' => 'style']);

if(is_post_request()) {
    $args = $_POST['style'];
    $style->name = $args['name'] ?? '';
    
    if($style->save()) {
        $session->message('Style created successfully.');
        redirect_to(url_for('/admin/categories/index.php' . get_ref_parameter('ref_page')));
    }
    // Error handling is preserved in the form display below
}

$page_title = 'Create Recipe Style';
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
                ['label' => 'Create Recipe Style']
            ]
        ); 
        ?>

        <div class="admin-header">
            <h1>Create Recipe Style</h1>
        </div>
        
        <?php echo display_session_message(); ?>
        <?php echo display_errors($style->errors); ?>
        
        <form action="<?php echo url_for('/admin/categories/style/new.php' . get_ref_parameter('ref_page')); ?>" method="post" class="form">
            <div class="form-group">
                <label for="style_name">Style Name</label>
                <input type="text" id="style_name" name="style[name]" value="<?php echo h($style->name); ?>" class="form-control" required>
            </div>
            
            <div class="form-buttons">
                <button type="submit" class="action save">Create Style</button>
                <a href="<?php echo url_for('/admin/categories/index.php' . get_ref_parameter('ref_page')); ?>" class="action cancel">Cancel</a>
            </div>
        </form>
    </div>
</main>

<?php include(SHARED_PATH . '/footer.php'); ?>
