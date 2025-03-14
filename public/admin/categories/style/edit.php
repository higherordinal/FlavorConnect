<?php
require_once('../../../../private/core/initialize.php');
require_login();
require_admin();

if(!isset($_GET['id'])) {
    $session->message('No style ID was provided.');
    redirect_to(url_for('/admin/categories/index.php'));
}

$id = $_GET['id'];
$style = RecipeAttribute::find_one($id, 'style');
if(!$style) {
    $session->message('Style not found.');
    redirect_to(url_for('/admin/categories/index.php'));
}

if(is_post_request()) {
    $args = $_POST['style'] ?? [];
    $style->merge_attributes($args);
    $result = $style->save();
    if($result === true) {
        $session->message('Style updated successfully.');
        redirect_to(url_for('/admin/categories/index.php'));
    } else {
        // Show errors
    }
}

$page_title = 'Edit Recipe Style';
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
            <span class="breadcrumb-item active">Edit Style</span>
        </div>

        <div class="admin-header">
            <h1>Edit Recipe Style</h1>
        </div>
        
        <?php echo display_session_message(); ?>
        <?php echo display_errors($style->errors); ?>
        
        <form action="<?php echo url_for('/admin/categories/style/edit.php?id=' . h(u($id))); ?>" method="post" class="form">
            <div class="form-group">
                <label for="style_name">Style Name</label>
                <input type="text" id="style_name" name="style[name]" value="<?php echo h($style->name); ?>" class="form-control" required>
            </div>
            
            <div class="form-buttons">
                <button type="submit" class="action save">Update Style</button>
                <a href="<?php echo url_for('/admin/categories/index.php'); ?>" class="action cancel">Cancel</a>
            </div>
        </form>
    </div>
</main>

<?php include(SHARED_PATH . '/footer.php'); ?>
