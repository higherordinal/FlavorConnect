<?php
require_once('../../../private/core/initialize.php');
require_login();

if(!$session->is_admin()) {
    $session->message('Access denied. Admin privileges required.');
    redirect_to(url_for('/index.php'));
}

if(is_post_request()) {
    $args = $_POST['measurement'];
    $measurement = new Measurement($args);
    if($measurement->save()) {
        $session->message('Measurement created successfully.');
        redirect_to(url_for('/admin/categories/recipe_metadata.php'));
    }
} else {
    $measurement = new Measurement;
}

$page_title = 'Create Measurement';
include(SHARED_PATH . '/header.php');
?>

<div class="container py-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <h1><?php echo h($page_title); ?></h1>
            
            <?php echo display_errors($measurement->errors); ?>
            
            <form action="<?php echo url_for('/admin/categories/measurement/new.php'); ?>" method="post">
                <?php include('form_fields.php'); ?>
                
                <div class="form-group mt-4">
                    <button type="submit" class="btn btn-primary">Create Measurement</button>
                    <a class="btn btn-secondary" href="<?php echo url_for('/admin/categories/recipe_metadata.php'); ?>">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include(SHARED_PATH . '/footer.php'); ?>
