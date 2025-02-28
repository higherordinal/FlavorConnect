<?php
require_once('../../../../private/core/initialize.php');
require_login();
require_admin();

if(!isset($_GET['id'])) {
    $session->message('No diet ID was provided.');
    redirect_to(private_url_for('/admin/categories/index.php'));
}
$id = $_GET['id'];
$diet = RecipeAttribute::find_by_type('diet');
$diet = array_filter($diet, function($d) use ($id) { return $d->id == $id; });
$diet = !empty($diet) ? array_values($diet)[0] : null;

if(!$diet) {
    $session->message('Diet not found.');
    redirect_to(private_url_for('/admin/categories/index.php'));
}

// Check if diet is in use
$recipes_count = Recipe::count_by_diet($id);
if($recipes_count > 0) {
    $session->message("Cannot delete diet. It is used by {$recipes_count} recipe(s).", 'error');
    redirect_to(private_url_for('/admin/categories/index.php'));
}

if(is_post_request()) {
    // Delete diet
    if($diet->delete()) {
        $session->message('Diet deleted successfully.');
        redirect_to(private_url_for('/admin/categories/index.php'));
    } else {
        $session->message('Failed to delete diet.', 'error');
        redirect_to(private_url_for('/admin/categories/index.php'));
    }
} else {
    // Show confirmation page
    $page_title = 'Delete Diet';
    $page_style = 'admin';
    include(SHARED_PATH . '/member_header.php');
}
?>

<main class="main-content">
    <div class="admin-content">
        <div class="breadcrumbs">
            <a href="<?php echo url_for('/'); ?>" class="breadcrumb-item">Home</a>
            <span class="breadcrumb-separator">/</span>
            <a href="<?php echo private_url_for('/admin/index.php'); ?>" class="breadcrumb-item">Admin</a>
            <span class="breadcrumb-separator">/</span>
            <a href="<?php echo private_url_for('/admin/categories/index.php'); ?>" class="breadcrumb-item">Recipe Metadata</a>
            <span class="breadcrumb-separator">/</span>
            <span class="breadcrumb-item active">Delete Diet</span>
        </div>

        <div class="admin-header">
            <h1>Delete Diet</h1>
        </div>

        <?php echo display_session_message(); ?>

        <div class="delete-confirmation">
            <p>Are you sure you want to delete the diet "<?php echo h($diet->name); ?>"?</p>
            <form action="<?php echo private_url_for('/admin/categories/diet/delete.php?id=' . h(u($id))); ?>" method="post">
                <div class="form-buttons delete">
                    <input type="submit" value="Delete Diet">
                    <a href="<?php echo private_url_for('/admin/categories/index.php'); ?>" class="cancel">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</main>

<?php include(SHARED_PATH . '/footer.php'); ?>
