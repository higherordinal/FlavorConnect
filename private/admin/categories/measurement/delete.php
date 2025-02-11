<?php
require_once('../../../private/initialize.php');
require_login();

if(!$session->is_admin()) {
    $session->message('Access denied. Admin privileges required.');
    redirect_to(url_for('/index.php'));
}

if(!isset($_GET['id'])) {
    $session->message('No measurement ID was provided.');
    redirect_to(url_for('/admin/categories/recipe_metadata.php'));
}
$id = $_GET['id'];
$measurement = Measurement::find_by_id($id);
if($measurement === false) {
    $session->message('Measurement not found.');
    redirect_to(url_for('/admin/categories/recipe_metadata.php'));
}

if(is_post_request()) {
    // Check if measurement is in use
    $recipe_count = Recipe::count_by_measurement($measurement->id);
    if($recipe_count > 0) {
        $session->message("Cannot delete measurement. It is used by {$recipe_count} recipes.", 'error');
    } else {
        if($measurement->delete()) {
            $session->message('The measurement was deleted successfully.');
            redirect_to(url_for('/admin/categories/recipe_metadata.php'));
        }
    }
    redirect_to(url_for('/admin/categories/recipe_metadata.php'));
}

$page_title = 'Delete Measurement Unit';
include(SHARED_PATH . '/header.php');
?>

<link rel="stylesheet" href="<?php echo url_for('/css/admin.css'); ?>">

<div class="admin delete">
    <h1>Delete Measurement Unit</h1>

    <?php echo display_session_message(); ?>

    <div class="delete-confirmation">
        <p>Are you sure you want to delete the measurement unit: <strong><?php echo h($measurement->name); ?></strong> (<?php echo h($measurement->abbreviation); ?>)?</p>
        
        <?php $recipe_count = Recipe::count_by_measurement($measurement->id); ?>
        <?php if($recipe_count > 0) { ?>
            <p class="warning">Warning: This measurement unit is currently used by <?php echo $recipe_count; ?> recipe(s).</p>
            <p>You cannot delete a measurement unit that is in use. Please update these recipes to use a different measurement first.</p>
        <?php } else { ?>
            <p class="warning">This action cannot be undone.</p>
            
            <form action="<?php echo url_for('/admin/categories/measurement/delete.php?id=' . h(u($id))); ?>" method="post">
                <div class="form-buttons delete">
                    <button type="submit" class="btn btn-danger">Delete Measurement</button>
                    <a class="cancel" href="<?php echo url_for('/admin/categories/recipe_metadata.php'); ?>">Cancel</a>
                </div>
            </form>
        <?php } ?>
    </div>
</div>

<?php include(SHARED_PATH . '/footer.php'); ?>
