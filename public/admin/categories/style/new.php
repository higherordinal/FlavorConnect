<?php
require_once('../../../../private/core/initialize.php');
require_login();
require_admin();

$style = new RecipeAttribute(['type' => 'style']);

if(is_post_request()) {
    $args = $_POST['style'];
    $style->name = $args['name'] ?? '';
    
    // Debug
    error_log("Style new - Name: " . $style->name);
    
    if($style->save()) {
        $session->message('Style created successfully.');
        redirect_to(url_for('/admin/categories/index.php'));
    } else {
        error_log("Style save failed. Errors: " . print_r($style->errors, true));
    }
}

$page_title = 'Create Recipe Style';
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
            <span class="breadcrumb-item active">Create Recipe Style</span>
        </div>

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
