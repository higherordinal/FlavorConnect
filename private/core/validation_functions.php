<?php
require_once('database_functions.php');
require_once('core_utilities.php');

// Validation functions

/**
 * Generic validation function that validates data against a set of rules
 * 
 * This function serves as the central validation mechanism for FlavorConnect.
 * It processes an array of data against a set of defined validation rules and
 * returns any validation errors encountered.
 * 
 * Available validation rules:
 * - required: Ensures the field is not empty
 * - email: Validates email format
 * - numeric: Ensures value is a number
 * - integer: Ensures value is an integer
 * - url: Validates URL format
 * - id: Validates ID format (using is_valid_id)
 * - rating: Validates rating is between 1-5
 * - min: Ensures numeric value is at least the specified minimum
 * - max: Ensures numeric value is at most the specified maximum
 * - min_length: Ensures string length is at least the specified minimum
 * - max_length: Ensures string length is at most the specified maximum
 * - match: Ensures value matches another field's value
 * 
 * @param array $data The data to validate (associative array of field => value pairs)
 * @param array $rules The validation rules (associative array of field => rules array)
 * @return array Array of validation errors (field => error message/array)
 * 
 * @example
 * $rules = [
 *   'username' => ['required' => true, 'min_length' => 4, 'max_length' => 255],
 *   'email' => ['required' => true, 'email' => true]
 * ];
 * $errors = validate($user_data, $rules);
 */
function validate($data, $rules) {
    $errors = [];
    
    foreach ($rules as $field => $rule_set) {
        $value = $data[$field] ?? '';
        $rules_array = is_array($rule_set) ? $rule_set : [$rule_set];
        
        foreach ($rules_array as $rule_key => $rule_value) {
            $rule_name = is_string($rule_key) ? $rule_key : $rule_value;
            $rule_param = is_string($rule_key) ? $rule_value : null;
            
            // Skip further validation if field is empty and not required
            if ($rule_name !== 'required' && is_blank($value)) {
                continue;
            }
            
            // Validate based on rule type
            switch ($rule_name) {
                case 'required':
                    if (is_blank($value)) {
                        $errors[$field] = [
                            'type' => 'required',
                            'message' => ucfirst($field) . " cannot be blank."
                        ];
                    }
                    break;
                    
                case 'email':
                    if (!is_valid_email($value)) {
                        $errors[$field] = [
                            'type' => 'email',
                            'message' => "Please enter a valid email address."
                        ];
                    }
                    break;
                    
                case 'numeric':
                    if (!is_numeric($value)) {
                        $errors[$field] = [
                            'type' => 'numeric',
                            'message' => ucfirst($field) . " must be a number."
                        ];
                    }
                    break;
                    
                case 'integer':
                    if (!filter_var($value, FILTER_VALIDATE_INT)) {
                        $errors[$field] = [
                            'type' => 'integer',
                            'message' => ucfirst($field) . " must be an integer."
                        ];
                    }
                    break;
                    
                case 'url':
                    if (!is_valid_url($value)) {
                        $errors[$field] = [
                            'type' => 'url',
                            'message' => "Please enter a valid URL."
                        ];
                    }
                    break;
                    
                case 'id':
                    if (!is_valid_id($value, $rule_param ?? true)) {
                        $errors[$field] = [
                            'type' => 'id',
                            'message' => ucfirst($field) . " must be a valid ID."
                        ];
                    }
                    break;
                    
                case 'rating':
                    if (!is_valid_rating($value, $rule_param ?? true)) {
                        $errors[$field] = [
                            'type' => 'rating',
                            'message' => ucfirst($field) . " must be between 1 and 5."
                        ];
                    }
                    break;
                    
                case 'min':
                    if ($value < $rule_param) {
                        $errors[$field] = [
                            'type' => 'min',
                            'message' => ucfirst($field) . " must be at least " . $rule_param . ".",
                            'min' => $rule_param
                        ];
                    }
                    break;
                    
                case 'max':
                    if ($value > $rule_param) {
                        $errors[$field] = [
                            'type' => 'max',
                            'message' => ucfirst($field) . " must be at most " . $rule_param . ".",
                            'max' => $rule_param
                        ];
                    }
                    break;
                    
                case 'min_length':
                    if (strlen($value) < $rule_param) {
                        $errors[$field] = [
                            'type' => 'min_length',
                            'message' => ucfirst($field) . " must be at least " . $rule_param . " characters.",
                            'min_length' => $rule_param
                        ];
                    }
                    break;
                    
                case 'max_length':
                    if (strlen($value) > $rule_param) {
                        $errors[$field] = [
                            'type' => 'max_length',
                            'message' => ucfirst($field) . " must be at most " . $rule_param . " characters.",
                            'max_length' => $rule_param
                        ];
                    }
                    break;
                    
                case 'length':
                    if (!has_length($value, $rule_param)) {
                        $min = $rule_param['min'] ?? null;
                        $max = $rule_param['max'] ?? null;
                        $exact = $rule_param['exact'] ?? null;
                        
                        if ($exact) {
                            $errors[$field] = [
                                'type' => 'length',
                                'message' => ucfirst($field) . " must be exactly " . $exact . " characters.",
                                'exact' => $exact
                            ];
                        } else if ($min && $max) {
                            $errors[$field] = [
                                'type' => 'length',
                                'message' => ucfirst($field) . " must be between " . $min . " and " . $max . " characters.",
                                'min' => $min,
                                'max' => $max
                            ];
                        } else if ($min) {
                            $errors[$field] = [
                                'type' => 'length',
                                'message' => ucfirst($field) . " must be at least " . $min . " characters.",
                                'min' => $min
                            ];
                        } else if ($max) {
                            $errors[$field] = [
                                'type' => 'length',
                                'message' => ucfirst($field) . " must be at most " . $max . " characters.",
                                'max' => $max
                            ];
                        }
                    }
                    break;
            }
            
            // Stop processing rules for this field if an error was found
            if (isset($errors[$field])) {
                break;
            }
        }
    }
    
    return $errors;
}

