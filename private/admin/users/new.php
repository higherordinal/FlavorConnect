<?php
require_once('../../../private/initialize.php');
require_login();

// Only admins and super admins can access this page
if(!$session->is_admin() && !$session->is_super_admin()) {
    $session->message('Access denied. Admin privileges required.');
    redirect_to(url_for('/index.php'));
}

$page_title = 'Create New User';
$page_style = 'admin';

$user = new User();

if(is_post_request()) {
    $args = $_POST['user'];
    
    // Only super admins can create admin users
    if(!$session->is_super_admin() && isset($args['user_level']) && $args['user_level'] !== 'u') {
        $session->message('You do not have permission to create admin users.');
        redirect_to(url_for('/admin/users/index.php'));
    }
    
    $user->merge_attributes($args);
    if($user->save()) {
        $session->message('The user was created successfully.');
        redirect_to(url_for('/admin/users/index.php'));
    }
}

include(SHARED_PATH . '/header.php');
?>

<div class="admin-content">
    <div class="admin-header">
        <h1>Create New User</h1>
    </div>

    <?php echo display_session_message(); ?>

    <div class="form-container">
        <form action="<?php echo url_for('/admin/users/new.php'); ?>" method="post">
            <?php include('form_fields.php'); ?>
            
            <div class="form-buttons">
                <button type="submit" class="btn btn-primary">Create User</button>
                <a href="<?php echo url_for('/admin/users/index.php'); ?>" class="btn">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include(SHARED_PATH . '/footer.php'); ?>
