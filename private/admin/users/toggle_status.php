<?php
require_once('../../../private/core/initialize.php');
require_login();

// Only admins can toggle user status
if(!$session->is_admin()) {
    $session->message('Access denied. Admin privileges required.');
    redirect_to(url_for('/private/admin/users/index.php'));
}

// Get user ID from URL
$id = $_GET['id'] ?? '';

// Find user by ID
$user = User::find_by_id($id);
if(!$user) {
    $session->message('User not found.', 'error');
    redirect_to(url_for('/private/admin/users/index.php'));
}

// Get current user
$current_user = User::find_by_id($session->get_user_id());
if(!$current_user) {
    $session->message('Current user not found.', 'error');
    redirect_to(url_for('/private/admin/users/index.php'));
}

// Regular admins cannot modify super admin or other admin accounts
if(!$session->is_super_admin() && ($user->is_admin() || $user->is_super_admin())) {
    $session->message('You do not have permission to modify admin accounts.', 'error');
    redirect_to(url_for('/private/admin/users/index.php'));
}

// Toggle user status
$user->is_active = !$user->is_active;
$result = $user->save();

if($result) {
    $status = $user->is_active ? 'activated' : 'deactivated';
    $session->message("User {$user->username} has been {$status}.");
    if($user->is_admin() || $user->is_super_admin()) {
        redirect_to(url_for('/private/admin/users/admin_management.php'));
    } else {
        redirect_to(url_for('/private/admin/users/user_management.php'));
    }
} else {
    $session->message('Failed to update user status.', 'error');
    redirect_to(url_for('/private/admin/users/index.php'));
}
?>