/**
 * Validates if a value is present
 * @param string|null $value The value to check
 * @return bool True if value is blank
 */
function is_blank($value) {
    return !isset($value) || trim($value) === '';
}

/**
 * Validates if a value is a valid email format
 * @param string $email The value to check
 * @return bool True if value is a valid email
 */
function is_valid_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Validates if a value has a specific length
 * @param string $value The value to check
 * @param array $options Array with 'min' and/or 'max' length
 * @return bool True if length is valid
 */
function has_length($value, $options=[]) {
    $length = strlen($value);
    
    if(isset($options['min']) && $length < $options['min']) {
        return false;
    }
    
    if(isset($options['max']) && $length > $options['max']) {
        return false;
    }
    
    if(isset($options['exact']) && $length != $options['exact']) {
        return false;
    }
    
    return true;
}

/**
 * Validates if a password has a valid format
 * @param string $password The value to check
 * @return bool True if password is valid
 */
function has_valid_password_format($password) {
    $has_length = has_length($password, ['min' => 8]);
    $has_upper = preg_match('/[A-Z]/', $password);
    $has_lower = preg_match('/[a-z]/', $password);
    $has_number = preg_match('/[0-9]/', $password);
    return $has_length && $has_upper && $has_lower && $has_number;
}

/**
 * Validates if a username is unique
 * @param string $username The value to check
 * @param string $current_id The current user ID
 * @return bool True if username is unique
 */
function has_unique_username($username, $current_id="0") {
    $database = User::get_database();
    return has_unique_value($username, 'user_account', 'username', 'user_id', $current_id, $database);
}

/**
 * Validates if an email is unique
 * @param string $email The value to check
 * @param string $current_id The current user ID
 * @return bool True if email is unique
 */
