<?php

/**
 * Session class for managing user sessions and flash messages
 * Handles user authentication state and temporary messages
 */
class Session {
    /** @var int|null ID of the logged in user */
    private $user_id;
    /** @var string|null Username of the logged in user */
    private $username;
    /** @var bool Whether the user is an admin */
    private $is_admin;
    /** @var bool Whether the user is a super admin */
    private $is_super_admin;
    /** @var string|null Flash message to display */
    public $message;
    /** @var string|null Type of flash message (success, error, warning) */
    public $message_type;

    /**
     * Constructor for Session class
     * Starts the session if not already started and loads stored login data
     */
    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        error_log("Session constructor called");
        error_log("Session data in constructor: " . print_r($_SESSION, true));
        $this->check_stored_login();
        $this->check_message();
    }

    /**
     * Logs in a user and stores their data in session
     * @param User $user User object to log in
     * @return bool True if login was successful
     */
    public function login($user) {
        if($user) {
            // prevent session fixation attacks
            session_regenerate_id();
            $_SESSION['user_id'] = $user->user_id;
            $_SESSION['username'] = $user->username;
            $_SESSION['is_admin'] = ($user->user_level === 'a' || $user->user_level === 's');
            $_SESSION['is_super_admin'] = ($user->user_level === 's');
            $this->user_id = $user->user_id;
            $this->username = $user->username;
            $this->is_admin = ($user->user_level === 'a' || $user->user_level === 's');
            $this->is_super_admin = ($user->user_level === 's');
            error_log("User logged in: " . print_r($_SESSION, true));
            error_log("Session object state after login:");
            error_log("user_id: " . $this->user_id);
            error_log("username: " . $this->username);
            error_log("is_admin: " . ($this->is_admin ? "true" : "false"));
            error_log("is_super_admin: " . ($this->is_super_admin ? "true" : "false"));
        }
        return true;
    }

    /**
     * Checks if a user is currently logged in
     * @return bool True if user is logged in
     */
    public function is_logged_in() {
        $logged_in = isset($this->user_id) && isset($_SESSION['user_id']) && ($this->user_id === $_SESSION['user_id']);
        error_log("is_logged_in check: " . ($logged_in ? "true" : "false"));
        error_log("user_id property: " . (isset($this->user_id) ? $this->user_id : "not set"));
        error_log("session user_id: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : "not set"));
        return $logged_in;
    }

    /**
     * Checks if current user is an admin
     * @return bool True if user is admin or super admin
     */
    public function is_admin() {
        return isset($this->is_admin) && $this->is_admin === true;
    }

    /**
     * Checks if current user is a super admin
     * @return bool True if user is super admin
     */
    public function is_super_admin() {
        return isset($this->is_super_admin) && $this->is_super_admin === true;
    }

    /**
     * Gets the current user's ID
     * @return int|null User ID or null if not logged in
     */
    public function get_user_id() {
        return $this->user_id ?? null;
    }

    /**
     * Logs out the current user
     * @return bool True if logout was successful
     */
    public function logout() {
        unset($_SESSION['user_id']);
        unset($_SESSION['username']);
        unset($_SESSION['is_admin']);
        unset($_SESSION['is_super_admin']);
        unset($this->user_id);
        unset($this->username);
        unset($this->is_admin);
        unset($this->is_super_admin);
        error_log("User logged out: " . print_r($_SESSION, true));
        return true;
    }

    /**
     * Gets the current user's username
     * @return string Username or empty string if not logged in
     */
    public function get_username() {
        return $this->username ?? '';
    }

    /**
     * Sets or gets a flash message
     * @param string $msg Optional message to set
     * @return string|null Current message or null if none
     */
    public function message($msg="") {
        if(!empty($msg)) {
            // Set message
            $_SESSION['message'] = $msg;
            return true;
        } else {
            // Get message
            $msg = $_SESSION['message'] ?? "";
            unset($_SESSION['message']);
            return $msg;
        }
    }

    /**
     * Sets the type of flash message
     * @param string $type Message type (success, error, warning)
     */
    public function set_message_type($type) {
        $_SESSION['message_type'] = $type;
    }

    /**
     * Gets and clears the current message type
     * @return string Message type or empty string if none
     */
    public function message_type() {
        $type = $_SESSION['message_type'] ?? "";
        unset($_SESSION['message_type']);
        return $type;
    }

    /**
     * Requires login for accessing protected pages
     * Redirects to login page if user is not logged in
     * @return void
     */
    public function require_login() {
        if(!$this->is_logged_in()) {
            $this->message('You must log in first.');
            redirect_to(url_for('/public/auth/login.php'));
        }
    }

    /**
     * Requires admin privileges for accessing protected pages
     * Redirects to home page if user is not an admin
     * @return void
     */
    public function require_admin() {
        if(!$this->is_admin()) {
            redirect_to(url_for('/index.php'));
        }
    }

    /**
     * Requires super admin privileges for accessing protected pages
     * Redirects to home page if user is not a super admin
     * @return void
     */
    public function require_super_admin() {
        if(!$this->is_super_admin()) {
            redirect_to(url_for('/index.php'));
        }
    }

    /**
     * Checks and loads stored login data from session into object properties
     * Retrieves user_id, username, and is_admin status from $_SESSION
     * Logs debug information about the session state
     * @access private
     * @return void
     */
    private function check_stored_login() {
        error_log("Checking stored login");
        error_log("Session data before check: " . print_r($_SESSION, true));
        if(isset($_SESSION['user_id'])) {
            $this->user_id = $_SESSION['user_id'];
            $this->username = $_SESSION['username'];
            $this->is_admin = $_SESSION['is_admin'];
            $this->is_super_admin = $_SESSION['is_super_admin'];
            error_log("Restored session values:");
            error_log("user_id: " . $this->user_id);
            error_log("username: " . $this->username);
            error_log("is_admin: " . ($this->is_admin ? "true" : "false"));
            error_log("is_super_admin: " . ($this->is_super_admin ? "true" : "false"));
        }
    }

    /**
     * Checks for stored flash message and loads it into the session
     */
    private function check_message() {
        if(isset($_SESSION['message'])) {
            $this->message = $_SESSION['message'];
            unset($_SESSION['message']);
        } else {
            $this->message = '';
        }

        if(isset($_SESSION['message_type'])) {
            $this->message_type = $_SESSION['message_type'];
            unset($_SESSION['message_type']);
        } else {
            $this->message_type = '';
        }
    }
}
?>