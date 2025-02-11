<?php
require_once('../../../private/initialize.php');
require_login();

// Only admins and super admins can access this page
if(!$session->is_admin() && !$session->is_super_admin()) {
    $session->message('Access denied. Admin privileges required.');
    redirect_to(url_for('/index.php'));
}

if(!isset($_GET['id'])) {
    $session->message('No user ID was provided.');
    redirect_to(url_for('/admin/users/index.php'));
}

$user = User::find_by_id($_GET['id']);
if(!$user) {
    $session->message('The user could not be found.');
    redirect_to(url_for('/admin/users/index.php'));
}

// Regular admins can't delete admin users
if(!$session->is_super_admin() && $user->user_level !== 'u') {
    $session->message('You do not have permission to delete admin users.');
    redirect_to(url_for('/admin/users/index.php'));
}

// Can't delete super admin users
if($user->user_level === 's') {
    $session->message('Super admin users cannot be deleted.');
    redirect_to(url_for('/admin/users/index.php'));
}

if(is_post_request()) {
    // Delete user's favorites and recipes first
    $favorites = UserFavorite::find_by_user_id($user->user_id);
    foreach($favorites as $favorite) {
        $favorite->delete();
    }
    
    $recipes = Recipe::find_by_user_id($user->user_id);
    foreach($recipes as $recipe) {
        $recipe->delete();
    }
    
    // Delete the user
    if($user->delete()) {
        $session->message('The user was deleted successfully.');
    } else {
        $session->message('Failed to delete the user.');
    }
    redirect_to(url_for('/admin/users/index.php'));
}

$page_title = 'Delete User';
$page_style = 'admin';
include(SHARED_PATH . '/header.php');
?>

<div class="admin-content">
    <div class="admin-header">
        <h1>Delete User: <?php echo h($user->username); ?></h1>
    </div>

    <?php echo display_session_message(); ?>

    <div class="delete-confirmation">
        <p>Are you sure you want to delete this user?</p>
        <p class="warning">Warning: This action cannot be undone. All of the user's recipes and favorites will also be deleted.</p>
        
        <div class="user-info">
            <p><strong>Username:</strong> <?php echo h($user->username); ?></p>
            <p><strong>Email:</strong> <?php echo h($user->email); ?></p>
            <p><strong>Status:</strong> <?php echo $user->is_active ? 'Active' : 'Inactive'; ?></p>
        </div>

        <form action="<?php echo url_for('/admin/users/delete.php?id=' . h(u($user->user_id))); ?>" method="post">
            <div class="form-buttons">
                <button type="submit" class="btn btn-danger">Delete User</button>
                <a href="<?php echo url_for('/admin/users/index.php'); ?>" class="btn">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include(SHARED_PATH . '/footer.php'); ?>