function has_unique_email($email, $current_id="0") {
    $database = User::get_database();
    return has_unique_value($email, 'user_account', 'email', 'user_id', $current_id, $database);
}

/**
 * Validates if a number is between min and max values
 * @param int|float $value The value to check
 * @param int|float $min Minimum value
 * @param int|float $max Maximum value
 * @return bool True if value is between min and max
 */
function has_number_between($value, $min, $max) {
    if(!is_numeric($value)) {
        return false;
    }
    $value = (float)$value;
    return ($value >= $min && $value <= $max);
}

/**
 * Validates if a value is a valid ID (numeric and positive)
 * @param mixed $value The value to check
 * @param bool $required Whether the ID is required (cannot be empty)
 * @return bool True if value is a valid ID
 */
function is_valid_id($value, $required = true) {
    if($required && (is_blank($value) || !isset($value))) {
        return false;
    }
    
    if(!$required && (is_blank($value) || !isset($value))) {
        return true; // Not required and empty is valid
    }
    
    return is_numeric($value) && intval($value) > 0;
}

/**
 * Validates if a value is a valid rating (1-5)
 * @param mixed $value The value to check
 * @param bool $required Whether the rating is required (cannot be empty)
 * @return bool True if value is a valid rating
 */
function is_valid_rating($value, $required = true) {
    if($required && (is_blank($value) || !isset($value) || $value === '')) {
        return false;
    }
    
    if(!$required && (is_blank($value) || !isset($value) || $value === '')) {
        return true; // Not required and empty is valid
    }
    
    return is_numeric($value) && has_number_between($value, 1, 5);
}

/**
 * Validates if a URL is valid
 * @param string $url The URL to check
 * @return bool True if URL is valid
 */
