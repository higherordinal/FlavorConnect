<?php
require_once('../../../../private/core/initialize.php');
require_login();

// Only admins and super admins can access this page
if(!$session->is_admin() && !$session->is_super_admin()) {
    $session->message('Access denied. Admin privileges required.');
    redirect_to(url_for('/'));
}

if(is_post_request()) {
    $args = $_POST['style'] ?? [];
    $style = new RecipeStyle($args);
    if($style->save()) {
        $session->message('Style created successfully.');
        redirect_to(url_for('/admin/categories/'));
    }
} else {
    $style = new RecipeStyle;
}

$page_title = 'Create Style';
include(SHARED_PATH . '/header.php');
?>

<div class="content">
    <div class="actions">
        <h1>Create Style</h1>
        
        <?php echo display_errors($style->errors); ?>
        
        <form action="<?php echo url_for('/admin/categories/style/new.php'); ?>" method="post">
            <?php include('form_fields.php'); ?>
            
            <div class="form-group mt-4">
                <button type="submit" class="btn btn-primary">Create Style</button>
                <a class="btn btn-secondary" href="<?php echo url_for('/admin/categories/'); ?>">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include(SHARED_PATH . '/footer.php'); ?>
