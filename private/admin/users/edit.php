<?php
require_once('../../../private/initialize.php');
require_login();

// Only admins and super admins can access this page
if(!$session->is_admin() && !$session->is_super_admin()) {
    $session->message('Access denied. Admin privileges required.');
    redirect_to(url_for('/index.php'));
}

$page_title = 'Edit User';
$page_style = 'admin';

if(!isset($_GET['id'])) {
    $session->message('No user ID was provided.');
    redirect_to(url_for('/admin/users/index.php'));
}

$user = User::find_by_id($_GET['id']);
if(!$user) {
    $session->message('The user could not be found.');
    redirect_to(url_for('/admin/users/index.php'));
}

// Regular admins can't edit admin users
if(!$session->is_super_admin() && $user->user_level !== 'u') {
    $session->message('You do not have permission to edit admin users.');
    redirect_to(url_for('/admin/users/index.php'));
}

if(is_post_request()) {
    $args = $_POST['user'];
    
    // Prevent changing user level unless super admin
    if(!$session->is_super_admin()) {
        unset($args['user_level']);
    }
    
    // Don't update password if it's empty
    if(empty($args['password'])) {
        unset($args['password']);
    }
    
    $user->merge_attributes($args);
    if($user->save()) {
        $session->message('The user was updated successfully.');
        redirect_to(url_for('/admin/users/index.php'));
    }
}

include(SHARED_PATH . '/header.php');
?>

<div class="admin-content">
    <div class="admin-header">
        <h1>Edit User: <?php echo h($user->username); ?></h1>
    </div>

    <?php echo display_session_message(); ?>

    <div class="form-container">
        <form action="<?php echo url_for('/admin/users/edit.php?id=' . h(u($user->user_id))); ?>" method="post">
            <?php include('form_fields.php'); ?>
            
            <div class="form-buttons">
                <button type="submit" class="btn btn-primary">Update User</button>
                <a href="<?php echo url_for('/admin/users/index.php'); ?>" class="btn">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include(SHARED_PATH . '/footer.php'); ?>
