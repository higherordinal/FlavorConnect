<?php
require_once('../../../private/core/initialize.php');
require_login();
require_admin();

$diet = new RecipeDiet();

if(is_post_request()) {
    $args = $_POST['diet'];
    $diet->merge_attributes($args);
    if($diet->save()) {
        $session->message('Diet type created successfully.');
        redirect_to(url_for('/admin/categories/recipe_metadata.php'));
    }
} 

$page_title = 'Create Diet Type';
include(SHARED_PATH . '/header.php');
?>

<div class="container py-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <h1><?php echo h($page_title); ?></h1>
            
            <?php echo display_errors($diet->errors); ?>
            
            <form action="<?php echo url_for('/admin/categories/diet/new.php'); ?>" method="post">
                <?php include('form_fields.php'); ?>
                
                <div class="form-group mt-4">
                    <button type="submit" class="btn btn-primary">Create Diet</button>
                    <a class="btn btn-secondary" href="<?php echo url_for('/admin/categories/recipe_metadata.php'); ?>">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include(SHARED_PATH . '/footer.php'); ?>
