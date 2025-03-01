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

// Regular admins can't delete admin users
if(!$session->is_super_admin() && $user->user_level !== 'u') {
    $session->message('You do not have permission to delete admin users.');
    redirect_to(url_for('/admin/users/index.php'));
}

// Validate the deletion
$errors = validate_user_deletion($_GET['user_id']);
if(!empty($errors)) {
    $session->message($errors[0]);
    redirect_to(url_for('/admin/users/index.php'));
}

if(is_post_request()) {
    // Delete user's favorites directly with SQL
    $db = DatabaseObject::get_database();
    $sql = "DELETE FROM user_favorite WHERE user_id = ?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("i", $user->user_id);
    $stmt->execute();
    
    // Delete user's comments
    $sql = "DELETE FROM recipe_comment WHERE user_id = ?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("i", $user->user_id);
    $stmt->execute();
    
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

include(SHARED_PATH . '/member_header.php');
?>

<div class="admin-content">
    <div class="admin-header">
        <h1>Delete User: <?php echo h($user->username); ?></h1>
    </div>

    <?php echo display_session_message(); ?>

    <div class="delete-confirmation">
        <p>Are you sure you want to delete this user?</p>
        <p>This action cannot be undone.</p>
        
        <div class="user-details">
            <p><strong>Username:</strong> <?php echo h($user->username); ?></p>
            <p><strong>Email:</strong> <?php echo h($user->email); ?></p>
            <p><strong>Full Name:</strong> <?php echo h($user->full_name()); ?></p>
            <?php 
            $level = '';
            switch($user->user_level) {
                case 's':
                    $level = 'Super Admin';
                    break;
                case 'a':
                    $level = 'Admin';
                    break;
                default:
                    $level = 'User';
            }
            ?>
            <p><strong>User Level:</strong> <?php echo h($level); ?></p>
            <p><strong>Status:</strong> <?php echo $user->is_active ? 'Active' : 'Inactive'; ?></p>
        </div>

        <form action="<?php echo url_for('/admin/users/delete.php?user_id=' . h(u($user->user_id))); ?>" method="post">
            <div class="form-buttons">
                <button type="submit" class="action delete">Delete User</button>
                <a href="#" onclick="history.back(); return false;" class="cancel">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include(SHARED_PATH . '/footer.php'); ?>
