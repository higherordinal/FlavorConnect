<?php
require_once('../../core/initialize.php');
require_login();

if(!$session->is_admin()) {
    $session->message('Access denied. Admin privileges required.');
    redirect_to(url_for('/index.php'));
}

$user = new User();
if(is_post_request()) {
    $args = $_POST['user'];
    $user->merge_attributes($args);
    $result = $user->save();
    if($result === true) {
        $session->message('The user was created successfully.');
        redirect_to(url_for('/admin/users/index.php'));
    }
}

$page_title = 'Create User';
$page_style = 'admin';
include(SHARED_PATH . '/member_header.php');
?>

<div class="admin-content">
    <a href="<?php echo private_url_for('/admin/users/index.php'); ?>" class="back-link">&laquo; Back to User List</a>

    <div class="admin-header">
        <h1>Create New User</h1>
    </div>

    <?php echo display_session_message(); ?>

    <div class="form-container">
        <form action="<?php echo private_url_for('/admin/users/new.php'); ?>" method="post" class="form">
            <?php include('form_fields.php'); ?>
            <div class="form-buttons">
                <button type="submit" class="action create">Create User</button>
                <a href="#" onclick="history.back(); return false;" class="cancel">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include(SHARED_PATH . '/footer.php'); ?>
