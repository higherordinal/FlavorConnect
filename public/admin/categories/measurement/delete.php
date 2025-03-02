<?php
require_once('../../../../private/core/initialize.php');
require_login();
require_admin();

if(!isset($_GET['id'])) {
    $session->message('No measurement ID was provided.');
    redirect_to(url_for('/admin/categories/index.php'));
}

$id = $_GET['id'];
$measurement = Measurement::find_by_id($id);
if(!$measurement) {
    $session->message('Measurement not found.');
    redirect_to(url_for('/admin/categories/index.php'));
}

if(is_post_request()) {
    if($measurement->delete()) {
        $session->message('Measurement unit deleted successfully.');
    }
    redirect_to(url_for('/admin/categories/index.php'));
}

$page_title = 'Delete Measurement Unit';
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
            <span class="breadcrumb-item active">Delete Measurement Unit</span>
        </div>

        <div class="admin-header">
            <h1>Delete Measurement Unit</h1>
        </div>
        
        <?php echo display_session_message(); ?>
        
        <div class="confirmation-box">
            <p>Are you sure you want to delete this measurement unit?</p>
            <p class="item-name"><?php echo h($measurement->name); ?></p>
            
            <p class="warning-text">This action cannot be undone.</p>
            
            <form action="<?php echo url_for('/admin/categories/measurement/delete.php?id=' . h(u($id))); ?>" method="post" class="form">
                <div class="form-buttons">
                    <button type="submit" class="action delete">Delete Measurement</button>
                    <a href="<?php echo url_for('/admin/categories/index.php'); ?>" class="action cancel">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</main>

<?php include(SHARED_PATH . '/footer.php'); ?>
