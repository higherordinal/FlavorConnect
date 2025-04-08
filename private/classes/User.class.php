<?php

/**
 * User class for managing user accounts and authentication
 * Extends DatabaseObject for database operations
 * 
 * Handles user registration, authentication, profile management,
 * and role-based access control (super admin vs admin vs regular users)
 */
class User extends DatabaseObject {
    /** @var string Database table name */
    static protected $table_name = 'user_account';
    /** @var array Database columns */
    static protected $db_columns = ['user_id', 'username', 'first_name', 'last_name', 'email', 'password_hash', 'user_level', 'is_active'];
    /** @var string Primary key column */
    static protected $primary_key = 'user_id';

    /** @var int Unique identifier for the user */
    public $user_id;
    /** @var string Username for login */
    public $username;
    /** @var string User's first name */
    public $first_name;
    /** @var string User's last name */
    public $last_name;
    /** @var string User's email address */
    public $email;
    /** @var string Hashed password */
    public $password_hash;
    /** @var string User level (user/admin/super admin) */
    public $user_level;
    /** @var bool Whether user account is active */
    public $is_active;
    /** @var string Password before hashing */
    protected $password;
    /** @var string Confirm password */
    public $confirm_password;
    /** @var array Array of validation errors */
    public $errors = [];
    /** @var bool Whether password is required for validation */
    protected $password_required = true;

    /**
     * Gets the user's full name
     * @return string Full name (first + last)
     */
    public function full_name() {
        return $this->first_name . ' ' . $this->last_name;
    }

    /**
     * Constructor for User class
     * @param array $args Associative array of property values
     */
    public function __construct($args=[]) {
        $this->username = $args['username'] ?? '';
        $this->first_name = $args['first_name'] ?? '';
        $this->last_name = $args['last_name'] ?? '';
        $this->email = $args['email'] ?? '';
        $this->password = $args['password'] ?? '';
        $this->confirm_password = $args['confirm_password'] ?? '';
        $this->user_level = $args['user_level'] ?? 'u';
        $this->is_active = $args['is_active'] ?? true;
    }

    /**
     * Finds a user by their username
     * @param string $username Username to search for
     * @return User|false User object or false if not found
     */
    public static function find_by_username($username) {
        $database = static::get_database();
        $sql = "SELECT * FROM " . static::$table_name . " ";
        $sql .= "WHERE username='" . $database->real_escape_string($username) . "'";
        $obj_array = static::find_by_sql($sql);
        if(!empty($obj_array)) {
            return array_shift($obj_array);
        } else {
            return false;
        }
    }

    /**
     * Finds a user by their email
     * @param string $email Email to search for
     * @return User|false User object or false if not found
     */
    public static function find_by_email($email) {
        $database = static::get_database();
        $sql = "SELECT * FROM " . static::$table_name . " ";
        $sql .= "WHERE email='" . $database->real_escape_string($email) . "'";
        $obj_array = static::find_by_sql($sql);
        if(!empty($obj_array)) {
            return array_shift($obj_array);
        } else {
            return false;
        }
    }

    /**
     * Verifies if a password matches the stored hash
     * @param string $password Password to verify
     * @return bool True if password matches
     */
    public function verify_password($password) {
        return password_verify($password, $this->password_hash);
    }

    /**
     * Sets the hashed password from the plain text password
     */
    protected function set_hashed_password() {
        $this->password_hash = password_hash($this->password, PASSWORD_BCRYPT);
    }

    /**
     * Creates a new user in the database
     * @return bool True if creation was successful
     */
    protected function create() {
        $this->set_hashed_password();
        return parent::create();
    }

    /**
     * Updates an existing user in the database
     * @return bool True if update was successful
     */
    protected function update() {
        if($this->password != '') {
            $this->set_hashed_password();
        }
        return parent::update();
    }

