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
    /** @var bool Whether the user has admin privileges (cached from User) */
    private $is_admin;
    /** @var bool Whether the user has super admin privileges (cached from User) */
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
            $_SESSION['is_admin'] = $user->is_admin();
            $_SESSION['is_super_admin'] = $user->is_super_admin();
            $this->user_id = $user->user_id;
            $this->username = $user->username;
            $this->is_admin = $user->is_admin();
            $this->is_super_admin = $user->is_super_admin();
        }
        return true;
    }

    /**
     * Checks if a user is currently logged in
     * @return bool True if user is logged in
     */
    public function is_logged_in() {
        $logged_in = isset($this->user_id) && isset($_SESSION['user_id']) && ($this->user_id === $_SESSION['user_id']);
        return $logged_in;
    }

    /**
     * Gets the cached admin status for the current session
     * This is a performance optimization to avoid database lookups
     * For authoritative admin check, use User::is_admin() instead
     * @return bool True if user has admin privileges in current session
     */
    public function is_admin() {
        return isset($this->is_admin) && $this->is_admin === true;
    }

    /**
     * Gets the cached super admin status for the current session
     * This is a performance optimization to avoid database lookups
     * For authoritative super admin check, use User::is_super_admin() instead
     * @return bool True if user has super admin privileges in current session
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
        return true;
    }

    /**
     * Gets or generates a CSRF token for the current session
     * @return string The CSRF token
     */
    public function get_csrf_token() {
        if(!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Sets a message to be displayed to the user
     * @param string $msg Message to display
     * @param string $type Type of message (success, error, warning)
     */
    public function message($msg="", $type="success") {
        if(!empty($msg)) {
            // Then this is a "set" message
            $_SESSION['message'] = $msg;
            $_SESSION['message_type'] = $type;
        } else {
            // Then this is a "get" message
            return $this->message;
        }
    }

    /**
     * Checks and loads stored login data from session into object properties
     * Retrieves user_id, username, and admin status from $_SESSION
     */
    private function check_stored_login() {
        if(isset($_SESSION['user_id'])) {
            $this->user_id = $_SESSION['user_id'];
            $this->username = $_SESSION['username'];
            $this->is_admin = $_SESSION['is_admin'];
            $this->is_super_admin = $_SESSION['is_super_admin'];
        }
    }

    /**
     * Checks for and loads any stored message
     */
    private function check_message() {
        if(isset($_SESSION['message'])) {
            $this->message = $_SESSION['message'];
            $this->message_type = $_SESSION['message_type'];
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
        } else {
            $this->message = '';
            $this->message_type = '';
        }
    }
    
    /**
     * Gets the current session instance
     * @return Session|null The current session instance or null if not available
     */
    public static function get_instance() {
        if(isset($GLOBALS['session']) && $GLOBALS['session'] instanceof Session) {
            return $GLOBALS['session'];
        }
        return null;
    }
}
?>