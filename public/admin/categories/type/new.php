<?php
require_once('../../../../private/core/initialize.php');
require_login();
require_admin();

// Create a new type attribute
$type = new RecipeAttribute(['type' => 'type']);

if(is_post_request()) {
    // Process form submission
    $args = $_POST['type'];
    $type->name = $args['name'] ?? '';
    
    if($type->save()) {
        $session->message('Recipe type created successfully.');
        redirect_to(url_for('/admin/categories/index.php'));
    }
}

$page_title = 'Create Recipe Type';
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
                ['label' => 'Create Recipe Type']
            ],
            'Back to Recipe Metadata'
        ); 
        ?>

        <div class="admin-header">
            <h1>Create Recipe Type</h1>
        </div>
        
        <?php echo display_session_message(); ?>
        <?php echo display_errors($type->errors); ?>
        
        <form action="<?php echo url_for('/admin/categories/type/new.php'); ?>" method="post" class="form">
            <div class="form-group">
                <label for="type_name">Type Name</label>
                <input type="text" id="type_name" name="type[name]" value="<?php echo h($type->name); ?>" class="form-control" required>
            </div>
            
            <div class="form-buttons">
                <button type="submit" class="action save">Create Type</button>
                <a href="<?php echo url_for('/admin/categories/index.php'); ?>" class="action cancel">Cancel</a>
            </div>
        </form>
    </div>
</main>

<?php include(SHARED_PATH . '/footer.php'); ?>
