<?php
// Authentication functions

/**
 * Requires login for accessing protected resources
 * For web requests: redirects to login page
 * For API requests: returns JSON error
 * @return void
 */
function require_login() {
    global $session;
    if (!$session->is_logged_in()) {
        // Check if this is an API request
        if (strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'error' => 'You must be logged in to access this resource'
            ]);
            exit;
        }
        // Web request - redirect to login
        redirect_to(url_for('/auth/login.php'));
    }
}

/**
 * Requires admin privileges for accessing protected pages
 * Redirects to home page if user is not an admin or super admin
 * @param bool $require_super_admin If true, requires super admin privileges
 * @return void
 */
function require_admin($require_super_admin = false) {
    global $session;
    if ($require_super_admin) {
        if (!$session->is_super_admin()) {
            $session->message('Access denied. Super Admin privileges required.');
            redirect_to(url_for('/index.php'));
        }
    } else {
        if (!$session->is_admin() && !$session->is_super_admin()) {
            $session->message('Access denied. Admin privileges required.');
            redirect_to(url_for('/index.php'));
        }
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
