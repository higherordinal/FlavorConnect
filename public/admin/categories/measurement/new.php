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
        // Redirect to the categories index page
        redirect_to(url_for('/admin/categories/index.php'));
    }
}

$page_title = 'Create Measurement Unit';
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
                ['label' => 'Create Measurement Unit']
            ]
        ); 
        ?>

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
                <a href="<?php echo url_for('/admin/categories/index.php' . get_ref_parameter('ref_page')); ?>" class="action cancel">Cancel</a>
            </div>
        </form>
    </div>
</main>

<?php include(SHARED_PATH . '/footer.php'); ?>
