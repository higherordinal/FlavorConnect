<?php
require_once('../../../private/core/initialize.php');
require_admin();

// Initialize user with default values
$user = new User();
$user->user_level = 'u'; // Default to regular user
$user->is_active = 1;    // Default to active

if(is_post_request()) {
    $args = $_POST['user'];
    $user->merge_attributes($args);
    $result = $user->save();
    if($result === true) {
        $session->message('The user was created successfully.');
        // Redirect to the users index page
        redirect_to(url_for('/admin/users/index.php'));
    }
}

$page_title = 'Create User';
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
    echo unified_navigation(
        '/admin/users/index.php',
        [
            ['url' => '/index.php', 'label' => 'Home'],
            ['url' => '/admin/index.php', 'label' => 'Admin'],
            ['url' => '/admin/users/index.php', 'label' => 'User Management'],
            ['label' => 'Create User']
        ]
    ); 
    ?>

    <div class="admin-header">
        <h1>Create New User</h1>
    </div>

    <?php echo display_session_message(); ?>

    <div class="form-container">
        <form action="<?php echo url_for('/admin/users/new.php'); ?>" method="post" class="form">
            <?php include('form_fields.php'); ?>
            <div class="form-buttons">
                <button type="submit" class="action create">Create User</button>
                <a href="<?php echo url_for('/admin/users/index.php' . get_ref_parameter()); ?>" class="action cancel">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include(SHARED_PATH . '/footer.php'); ?>
