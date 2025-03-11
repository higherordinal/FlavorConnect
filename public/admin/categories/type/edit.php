<?php
require_once('../../../../private/core/initialize.php');
require_login();
require_admin();

if(!isset($_GET['id'])) {
    $session->message('No recipe type ID was provided.');
    redirect_to(url_for('/admin/categories/index.php'));
}

$id = $_GET['id'];
$type = RecipeAttribute::find_one($id, 'type');
if(!$type) {
    $session->message('Recipe type not found.');
    redirect_to(url_for('/admin/categories/index.php'));
}

if(is_post_request()) {
    $args = $_POST['type'] ?? [];
    $type->merge_attributes($args);
    $result = $type->save();
    if($result === true) {
        $session->message('Type updated successfully.');
        redirect_to(url_for('/admin/categories/index.php'));
    } else {
        // Show errors
    }
}

$page_title = 'Edit Recipe Type';
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
            <span class="breadcrumb-item active">Edit Recipe Type</span>
        </div>

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
                <a href="<?php echo url_for('/admin/categories/index.php'); ?>" class="action cancel">Cancel</a>
            </div>
        </form>
    </div>
</main>

<?php include(SHARED_PATH . '/footer.php'); ?>
