<?php
require_once('../../../../private/core/initialize.php');
require_admin();

if(!isset($_GET['id'])) {
    $session->message('No measurement ID was provided.');
    // Redirect to the categories index page
    redirect_to(url_for('/admin/categories/index.php'));
}

$id = $_GET['id'];
$measurement = Measurement::find_by_id($id);
if(!$measurement) {
    $session->message('Measurement not found.');
    // Redirect to the categories index page
    redirect_to(url_for('/admin/categories/index.php'));
}

if(is_post_request()) {
    if($measurement->delete()) {
        $session->message('Measurement unit deleted successfully.');
    }
    // Redirect to the categories index page
    redirect_to(url_for('/admin/categories/index.php'));
}

$page_title = 'Delete Measurement Unit';
$page_style = 'admin';
$component_styles = ['forms'];

// Scripts
// Note: 'common' and 'back-link' are already loaded in member_header.php
$utility_scripts = [];
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
                ['label' => 'Delete Measurement Unit']
            ]
        ); 
        ?>

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
                    <a href="<?php echo url_for('/admin/categories/index.php' . get_ref_parameter()); ?>" class="action cancel">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</main>

<?php include(SHARED_PATH . '/footer.php'); ?>
