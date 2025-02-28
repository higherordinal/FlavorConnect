<?php
require_once('../../../private/core/initialize.php');
require_login();
require_admin();

if(!isset($_GET['id'])) {
    $session->message('No recipe type ID was provided.');
    redirect_to(url_for('/admin/categories/recipe_metadata.php'));
}

$id = $_GET['id'] ?? '';
$type = RecipeType::find_by_id($id);
if(!$type) {
    $session->message('Recipe type not found.');
    redirect_to(url_for('/admin/categories/recipe_metadata.php'));
}

if(is_post_request()) {
    $args = $_POST['type'];
    $type->merge_attributes($args);
    if($type->save()) {
        $session->message('The recipe type was updated successfully.');
        redirect_to(url_for('/admin/categories/recipe_metadata.php'));
    }
}

$page_title = 'Edit Recipe Type';
include(SHARED_PATH . '/header.php');
?>

<div class="container py-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <h1><?php echo h($page_title); ?></h1>
            
            <?php echo display_errors($type->errors); ?>
            
            <form action="<?php echo url_for('/admin/categories/type/edit.php?id=' . h(u($id))); ?>" method="post">
                <?php include('form_fields.php'); ?>
                
                <div class="form-group mt-4">
                    <button type="submit" class="btn btn-primary">Update Type</button>
                    <a class="btn btn-secondary" href="<?php echo url_for('/admin/categories/recipe_metadata.php'); ?>">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include(SHARED_PATH . '/footer.php'); ?>
