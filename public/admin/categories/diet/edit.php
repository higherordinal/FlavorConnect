<?php
require_once('../../../../private/core/initialize.php');
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
    $diet->merge_attributes($args);
    $result = $diet->save();
    if($result === true) {
        $session->message('Diet updated successfully.');
        redirect_to(url_for('/admin/categories/index.php'));
    } else {
        // Show errors
    }
}

$page_title = 'Edit Diet';
$page_style = 'admin';
$component_styles = ['forms'];

// Scripts
$utility_scripts = ['common', 'form-validation', 'back-link'];
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
                ['label' => 'Edit Diet']
            ],
            $back_link_data['text']
        ); 
        ?>

        <div class="admin-header">
            <h1>Edit Diet</h1>
        </div>
        
        <?php echo display_session_message(); ?>
        <?php echo display_errors($diet->errors); ?>
        
        <form action="<?php echo url_for('/admin/categories/diet/edit.php?id=' . h(u($id))); ?>" method="post" class="form">
            <div class="form-group">
                <label for="diet_name">Diet Name</label>
                <input type="text" id="diet_name" name="diet[name]" value="<?php echo h($diet->name); ?>" class="form-control" required>
            </div>
            
            <div class="form-buttons">
                <button type="submit" class="action save">Update Diet</button>
                <a href="<?php echo url_for('/admin/categories/index.php'); ?>" class="action cancel">Cancel</a>
            </div>
        </form>
    </div>
</main>

<?php include(SHARED_PATH . '/footer.php'); ?>
