<?php
require_once('../../../../private/core/initialize.php');
require_admin();

if(!isset($_GET['id'])) {
    $session->message('No style ID was provided.');
    redirect_to(url_for('/admin/categories/index.php' . get_ref_parameter()));
}

$id = $_GET['id'];
$style = RecipeAttribute::find_one($id, 'style');
if(!$style) {
    $session->message('Style not found.');
    redirect_to(url_for('/admin/categories/index.php' . get_ref_parameter()));
}

if(is_post_request()) {
    $args = $_POST['style'] ?? [];
    $style->merge_attributes($args);
    $result = $style->save();
    if($result === true) {
        $session->message('Style updated successfully.');
        redirect_to(url_for('/admin/categories/index.php' . get_ref_parameter()));
    } else {
        // Show errors
    }
}

$page_title = 'Edit Recipe Style';
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
                ['label' => 'Edit Style']
            ],
            $back_link_data['text']
        ); 
        ?>

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
                <a href="<?php echo url_for('/admin/categories/index.php' . get_ref_parameter()); ?>" class="action cancel">Cancel</a>
            </div>
        </form>
    </div>
</main>

<?php include(SHARED_PATH . '/footer.php'); ?>
