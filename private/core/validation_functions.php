<?php
require_once('database_functions.php');
require_once('functions.php');

// Validation functions

/**
 * Generic validation function that validates data against a set of rules
 * @param array $data The data to validate
 * @param array $rules The validation rules
 * @return array Array of validation errors
 */
function validate($data, $rules) {
    $errors = [];
    
    foreach ($rules as $field => $rule) {
        $value = $data[$field] ?? '';
        
        // Handle different validation types
        if ($rule === 'required' && is_blank($value)) {
            $errors[$field] = ucfirst($field) . " cannot be blank.";
        } elseif ($rule === 'email' && !empty($value) && !is_valid_email($value)) {
            $errors[$field] = "Please enter a valid email address.";
        } elseif ($rule === 'numeric' && !empty($value) && !is_numeric($value)) {
            $errors[$field] = ucfirst($field) . " must be a number.";
        } elseif ($rule === 'url' && !empty($value) && !is_valid_url($value)) {
            $errors[$field] = "Please enter a valid URL.";
        } elseif (is_array($rule)) {
            // Handle min/max rules
            if (isset($rule['min']) && $value < $rule['min']) {
                $errors[$field] = ucfirst($field) . " must be at least " . $rule['min'] . ".";
            }
            if (isset($rule['max']) && $value > $rule['max']) {
                $errors[$field] = ucfirst($field) . " must be at most " . $rule['max'] . ".";
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
    if(isset($options['min']) && strlen($value) < $options['min']) {
        return false;
    } elseif(isset($options['max']) && strlen($value) > $options['max']) {
        return false;
    } elseif(isset($options['exact']) && strlen($value) != $options['exact']) {
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
    return User::check_unique_username($username, $current_id);
}

/**
 * Validates if an email is unique
 * @param string $email The value to check
 * @param string $current_id The current user ID
 * @return bool True if email is unique
 */
function has_unique_email($email, $current_id="0") {
    return User::check_unique_email($email, $current_id);
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

    $allowed_types = $options['allowed_types'] ?? ['image/jpeg', 'image/png', 'image/gif'];
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
 * Validates recipe form data
 * @param array $recipe_data The recipe data to validate
 * @return array Array of validation errors
 */
function validate_recipe($recipe_data) {
    $errors = [];

    // Title validation
    if(is_blank($recipe_data['title'])) {
        $errors['title'] = "Title cannot be blank.";
    } elseif(!has_length($recipe_data['title'], ['min' => 2, 'max' => 255])) {
        $errors['title'] = "Title must be between 2 and 255 characters.";
    }

    // Description validation
    if(is_blank($recipe_data['description'])) {
        $errors['description'] = "Description cannot be blank.";
    } elseif(!has_length($recipe_data['description'], ['min' => 10, 'max' => 255])) {
        $errors['description'] = "Description must be between 10 and 255 characters.";
    }

    // Style validation
    if(!isset($recipe_data['style_id']) || !is_numeric($recipe_data['style_id'])) {
        $errors['style_id'] = "Please select a cuisine style.";
    }

    // Diet validation
    if(!isset($recipe_data['diet_id']) || !is_numeric($recipe_data['diet_id'])) {
        $errors['diet_id'] = "Please select a diet type.";
    }

    // Type validation
    if(!isset($recipe_data['type_id']) || !is_numeric($recipe_data['type_id'])) {
        $errors['type_id'] = "Please select a meal type.";
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
            'allowed_types' => ['image/jpeg', 'image/png'],
            'max_size' => MAX_FILE_SIZE
        ])) {
            $errors['recipe_image'] = "Please upload a valid image file (JPG, PNG, or GIF) under " . (MAX_FILE_SIZE / 1048576) . "MB.";
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
    $errors = [];

    // Recipe ID validation
    if(!isset($step_data['recipe_id']) || !is_numeric($step_data['recipe_id'])) {
        $errors['recipe_id'] = "Recipe ID is required.";
    }

    // Step number validation
    if(!isset($step_data['step_number']) || !is_numeric($step_data['step_number'])) {
        $errors['step_number'] = "Step number is required.";
    } elseif(!has_number_between($step_data['step_number'], 1, 100)) {
        $errors['step_number'] = "Step number must be between 1 and 100.";
    }

    // Instruction validation
    if(is_blank($step_data['instruction'])) {
        $errors['instruction'] = "Instruction cannot be blank.";
    } elseif(!has_length($step_data['instruction'], ['min' => 3, 'max' => 255])) {
        $errors['instruction'] = "Instruction must be between 3 and 255 characters.";
    }

    return $errors;
}

/**
 * Validates recipe comment data
 * @param array $comment_data The comment data to validate
 * @return array Array of validation errors
 */
function validate_recipe_comment($comment_data) {
    $errors = [];

    // Recipe ID validation
    if(!isset($comment_data['recipe_id']) || !is_numeric($comment_data['recipe_id'])) {
        $errors['recipe_id'] = "Recipe ID is required.";
    }

    // User ID validation
    if(!isset($comment_data['user_id']) || !is_numeric($comment_data['user_id'])) {
        $errors['user_id'] = "User ID is required.";
    }

    // Rating validation
    if(!isset($comment_data['rating_value']) || $comment_data['rating_value'] === '') {
        $errors['rating_value'] = "Rating is required.";
    } elseif(!is_numeric($comment_data['rating_value']) || !has_number_between($comment_data['rating_value'], 1, 5)) {
        $errors['rating_value'] = "Rating must be between 1 and 5.";
    }

    // Comment text validation - only validate if provided
    if(isset($comment_data['comment_text']) && $comment_data['comment_text'] !== '') {
        if(!has_length($comment_data['comment_text'], ['min' => 2, 'max' => 255])) {
            $errors['comment_text'] = "Comment must be between 2 and 255 characters.";
        }
    }

    return $errors;
}

/**
 * Validates user form data
 * @param array $user_data The user data to validate
 * @param string $current_id Current user ID for unique checks
 * @return array Array of validation errors
 */
function validate_user($user_data, $current_id="0") {
    $errors = [];

    // Username validation
    if(is_blank($user_data['username'])) {
        $errors['username'] = "Username cannot be blank.";
    } elseif(!has_length($user_data['username'], ['min' => 4, 'max' => 255])) {
        $errors['username'] = "Username must be between 4 and 255 characters.";
    } elseif(!has_unique_username($user_data['username'], $current_id)) {
        $errors['username'] = "Username is already taken.";
    }

    // Email validation
    if(is_blank($user_data['email'])) {
        $errors['email'] = "Email cannot be blank.";
    } elseif(!is_valid_email($user_data['email'])) {
        $errors['email'] = "Please enter a valid email address.";
    } elseif(!has_unique_email($user_data['email'], $current_id)) {
        $errors['email'] = "Email is already taken.";
    }

    // Password validation
    if(isset($user_data['password'])) {
        if(is_blank($user_data['password'])) {
            $errors['password'] = "Password cannot be blank.";
        } elseif(!has_valid_password_format($user_data['password'])) {
            $errors['password'] = "Password must be at least 8 characters and contain at least one uppercase letter, one lowercase letter, and one number.";
        }

        if(is_blank($user_data['confirm_password'])) {
            $errors['confirm_password'] = "Confirm password cannot be blank.";
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
    $errors = [];

    if(is_blank($login_data['username'])) {
        $errors['username'] = "Username cannot be blank.";
    }
    if(is_blank($login_data['password'])) {
        $errors['password'] = "Password cannot be blank.";
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
    $errors = [];

    // Name validation
    if(is_blank($attribute_data['name'])) {
        $errors['name'] = "Name cannot be blank.";
    } elseif (!has_length($attribute_data['name'], ['min' => 2, 'max' => 255])) {
        $errors['name'] = "Name must be between 2 and 255 characters.";
    } elseif ($type) {
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
 * Validates if a metadata name is unique within its type
 * @param string $name The name to check
 * @param string $table The table to check in (recipe_style, recipe_diet, recipe_type)
 * @param string $current_id The current ID to exclude
 * @return bool True if name is unique
 */
function has_unique_metadata_name($name, $table, $current_id="0") {
    $database = RecipeAttribute::get_database();
    
    // Map table names to their primary key columns
    $primary_keys = [
        'recipe_style' => 'style_id',
        'recipe_diet' => 'diet_id',
        'recipe_type' => 'type_id'
    ];
    
    $primary_key = $primary_keys[$table] ?? null;
    if (!$primary_key) {
        return false; // Invalid table name
    }
    
    $sql = "SELECT COUNT(*) FROM " . $table;
    $sql .= " WHERE name='" . db_escape($database, $name) . "'";
    if($current_id != "0") {
        $sql .= " AND " . $primary_key . " != '" . db_escape($database, $current_id) . "'";
    }
    
    try {
        $result = mysqli_query($database, $sql);
        if (!$result) {
            return false;
        }
        $row = mysqli_fetch_row($result);
        return $row[0] == 0;
    } catch(Exception $e) {
        return false;
    }
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
    $errors = [];

    // Recipe ID validation
    if(!isset($ingredient_data['recipe_id']) || !is_numeric($ingredient_data['recipe_id'])) {
        $errors['recipe_id'] = "Recipe ID is required.";
    }

    // Measurement ID validation
    if(!isset($ingredient_data['measurement_id']) || !is_numeric($ingredient_data['measurement_id'])) {
        $errors['measurement_id'] = "Measurement is required.";
    }

    // Quantity validation
    if(is_blank($ingredient_data['quantity'])) {
        $errors['quantity'] = "Quantity cannot be blank.";
    } elseif(!is_numeric($ingredient_data['quantity']) || !has_number_between($ingredient_data['quantity'], 0.01, 9999)) {
        $errors['quantity'] = "Quantity must be a positive number.";
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
    $errors = [];
    
    // Name validation
    if(is_blank($measurement_data['name'])) {
        $errors['name'] = "Name cannot be blank.";
    } elseif (!has_length($measurement_data['name'], ['min' => 2, 'max' => 255])) {
        $errors['name'] = "Name must be between 2 and 255 characters.";
    } else {
        // Check if name is unique
        $database = Measurement::get_database();
        $sql = "SELECT COUNT(*) FROM measurement";
        $sql .= " WHERE name='" . db_escape($database, $measurement_data['name']) . "'";
        if($current_id != '') {
            $sql .= " AND measurement_id != '" . db_escape($database, $current_id) . "'";
        }
        
        try {
            $result = mysqli_query($database, $sql);
            if(!$result) {
                $errors['name'] = "Database error checking name uniqueness.";
            } else {
                $row = mysqli_fetch_row($result);
                if($row[0] > 0) {
                    $errors['name'] = "A measurement with this name already exists.";
                }
            }
        } catch(Exception $e) {
            $errors['name'] = "Error checking name uniqueness: " . $e->getMessage();
        }
    }
    
    return $errors;
}

?>