<?php
// Validation functions

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
    } elseif(!has_length($recipe_data['description'], ['min' => 10, 'max' => 65535])) {
        $errors['description'] = "Description must be between 10 and 65,535 characters.";
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
    if($cook_hours == 0 && $cook_minutes == 0) {
        $errors['cook_time'] = "Please specify cooking time.";
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