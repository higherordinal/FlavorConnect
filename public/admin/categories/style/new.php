<?php
require_once('../../../../private/core/initialize.php');
require_admin();

$style = new RecipeAttribute(['type' => 'style']);

if(is_post_request()) {
    $args = $_POST['style'];
    $style->name = $args['name'] ?? '';
    
    if($style->save()) {
        $session->message('Style created successfully.');
        redirect_to(url_for('/admin/categories/index.php'));
    }
    // Error handling is preserved in the form display below
}

$page_title = 'Create Recipe Style';
$page_style = 'admin';
$component_styles = ['forms'];

// Scripts
$utility_scripts = ['common', 'form-validation', 'back-link'];
$component_scripts = ['recipe-favorite'];
$page_scripts = ['admin'];
include(SHARED_PATH . '/member_header.php');
?>

<main class="main-content">
    <div class="admin-content">
        <?php 
        // Use get_back_link to determine the appropriate back link
        $back_link_data = get_back_link('/admin/categories/index.php');
        
        echo unified_navigation(
            $back_link_data['url'],
            [
                ['url' => '/index.php', 'label' => 'Home'],
                ['url' => '/admin/index.php', 'label' => 'Admin'],
                ['url' => '/admin/categories/index.php', 'label' => 'Recipe Metadata'],
                ['label' => 'Create Recipe Style']
            ],
            $back_link_data['text']
        ); 
        ?>

        <div class="admin-header">
            <h1>Create Recipe Style</h1>
        </div>
        
        <?php echo display_session_message(); ?>
        <?php echo display_errors($style->errors); ?>
        
        <form action="<?php echo url_for('/admin/categories/style/new.php'); ?>" method="post" class="form">
            <div class="form-group">
                <label for="style_name">Style Name</label>
                <input type="text" id="style_name" name="style[name]" value="<?php echo h($style->name); ?>" class="form-control" required>
            </div>
            
            <div class="form-buttons">
                <button type="submit" class="action save">Create Style</button>
                <a href="<?php echo url_for('/admin/categories/index.php'); ?>" class="action cancel">Cancel</a>
            </div>
        </form>
    </div>
</main>

<?php include(SHARED_PATH . '/footer.php'); ?>
