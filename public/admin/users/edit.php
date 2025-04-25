<?php
require_once('../../../private/core/initialize.php');
require_admin();

if(!isset($_GET['user_id'])) {
    $session->message('No user ID was provided.');
    redirect_to(url_for('/admin/users/index.php' . get_ref_parameter()));
}

$user = User::find_by_id($_GET['user_id']);
if(!$user) {
    $session->message('The user could not be found.');
    redirect_to(url_for('/admin/users/index.php' . get_ref_parameter()));
}

// Regular admins can't edit admin users
if(!$session->is_super_admin() && ($user->is_admin() || $user->is_super_admin())) {
    $session->message('You do not have permission to edit admin users.');
    redirect_to(url_for('/admin/users/index.php' . get_ref_parameter()));
}

if(is_post_request()) {
    $args = $_POST['user'];
    
    // Only allow super admins to change user levels
    if(!$session->is_super_admin()) {
        unset($args['user_level']);
    }
    
    // Check if this would deactivate the last active admin
    $original_is_active = $user->is_active;
    $new_is_active = isset($args['is_active']) ? (bool)$args['is_active'] : $original_is_active;
    
    if(($user->is_admin() || $user->is_super_admin()) && $original_is_active && !$new_is_active) {
        if(!has_remaining_active_admin($user->user_id, false)) {
            $session->message('Cannot deactivate the last active admin user.', 'error');
            redirect_to(url_for('/admin/users/index.php' . get_ref_parameter()));
        }
    }
    
    $user->merge_attributes($args);
    $result = $user->save();
    if($result === true) {
        $session->message('The user was updated successfully.');
        redirect_to(url_for('/admin/users/index.php' . get_ref_parameter()));
    }
}

$page_title = 'Edit User';
$page_style = 'admin';
$component_styles = ['forms'];

// Scripts
// Note: 'common' and 'back-link' are already loaded in member_header.php
$utility_scripts = ['form-validation'];
$component_scripts = [];
$page_scripts = ['admin'];
include(SHARED_PATH . '/member_header.php');
?>

<div class="admin-content">
    <?php 
    // Use unified_navigation directly, which will call get_back_link internally
    // Use unified_navigation directly, which will call get_back_link internally
    
    echo unified_navigation(
        '/admin/users/index.php',
        [
            ['url' => '/index.php', 'label' => 'Home'],
            ['url' => '/admin/index.php', 'label' => 'Admin'],
            ['url' => '/admin/users/index.php', 'label' => 'User Management'],
            ['label' => 'Edit User']
        ]
    ); 
    ?>

    <div class="admin-header">
        <h1>Edit User: <span class="username-highlight"><?php echo h($user->username); ?></span></h1>
    </div>

    <?php echo display_session_message(); ?>

    <div class="form-container">
        <form action="<?php echo url_for('/admin/users/edit.php?user_id=' . h(u($user->user_id))); ?>" method="post" class="form">
            <?php include('form_fields.php'); ?>
            <div class="form-buttons">
                <button type="submit" class="action update">Update User</button>
                <a href="<?php echo url_for('/admin/users/index.php' . get_ref_parameter()); ?>" class="action cancel">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include(SHARED_PATH . '/footer.php'); ?>
