<?php
require_once('../../private/core/initialize.php');

// Perform logout
$session->logout();

// Clear remember me cookies if they exist
if(isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/', '', true, true);
    setcookie('remember_user', '', time() - 3600, '/', '', true, true);
}

// Set success message
$session->message('You have been successfully logged out.');

// Redirect to login page
redirect_to(url_for('/auth/login.php'));
?>
