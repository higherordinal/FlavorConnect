<?php
require_once('../../../../private/core/initialize.php');
require_login();
require_admin();

if(!isset($_GET['id'])) {
    $session->message('No style ID was provided.');
    redirect_to(url_for('/admin/categories/'));
}

$id = $_GET['id'];
$style = RecipeStyle::find_by_id($id);
if(!$style) {
    $session->message('Style not found.');
    redirect_to(url_for('/admin/categories/'));
}

if(is_post_request()) {
    $args = $_POST['style'] ?? [];
    $style->merge_attributes($args);
    if($style->save()) {
        $session->message('Style updated successfully.');
        redirect_to(url_for('/admin/categories/'));
    }
}

$page_title = 'Edit Style';
include(SHARED_PATH . '/header.php');
?>

<div class="content">
    <div class="actions">
        <h1>Edit Style</h1>
        
        <?php echo display_errors($style->errors); ?>
        
        <form action="<?php echo url_for('/admin/categories/style/edit.php?id=' . h(u($id))); ?>" method="post">
            <?php include('form_fields.php'); ?>
            
            <div class="form-group mt-4">
                <button type="submit" class="btn btn-primary">Update Style</button>
                <a class="btn btn-secondary" href="<?php echo url_for('/admin/categories/'); ?>">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include(SHARED_PATH . '/footer.php'); ?>
