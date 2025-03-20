<?php
require_once('../../../../private/core/initialize.php');
require_admin();

// Create a new measurement
$measurement = new Measurement();

if(is_post_request()) {
    // Process form submission
    $args = $_POST['measurement'];
    $measurement->name = $args['name'] ?? '';
    
    if($measurement->save()) {
        $session->message('Measurement unit created successfully.');
        redirect_to(url_for('/admin/categories/index.php'));
    }
}

$page_title = 'Create Measurement Unit';
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
            <span class="breadcrumb-item active">Create Measurement Unit</span>
        </div>

        <div class="admin-header">
            <h1>Create Measurement Unit</h1>
        </div>
        
        <?php echo display_session_message(); ?>
        <?php echo display_errors($measurement->errors); ?>
        
        <form action="<?php echo url_for('/admin/categories/measurement/new.php'); ?>" method="post" class="form">
            <div class="form-group">
                <label for="measurement_name">Measurement Name</label>
                <input type="text" id="measurement_name" name="measurement[name]" value="<?php echo h($measurement->name); ?>" class="form-control" required>
            </div>
            
            <div class="form-buttons">
                <button type="submit" class="action save">Create Measurement</button>
                <a href="<?php echo url_for('/admin/categories/index.php'); ?>" class="action cancel">Cancel</a>
            </div>
        </form>
    </div>
</main>

<?php include(SHARED_PATH . '/footer.php'); ?>
