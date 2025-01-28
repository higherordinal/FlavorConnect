<?php
// Authentication functions

/**
 * Requires login for accessing protected pages
 * Redirects to login page if user is not logged in
 * @return void
 */
function require_login() {
    global $session;
    if(!$session->is_logged_in()) {
        $session->message('You must log in first.');
        redirect_to(url_for('/public/auth/login.php'));
    }
}

/**
 * Requires admin privileges for accessing protected pages
 * Redirects to home page if user is not an admin
 * @return void
 */
function require_admin() {
    global $session;
    if(!$session->is_admin()) {
        redirect_to(url_for('/index.php'));
    }
}

/**
 * Logs in a user
 * @param object $user The user object to log in
 * @return void
 */
function log_in_user($user) {
    global $session;
    $session->login($user);
    redirect_to('/index.php');
}

/**
 * Logs out the current user
 * @return void
 */
function log_out_user() {
    global $session;
    $session->logout();
    redirect_to('/index.php');
}

/**
 * Checks if the current user is logged in
 * @return bool True if user is logged in
 */
function is_logged_in() {
    global $session;
    return $session->is_logged_in();
}

/**
 * Checks if the current user is an admin
 * @return bool True if user is an admin
 */
function is_admin() {
    global $session;
    return $session->is_admin();
}
?>
