<?php
require_once('../../core/initialize.php');
require_login();

// Only admins can toggle user status
if(!$session->is_admin()) {
    $session->message('Access denied. Admin privileges required.');
    redirect_to(url_for('/admin/users/index.php'));
}

// Get user ID from URL
if(!isset($_GET['user_id'])) {
    $session->message('No user ID was provided.');
    redirect_to(url_for('/admin/users/index.php'));
}

// Find user by ID
$user = User::find_by_id($_GET['user_id']);
if(!$user) {
    $session->message('User not found.', 'error');
    redirect_to(url_for('/admin/users/index.php'));
}

// Regular admins cannot modify super admin or other admin accounts
if(!$session->is_super_admin() && ($user->is_admin() || $user->is_super_admin())) {
    $session->message('You do not have permission to modify admin accounts.', 'error');
    redirect_to(url_for('/admin/users/index.php'));
}

// Check if this would deactivate the last active admin
$new_status = !$user->is_active;
if(($user->is_admin() || $user->is_super_admin()) && !$new_status) {
    if(!has_remaining_active_admin($user->user_id, false)) {
        $session->message('Cannot deactivate the last active admin user.', 'error');
        redirect_to(url_for('/admin/users/index.php'));
    }
}

// Toggle user status
$user->is_active = $new_status;
$result = $user->save();

if($result) {
    $status = $user->is_active ? 'activated' : 'deactivated';
    $session->message("User {$user->username} has been {$status}.");
    redirect_to(url_for('/admin/users/index.php'));
} else {
    $session->message('Failed to update user status.', 'error');
    redirect_to(url_for('/admin/users/index.php'));
}
?>
