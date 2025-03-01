<?php
require_once('../../../private/core/initialize.php');
require_login();
require_admin();

if(!isset($_GET['user_id'])) {
    $session->message('No user ID was provided.');
    redirect_to(url_for('/admin/users/index.php'));
}

$user = User::find_by_id($_GET['user_id']);
if(!$user) {
    $session->message('The user could not be found.');
    redirect_to(url_for('/admin/users/index.php'));
}

// Regular admins can't edit admin users
if(!$session->is_super_admin() && ($user->is_admin() || $user->is_super_admin())) {
    $session->message('You do not have permission to edit admin users.');
    redirect_to(url_for('/admin/users/index.php'));
}

if(is_post_request()) {
    $args = $_POST['user'];
    
    // Only allow super admins to change user levels
    if(!$session->is_super_admin()) {
        unset($args['user_level']);
    }
    
    $user->merge_attributes($args);
    $result = $user->save();
    if($result === true) {
        $session->message('The user was updated successfully.');
        redirect_to(url_for('/admin/users/index.php'));
    }
}

$page_title = 'Edit User';
$page_style = 'admin';
include(SHARED_PATH . '/member_header.php');
?>

<div class="admin-content">
    <a href="<?php echo url_for('/admin/users/index.php'); ?>" class="back-link">
        <i class="fas fa-arrow-left"></i> Back to User List
    </a>

    <div class="admin-header">
        <h1>Edit User: <span style="color: var(--color-white);"><?php echo h($user->username); ?></span></h1>
    </div>

    <?php echo display_session_message(); ?>

    <div class="form-container">
        <form action="<?php echo url_for('/admin/users/edit.php?user_id=' . h(u($user->user_id))); ?>" method="post" class="form">
            <?php include('form_fields.php'); ?>
            <div class="form-buttons">
                <button type="submit" class="action update">Update User</button>
                <a href="#" onclick="history.back(); return false;" class="cancel">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include(SHARED_PATH . '/footer.php'); ?>