function is_valid_url($url) {
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

/**
 * Validates if a file upload is valid
 * @param array $file The $_FILES array element
 * @param array $options Array with 'allowed_types' and 'max_size'
 * @return bool True if file is valid
 */
function is_valid_file_upload($file, $options=[]) {
    if(!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }

    $allowed_types = $options['allowed_types'] ?? ALLOWED_IMAGE_TYPES;
    $max_size = $options['max_size'] ?? MAX_FILE_SIZE;

    if(!in_array($file['type'], $allowed_types)) {
        return false;
    }

    if($file['size'] > $max_size) {
        return false;
    }

    return true;
}

/**
 * Validates recipe form data to ensure all required fields and constraints are met
 * 
 * This function validates recipe data submitted through forms, ensuring that:
 * - Title and description meet length requirements
 * - Category IDs (style, diet, type) are valid
 * - Preparation and cooking times are within acceptable ranges
 * - Video URL (if provided) is in a valid format
 * - Image uploads (if any) meet file type and size requirements
 * - Alt text is provided for accessibility when images are uploaded
 * 
 * The function uses the generic validate() function for basic field validation
 * and adds custom validation for time fields and file uploads.
 * 
 * @param array $recipe_data The recipe data to validate, including form fields and file uploads
 * @return array Associative array of validation errors (field => error message)
 * 
 * @example
 * $errors = validate_recipe($_POST);
 * if(empty($errors)) {
 *     // Process valid recipe data
 * } else {
 *     // Display errors to user
 * }
 */
function validate_recipe($recipe_data) {
    // Define validation rules
    $rules = [
        'title' => ['required' => true, 'min_length' => 2, 'max_length' => 255],
        'description' => ['required' => true, 'min_length' => 10, 'max_length' => 255],
        'style_id' => ['id' => true],
        'diet_id' => ['id' => true],
        'type_id' => ['id' => true]
    ];
    
    // Use the generic validation function
    $errors = validate($recipe_data, $rules);
    
    // Convert array errors to string errors
    foreach ($errors as $field => $error) {
        if (is_array($error)) {
            $errors[$field] = $error['message'] ?? "Invalid {$field}.";
        }
    }

    // Prep time validation
    $prep_hours = $recipe_data['prep_hours'] ?? 0;
    $prep_minutes = $recipe_data['prep_minutes'] ?? 0;
    if(!has_number_between($prep_hours, 0, 72)) {
        $errors['prep_hours'] = "Prep hours must be between 0 and 72.";
    }
    if(!has_number_between($prep_minutes, 0, 59)) {
        $errors['prep_minutes'] = "Prep minutes must be between 0 and 59.";
    }
    if($prep_hours == 0 && $prep_minutes == 0) {
        $errors['prep_time'] = "Please specify preparation time.";
    }

    // Cook time validation
    $cook_hours = $recipe_data['cook_hours'] ?? 0;
    $cook_minutes = $recipe_data['cook_minutes'] ?? 0;
    if(!has_number_between($cook_hours, 0, 72)) {
        $errors['cook_hours'] = "Cook hours must be between 0 and 72.";
    }
    if(!has_number_between($cook_minutes, 0, 59)) {
        $errors['cook_minutes'] = "Cook minutes must be between 0 and 59.";
    }

    // Video URL validation (optional)
    if(!empty($recipe_data['video_url'])) {
        if(!is_valid_url($recipe_data['video_url'])) {
            $errors['video_url'] = "Please enter a valid URL.";
        }
    }

    // Image validation (if uploaded)
    if(isset($_FILES['recipe_image']) && $_FILES['recipe_image']['error'] === UPLOAD_ERR_OK) {
        if(!is_valid_file_upload($_FILES['recipe_image'], [
            'allowed_types' => ALLOWED_IMAGE_TYPES,
            'max_size' => MAX_FILE_SIZE
        ])) {
            $errors['recipe_image'] = "Please upload a valid image file (JPG, PNG, GIF, or WebP) under " . (MAX_FILE_SIZE / 1048576) . "MB.";
        }
        
        // Alt text required if image is provided
        if(empty($recipe_data['alt_text'])) {
            $errors['alt_text'] = "Please provide alt text for the image.";
        }
    }

    return $errors;
}

/**
 * Validates recipe step data
 * @param array $step_data The step data to validate
 * @return array Array of validation errors
 */
function validate_recipe_step($step_data) {
    $rules = [
        'recipe_id' => ['id' => true],
        'step_number' => ['required' => true, 'numeric' => true, 'min' => 1, 'max' => 100],
        'instruction' => ['required' => true, 'min_length' => 3, 'max_length' => 255]
    ];
    
    $errors = validate($step_data, $rules);
    
    // Convert array errors to string errors
    foreach ($errors as $field => $error) {
        if (is_array($error)) {
            switch ($field) {
                case 'recipe_id':
                    $errors[$field] = "Recipe ID is required.";
                    break;
                case 'step_number':
                    $errors[$field] = "Step number must be between 1 and 100.";
                    break;
                case 'instruction':
                    $errors[$field] = "Instruction must be between 3 and 255 characters.";
                    break;
                default:
                    $errors[$field] = $error['message'] ?? "Invalid {$field}.";
            }
        }
    }
    
    return $errors;
}

/**
 * Validates recipe comment data
 * @param array $comment_data The comment data to validate
 * @return array Array of validation errors
 */
function validate_recipe_comment($comment_data) {
    $rules = [
        'recipe_id' => ['id' => true],
        'user_id' => ['id' => true],
        'rating_value' => ['rating' => true]
    ];
    
    // Add comment_text validation only if it's provided
    if(isset($comment_data['comment_text']) && $comment_data['comment_text'] !== '') {
        $rules['comment_text'] = ['min_length' => 2, 'max_length' => 255];
    }
    
    $errors = validate($comment_data, $rules);
    
    // Convert array errors to string errors
    foreach ($errors as $field => $error) {
        if (is_array($error)) {
            switch ($field) {
                case 'recipe_id':
                    $errors[$field] = "Recipe ID is required.";
                    break;
                case 'user_id':
                    $errors[$field] = "User ID is required.";
                    break;
                case 'rating_value':
                    $errors[$field] = "Rating must be between 1 and 5.";
                    break;
                case 'comment_text':
                    $errors[$field] = "Comment must be between 2 and 255 characters.";
                    break;
                default:
                    $errors[$field] = $error['message'] ?? "Invalid {$field}.";
            }
        }
    }
    
    return $errors;
}

/**
 * Validates user form data for registration and profile updates
 * 
 * This function performs comprehensive validation of user account data, ensuring:
 * - Username meets length requirements (4-255 chars), contains no spaces, and is unique
 * - Email is properly formatted and unique in the system
 * - Password (when provided) meets security requirements and matches confirmation
 * 
 * The function uses a combination of the generic validate() function for basic validations
 * and custom validation for uniqueness checks and password requirements. It handles both
 * new user registration and existing user updates by using the $current_id parameter
 * to exclude the current user from uniqueness checks.
 * 
 * @param array $user_data The user data to validate (username, email, password, etc.)
 * @param string $current_id Current user ID for unique checks ("0" for new users)
 * @return array Associative array of validation errors (field => error message)
 * 
 * @example
 * // For new user registration
 * $errors = validate_user($_POST);
 * 
 * // For updating existing user
 * $errors = validate_user($_POST, $current_user->id);
 */
function validate_user($user_data, $current_id="0") {
    // Basic validation rules
    $rules = [
        'username' => ['required' => true, 'min_length' => 4, 'max_length' => 255],
        'email' => ['required' => true, 'email' => true]
    ];
    
    // Add password validation if password is being set/updated
    if(isset($user_data['password'])) {
        $rules['password'] = ['required' => true];
        $rules['confirm_password'] = ['required' => true];
    }
    
    // Run basic validation
    $errors = validate($user_data, $rules);
    
    // Convert array errors to string errors
    foreach ($errors as $field => $error) {
        if (is_array($error)) {
            switch ($field) {
                case 'username':
                    $errors[$field] = "Username must be between 4 and 255 characters.";
                    break;
                case 'email':
                    $errors[$field] = "Please enter a valid email address.";
                    break;
                case 'password':
                    $errors[$field] = "Password cannot be blank.";
                    break;
                case 'confirm_password':
                    $errors[$field] = "Confirm password cannot be blank.";
                    break;
                default:
                    $errors[$field] = $error['message'] ?? "Invalid {$field}.";
            }
        }
    }
    
    // Custom validations that aren't handled by the generic validate function
    
    // Username additional validations
    if(!isset($errors['username'])) {
        if(!has_unique_username($user_data['username'], $current_id)) {
            $errors['username'] = "Username is already taken.";
        } elseif(!has_no_spaces($user_data['username'])) {
            $errors['username'] = "Username cannot contain spaces.";
        }
    }
    
    // Email uniqueness validation
    if(!isset($errors['email']) && !has_unique_email($user_data['email'], $current_id)) {
        $errors['email'] = "Email is already taken.";
    }
    
    // Password format and confirmation match
    if(isset($user_data['password']) && !isset($errors['password'])) {
        if(!has_valid_password_format($user_data['password'])) {
            $errors['password'] = "Password must be at least 8 characters and contain at least one uppercase letter, one lowercase letter, and one number.";
        } elseif($user_data['password'] !== $user_data['confirm_password']) {
            $errors['confirm_password'] = "Password and confirm password must match.";
        }
    }
    
    return $errors;
}

/**
 * Validates login form data
 * @param array $login_data The login data to validate
 * @return array Array of validation errors
 */
function validate_login($login_data) {
    $rules = [
        'username' => ['required' => true],
        'password' => ['required' => true]
    ];
    
    $errors = validate($login_data, $rules);
    
    // Convert array errors to string errors
    foreach ($errors as $field => $error) {
        if (is_array($error)) {
            $errors[$field] = $error['message'] ?? "Invalid {$field}.";
        }
    }
    
    return $errors;
}

/**
 * Validates recipe attribute data (style, diet, type)
 * @param array $attribute_data The attribute data to validate
 * @param string $type The attribute type (style, diet, type)
 * @param string $current_id Current ID for uniqueness check
 * @return array Array of validation errors
 */
function validate_recipe_attribute_data($attribute_data, $type = '', $current_id = '0') {
    $rules = [
        'name' => ['required' => true, 'min_length' => 2, 'max_length' => 255]
    ];
    
    $errors = validate($attribute_data, $rules);
    
    // Convert array errors to string errors
    foreach ($errors as $field => $error) {
        if (is_array($error)) {
            $errors[$field] = $error['message'] ?? "Invalid {$field}.";
        }
    }
    
    // Check for uniqueness if type is provided
    if ($type && !isset($errors['name'])) {
        // Map type to table name
        $tables = [
            'style' => 'recipe_style',
            'diet' => 'recipe_diet',
            'type' => 'recipe_type'
        ];
        
        $table = $tables[$type] ?? '';
        
        if ($table && !has_unique_metadata_name($attribute_data['name'], $table, $current_id)) {
            $errors['name'] = "A " . $type . " with the name '" . h($attribute_data['name']) . "' already exists.";
        }
    }

    return $errors;
}

/**
 * Generic function to check if a value is unique in a database table
 * 
 * This function provides a reusable way to check for uniqueness constraints across
 * the application. It's used to ensure values like usernames, emails, and metadata names
 * are unique within their respective tables. The function performs a database query
 * to count matching records, excluding the current record being edited if applicable.
 * 
 * @param string $value The value to check for uniqueness (e.g., username, email)
 * @param string $table The table name to check in (e.g., 'user_account', 'recipe_style')
 * @param string $field The field name to check for uniqueness (e.g., 'username', 'name')
 * @param string $id_field The primary key field name (e.g., 'user_id', 'style_id')
 * @param string $current_id The current ID to exclude from the check (for updates)
 * @param object $database The database connection to use (defaults to global $db)
 * @return bool True if the value is unique (no matching records found), false otherwise
 * 
 * @example
 * // Check if username is unique for a new user
 * $is_unique = has_unique_value('johndoe', 'user_account', 'username', 'user_id');
 * 
 * // Check if email is unique when updating user #5
 * $is_unique = has_unique_value('john@example.com', 'user_account', 'email', 'user_id', '5');
 */
function has_unique_value($value, $table, $field, $id_field, $current_id="0", $database=null) {
    if($database === null) {
        global $db;
        $database = $db;
    }
    
    if(empty($value) || empty($table) || empty($field) || empty($id_field)) {
        return false;
    }
    
    $sql = "SELECT COUNT(*) FROM {$table} ";
    $sql .= "WHERE {$field} = '" . db_escape($database, $value) . "' ";
    if($current_id != "0") {
        $sql .= "AND {$id_field} != '" . db_escape($database, $current_id) . "' ";
    }
    
    try {
        $result = mysqli_query($database, $sql);
        if(!$result) {
            return false;
        }
        $row = mysqli_fetch_row($result);
        mysqli_free_result($result);
        return ($row[0] == 0);
    } catch(Exception $e) {
        return false;
    }
}

/**
 * Validates if a metadata name is unique within its type
 * @param string $name The name to check
 * @param string $table The table to check in (recipe_style, recipe_diet, recipe_type, measurement)
 * @param string $current_id The current ID to exclude
 * @return bool True if name is unique
 */
function has_unique_metadata_name($name, $table, $current_id="0") {
    // Get the appropriate database connection based on table
    $database = $table === 'measurement' ? Measurement::get_database() : RecipeAttribute::get_database();
    
    // Map table names to their primary key columns
    $primary_keys = [
        'recipe_style' => 'style_id',
        'recipe_diet' => 'diet_id',
        'recipe_type' => 'type_id',
        'measurement' => 'measurement_id'
    ];
    
    $primary_key = $primary_keys[$table] ?? null;
    if (!$primary_key) {
        return false; // Invalid table name
    }
    
    return has_unique_value($name, $table, 'name', $primary_key, $current_id, $database);
}

/**
 * Validates if a username contains spaces
 * @param string $username The username to check
 * @return bool True if username does not contain spaces
 */
function has_no_spaces($username) {
    return strpos($username, ' ') === false;
}

/**
 * Checks if there would still be at least one active admin user after a change
 * @param string $user_id ID of the user being modified
 * @param bool $is_active Whether the user will be active
 * @return bool True if at least one active admin would remain
 */
function has_remaining_active_admin($user_id, $is_active=false) {
    $database = User::get_database();
    
    // Get the user being modified to check if they're an admin
    $user = User::find_by_id($user_id);
    if(!$user || !($user->is_admin() || $user->is_super_admin())) {
        return true; // Not an admin, so no impact on admin count
    }
    
    // Count active admins excluding this user
    $sql = "SELECT COUNT(*) FROM user_account ";
    $sql .= "WHERE user_id != '" . db_escape($database, $user_id) . "' ";
    $sql .= "AND (user_level = 'a' OR user_level = 's') ";
    $sql .= "AND is_active = 1";
    
    $result = $database->query($sql);
    if(!$result) {
        return false;
    }
    
    $row = $result->fetch_row();
    $active_admin_count = $row[0];
    $result->free();
    
    // If this user will be active, add 1 to the count
    if($is_active) {
        $active_admin_count++;
    }
    
    return $active_admin_count > 0;
}

/**
 * Validates user deletion
 * @param string $user_id ID of the user to delete
 * @return array Array of validation errors
 */
function validate_user_deletion($user_id) {
    $errors = [];
    
    // Validate user ID format first
    if(!is_valid_id($user_id)) {
        $errors[] = "Invalid user ID format.";
        return $errors;
    }
    
    $user = User::find_by_id($user_id);
    if(!$user) {
        $errors[] = "User not found.";
        return $errors;
    }
    
    // Check if this is a super admin
    if($user->is_super_admin()) {
        $errors[] = "Super admin users cannot be deleted.";
    }
    
    // Check if this would remove the last active admin
    if(($user->is_admin() || $user->is_super_admin()) && $user->is_active) {
        if(!has_remaining_active_admin($user_id)) {
            $errors[] = "Cannot delete the last active admin user.";
        }
    }
    
    return $errors;
}

/**
 * Validates recipe ingredient data
 * @param array $ingredient_data The ingredient data to validate
 * @return array Array of validation errors
 */
function validate_recipe_ingredient_data($ingredient_data) {
    $rules = [
        'recipe_id' => ['id' => true],
        'measurement_id' => ['id' => true],
        'quantity' => ['required' => true, 'numeric' => true, 'min' => 0.01, 'max' => 9999]
    ];
    
    // Add ingredient_name validation if it exists
    if(isset($ingredient_data['ingredient_name'])) {
        $rules['ingredient_name'] = ['required' => true, 'min' => 2, 'max' => 100];
    }
    
    $errors = validate($ingredient_data, $rules);
    
    // Convert array errors to string errors
    foreach ($errors as $field => $error) {
        if (is_array($error)) {
            switch ($field) {
                case 'recipe_id':
                    $errors[$field] = "Recipe ID is required.";
                    break;
                case 'measurement_id':
                    $errors[$field] = "Measurement is required.";
                    break;
                case 'quantity':
                    $errors[$field] = "Quantity must be a positive number.";
                    break;
                default:
                    $errors[$field] = $error['message'] ?? "Invalid {$field}.";
            }
        }
    }
    
    return $errors;
}

/**
 * Validates measurement data
 * @param array $measurement_data The measurement data to validate
 * @param string $current_id Current ID for uniqueness check
 * @return array Array of validation errors
 */
function validate_measurement_data($measurement_data, $current_id = '') {
    $rules = ['name' => ['required' => true, 'min' => 2, 'max' => 255]];
    $errors = validate($measurement_data, $rules);
    
    // Convert array errors to string errors
    foreach ($errors as $field => $error) {
        if (is_array($error)) {
            $errors[$field] = $error['message'] ?? "Invalid {$field}.";
        }
    }
    
    // Check for unique name
    if(!isset($errors['name']) && !has_unique_metadata_name($measurement_data['name'], 'measurement', $current_id)) {
        $errors['name'] = "A measurement with this name already exists.";
    }
    
    return $errors;
}

/**
 * Displays an inline error message for a form field if it exists in the errors array
 * @param string $field The field name to check for errors
 * @param array $errors The array of validation errors
 * @return string HTML for the error message or empty string if no error
 */
function display_error($field, $errors) {
    if (isset($errors[$field])) {
        $error = $errors[$field];
        $message = is_array($error) ? $error['message'] : $error;
        return '<div class="form-error">' . h($message) . '</div>';
    }
    return '';
}

/**
 * Adds error class to form field if it has an error
 * @param string $field The field name to check for errors
 * @param array $errors The array of validation errors
 * @return string 'error' class or empty string
 */
function error_class($field, $errors) {
    return isset($errors[$field]) ? ' error' : '';
}

/**
 * Validates recipe access and existence
 * @param mixed $recipe_id The recipe ID to validate (can be null, GET parameter, or direct value)
 * @param bool $check_ownership Whether to check if the current user owns the recipe
 * @param bool $admin_override Whether admins can access recipes they don't own
 * @param string $redirect_url URL to redirect to if validation fails (if empty, will use error_404/error_403)
 * @return Recipe|null The recipe object if validation passes, null if redirected
 */
function validate_recipe_access($recipe_id = null, $check_ownership = false, $admin_override = true, $redirect_url = '') {
    global $session;
    
    // Get recipe ID from parameter or GET
    if ($recipe_id === null) {
        if (!isset($_GET['id'])) {
            // No recipe ID provided
            if (!empty($redirect_url)) {
                redirect_to(url_for($redirect_url));
                return null;
            } else {
                error_404('Recipe ID is required.');
                return null;
            }
        }
        $recipe_id = $_GET['id'];
    }
    
    // Validate recipe ID format
    if (!is_valid_id($recipe_id)) {
        if (!empty($redirect_url)) {
            $session->message('Invalid recipe ID format.', 'error');
            redirect_to(url_for($redirect_url));
            return null;
        } else {
            error_404('Invalid recipe ID format.');
            return null;
        }
    }
    
    // Find the recipe
    $recipe = Recipe::find_by_id($recipe_id);
    
    // Check if recipe exists
    if (!$recipe) {
        if (!empty($redirect_url)) {
            $session->message('The recipe could not be found.', 'error');
            redirect_to(url_for($redirect_url));
            return null;
        } else {
            error_404("The recipe you're looking for could not be found. It may have been moved or deleted.");
            return null;
        }
    }
    
    // Check ownership if required
    if ($check_ownership) {
        $is_owner = ($recipe->user_id == $session->get_user_id());
        $is_admin_with_override = ($admin_override && $session->is_admin());
        
        if (!$is_owner && !$is_admin_with_override) {
            if (!empty($redirect_url)) {
                $session->message('You do not have permission to access this recipe.', 'error');
                redirect_to(url_for($redirect_url));
                return null;
            } else {
                error_403('You do not have permission to access this recipe.');
                return null;
            }
        }
    }
    
    return $recipe;
}

?>