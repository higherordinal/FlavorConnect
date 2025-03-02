<?php
require_once('../../../../private/core/initialize.php');
require_login();
require_admin();

if(!isset($_GET['id'])) {
    $session->message('No diet ID was provided.');
    redirect_to(url_for('/admin/categories/index.php'));
}

$id = $_GET['id'];
$diet = RecipeAttribute::find_one($id, 'diet');
if(!$diet) {
    $session->message('Diet not found.');
    redirect_to(url_for('/admin/categories/index.php'));
}

if(is_post_request()) {
    $args = $_POST['diet'] ?? [];
    $diet->name = $args['name'] ?? '';
    
    if($diet->save()) {
        $session->message('Diet restriction updated successfully.');
        redirect_to(url_for('/admin/categories/index.php'));
    }
}

$page_title = 'Edit Diet Restriction';
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
            <span class="breadcrumb-item active">Edit Diet Restriction</span>
        </div>

        <div class="admin-header">
            <h1>Edit Diet Restriction</h1>
        </div>
        
        <?php echo display_session_message(); ?>
        <?php echo display_errors($diet->errors); ?>
        
        <form action="<?php echo url_for('/admin/categories/diet/edit.php?id=' . h(u($id))); ?>" method="post" class="form">
            <div class="form-group">
                <label for="diet_name">Diet Restriction Name</label>
                <input type="text" id="diet_name" name="diet[name]" value="<?php echo h($diet->name); ?>" class="form-control" required>
            </div>
            
            <div class="form-buttons">
                <button type="submit" class="action save">Update Diet Restriction</button>
                <a href="<?php echo url_for('/admin/categories/index.php'); ?>" class="action cancel">Cancel</a>
            </div>
        </form>
    </div>
</main>

<?php include(SHARED_PATH . '/footer.php'); ?>
