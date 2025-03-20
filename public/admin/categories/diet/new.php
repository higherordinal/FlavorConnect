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
        redirect_to(url_for('/admin/categories/index.php'));
    }
}

$page_title = 'Create Diet';
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
            <span class="breadcrumb-item active">Create Diet</span>
        </div>

        <div class="admin-header">
            <h1>Create Diet</h1>
        </div>
        
        <?php echo display_session_message(); ?>
        <?php echo display_errors($diet->errors); ?>
        
        <form action="<?php echo url_for('/admin/categories/diet/new.php'); ?>" method="post" class="form">
            <div class="form-group">
                <label for="diet_name">Diet Name</label>
                <input type="text" id="diet_name" name="diet[name]" value="<?php echo h($diet->name); ?>" class="form-control" required>
            </div>
            
            <div class="form-buttons">
                <button type="submit" class="action save">Create Diet</button>
                <a href="<?php echo url_for('/admin/categories/index.php'); ?>" class="action cancel">Cancel</a>
            </div>
        </form>
    </div>
</main>

<?php include(SHARED_PATH . '/footer.php'); ?>
