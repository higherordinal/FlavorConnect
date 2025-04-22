<?php
require_once('../../../../private/core/initialize.php');
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
    $args = $_POST['measurement'] ?? [];
    $measurement->name = $args['name'] ?? '';
    
    if($measurement->save()) {
        $session->message('Measurement unit updated successfully.');
        redirect_to(url_for('/admin/categories/index.php'));
    }
}

$page_title = 'Edit Measurement Unit';
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
        // Use get_back_link to determine the appropriate back link
        $back_link_data = get_back_link('/admin/categories/index.php');
        
        echo unified_navigation(
            $back_link_data['url'],
            [
                ['url' => '/index.php', 'label' => 'Home'],
                ['url' => '/admin/index.php', 'label' => 'Admin'],
                ['url' => '/admin/categories/index.php', 'label' => 'Recipe Metadata'],
                ['label' => 'Edit Measurement Unit']
            ],
            $back_link_data['text']
        ); 
        ?>

        <div class="admin-header">
            <h1>Edit Measurement Unit</h1>
        </div>
        
        <?php echo display_session_message(); ?>
        <?php echo display_errors($measurement->errors); ?>
        
        <form action="<?php echo url_for('/admin/categories/measurement/edit.php?id=' . h(u($id))); ?>" method="post" class="form">
            <div class="form-group">
                <label for="measurement_name">Measurement Name</label>
                <input type="text" id="measurement_name" name="measurement[name]" value="<?php echo h($measurement->name); ?>" class="form-control" required>
            </div>
            
            <div class="form-buttons">
                <button type="submit" class="action save">Update Measurement</button>
                <a href="<?php echo url_for('/admin/categories/index.php'); ?>" class="action cancel">Cancel</a>
            </div>
        </form>
    </div>
</main>

<?php include(SHARED_PATH . '/footer.php'); ?>
