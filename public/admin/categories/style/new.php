<?php
require_once('../../../../private/core/initialize.php');
require_login();
require_admin();

$style = new RecipeAttribute(['type' => 'style']);

if(is_post_request()) {
    $args = $_POST['style'];
    $style->name = $args['name'] ?? '';
    
    if($style->save()) {
        $session->message('Style created successfully.');
        redirect_to(url_for('/admin/categories/index.php'));
    }
}

$page_title = 'Create Recipe Style';
$page_style = 'admin';
include(SHARED_PATH . '/member_header.php');
?>

<div class="content">
    <div class="actions">
        <h1>Create Recipe Style</h1>
        
        <?php echo display_session_message(); ?>
        <?php echo display_errors($style->errors); ?>
        
        <form action="<?php echo url_for('/admin/categories/style/new.php'); ?>" method="post">
            <div class="form-group">
                <label for="style_name">Style Name</label>
                <input type="text" id="style_name" name="style[name]" value="<?php echo h($style->name); ?>" class="form-control" required>
            </div>
            
            <div class="form-group mt-4">
                <button type="submit" class="btn btn-primary">Create Style</button>
                <a class="btn btn-secondary" href="<?php echo url_for('/admin/categories/'); ?>">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include(SHARED_PATH . '/footer.php'); ?>