    /**
     * Validates user data before saving
     * @return array Array of validation errors
     */
    protected function validate() {
        $this->errors = [];

        if(is_blank($this->username)) {
            $this->errors['username'] = "Username cannot be blank.";
        } elseif (!has_length($this->username, array('min' => 3, 'max' => 255))) {
            $this->errors['username'] = "Username must be between 3 and 255 characters.";
        } elseif (!has_no_spaces($this->username)) {
            $this->errors['username'] = "Username cannot contain spaces.";
        } elseif (!has_unique_username($this->username, $this->user_id ?? 0)) {
            $this->errors['username'] = "Username is already taken. Please choose another.";
        }

        if(is_blank($this->email)) {
            $this->errors['email'] = "Email cannot be blank.";
        } elseif (!has_length($this->email, array('max' => 255))) {
            $this->errors['email'] = "Email must be less than 255 characters.";
        } elseif (!is_valid_email($this->email)) {
            $this->errors['email'] = "Email must be a valid format.";
        } elseif (!has_unique_email($this->email, $this->user_id ?? 0)) {
            $this->errors['email'] = "Email is already taken. Please choose another.";
        }

        if(is_blank($this->first_name)) {
            $this->errors['first_name'] = "First name cannot be blank.";
        } elseif (!has_length($this->first_name, array('min' => 2, 'max' => 255))) {
            $this->errors['first_name'] = "First name must be between 2 and 255 characters.";
        }

        if(is_blank($this->last_name)) {
            $this->errors['last_name'] = "Last name cannot be blank.";
        } elseif (!has_length($this->last_name, array('min' => 2, 'max' => 255))) {
            $this->errors['last_name'] = "Last name must be between 2 and 255 characters.";
        }

        if(!isset($this->user_id)) {
            // New user requires password
            if(is_blank($this->password)) {
                $this->errors['password'] = "Password cannot be blank.";
            } elseif (!has_length($this->password, array('min' => 8))) {
                $this->errors['password'] = "Password must contain 8 or more characters.";
            } elseif (!preg_match('/[A-Z]/', $this->password)) {
                $this->errors['password'] = "Password must contain at least one uppercase letter.";
            } elseif (!preg_match('/[a-z]/', $this->password)) {
                $this->errors['password'] = "Password must contain at least one lowercase letter.";
            } elseif (!preg_match('/[0-9]/', $this->password)) {
                $this->errors['password'] = "Password must contain at least one number.";
            }

            // Validate confirm password
            if(isset($this->confirm_password) && $this->password !== $this->confirm_password) {
                $this->errors['confirm_password'] = "Password and confirm password must match.";
            }
        } else {
            // Existing user, password is optional
            if(!is_blank($this->password)) {
                if(!has_length($this->password, array('min' => 8))) {
                    $this->errors['password'] = "Password must contain 8 or more characters.";
                } elseif (!preg_match('/[A-Z]/', $this->password)) {
                    $this->errors['password'] = "Password must contain at least one uppercase letter.";
                } elseif (!preg_match('/[a-z]/', $this->password)) {
                    $this->errors['password'] = "Password must contain at least one lowercase letter.";
                } elseif (!preg_match('/[0-9]/', $this->password)) {
                    $this->errors['password'] = "Password must contain at least one number.";
                }
                
                // Validate confirm password for existing users when changing password
                if(isset($this->confirm_password) && $this->password !== $this->confirm_password) {
                    $this->errors['confirm_password'] = "Password and confirm password must match.";
                }
            }
        }

        return $this->errors;
    }

    /**
     * Checks if user is a super admin
     * @return bool True if user is super admin
     */
    public function is_super_admin() {
        return $this->user_level === 's';
    }

    /**
     * Checks if user is an admin or super admin
     * @return bool True if user is admin or super admin
     */
    public function is_admin() {
        return $this->user_level === 'a' || $this->user_level === 's';
    }

    /**
     * Finds all admin users (both admin and super admin)
     * @return array Array of User objects
     */
    public static function find_all_admins() {
        $database = static::get_database();
        $sql = "SELECT * FROM " . static::$table_name . " ";
        $sql .= "WHERE user_level IN ('a', 's') ";
        $sql .= "ORDER BY username ASC";
        return static::find_by_sql($sql);
    }

    /**
     * Finds all regular users (not admins or super admins)
     * @return array Array of User objects
     */
    public static function find_all_regular_users() {
        $database = static::get_database();
        $sql = "SELECT * FROM " . static::$table_name . " ";
        $sql .= "WHERE user_level = 'u' ";
        $sql .= "ORDER BY username ASC";
        return static::find_by_sql($sql);
    }

    /**
     * Counts total number of users
     * @return int Total number of users
     */
    public static function count_all() {
        $database = static::get_database();
        $sql = "SELECT COUNT(*) as count FROM " . static::$table_name;
        $result = $database->query($sql);
        $row = $result->fetch_assoc();
        return $row['count'] ?? 0;
    }
    
    /**
     * Counts users with a specific user level
     * @param string $level User level to count (u, a, s)
     * @return int Number of users with the specified level
     */
    public static function count_by_level($level) {
        $database = static::get_database();
        $sql = "SELECT COUNT(*) as count FROM " . static::$table_name;
        $sql .= " WHERE user_level='" . $database->real_escape_string($level) . "'";
        $result = $database->query($sql);
        $row = $result->fetch_assoc();
        return $row['count'] ?? 0;
    }

    /**
     * Counts number of recipes created by this user
     * @return int Number of recipes
     */
    public function count_recipes() {
        $database = static::get_database();
        $sql = "SELECT COUNT(*) as count FROM recipe WHERE user_id = " . $database->real_escape_string($this->user_id);
        $result = $database->query($sql);
        $row = $result->fetch_assoc();
        return $row['count'] ?? 0;
    }

    /**
     * Toggles the user's active status
     * @return bool True if toggle was successful
     */
    public function toggle_active() {
        $this->is_active = !$this->is_active;
        return $this->save();
    }

    /**
     * Checks if a username is unique in the database
     * @param string $username Username to check
     * @param int $current_id Current user ID (to exclude from check)
     * @return bool True if username is unique
     */
    public static function check_unique_username($username, $current_id=0) {
        $database = static::get_database();
        $sql = "SELECT COUNT(*) as count FROM " . static::$table_name . " ";
        $sql .= "WHERE username='" . $database->real_escape_string($username) . "' ";
        $sql .= "AND user_id != '" . $database->real_escape_string($current_id) . "'";
        $result = $database->query($sql);
        $row = $result->fetch_assoc();
        return $row['count'] == 0;
    }

    /**
     * Checks if an email is unique in the database
     * @param string $email Email to check
     * @param int $current_id Current user ID (to exclude from check)
     * @return bool True if email is unique
     */
    public static function check_unique_email($email, $current_id=0) {
        $database = static::get_database();
        $sql = "SELECT COUNT(*) as count FROM " . static::$table_name . " ";
        $sql .= "WHERE email='" . $database->real_escape_string($email) . "' ";
        $sql .= "AND user_id != '" . $database->real_escape_string($current_id) . "'";
        $result = $database->query($sql);
        $row = $result->fetch_assoc();
        return $row['count'] == 0;
    }
}