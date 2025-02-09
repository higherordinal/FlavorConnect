<?php
/**
 * API response and validation functions
 */

/**
 * Returns a JSON error response
 * @param string $message Error message
 * @param int $status HTTP status code
 */
function json_error($message, $status = 400) {
    http_response_code($status);
    echo json_encode([
        'success' => false,
        'error' => $message
    ]);
    exit;
}

/**
 * Returns a JSON success response
 * @param array $data Response data
 */
function json_success($data) {
    echo json_encode(array_merge(['success' => true], $data));
    exit;
}

/**
 * Validates API request data
 * @param array $data Request data to validate
 * @param array $rules Validation rules (e.g., ['recipe_id' => ['required', 'number', 'min:1']])
 * @return array Array of error messages, empty if validation passes
 */
function validate_api_request($data, $rules) {
    $errors = [];
    
    foreach ($rules as $field => $field_rules) {
        foreach ($field_rules as $rule) {
            if ($rule === 'required' && (!isset($data[$field]) || is_blank($data[$field]))) {
                $errors[] = ucfirst($field) . ' is required';
                break; // Skip other rules if required field is missing
            }
            
            if (!isset($data[$field]) || is_blank($data[$field])) {
                continue; // Skip other rules if field is not present (unless it was required)
            }
            
            if ($rule === 'number' && !is_numeric($data[$field])) {
                $errors[] = ucfirst($field) . ' must be a number';
            }
            
            if (strpos($rule, 'min:') === 0) {
                $min = (int)substr($rule, 4);
                if (!has_number_between($data[$field], $min, PHP_INT_MAX)) {
                    $errors[] = ucfirst($field) . ' must be at least ' . $min;
                }
            }
            
            if (strpos($rule, 'max:') === 0) {
                $max = (int)substr($rule, 4);
                if (!has_number_between($data[$field], 0, $max)) {
                    $errors[] = ucfirst($field) . ' must not exceed ' . $max;
                }
            }
        }
    }
    
    return $errors;
}

/**
 * Validates and processes an API request
 * @param array $request Request data including session, method, query params, and body
 * @param array $rules Validation rules
 * @param callable $success_callback Callback to run if validation passes
 */
function process_api_request($request, $rules, $success_callback) {
    try {
        // Extract request components
        $data = $request['body'] ?? [];
        
        // Validate JSON data
        if (!empty($request['raw_body']) && json_last_error() !== JSON_ERROR_NONE) {
            json_error('Invalid JSON data');
        }
        
        // Validate request data
        $errors = validate_api_request($data, $rules);
        if (!empty($errors)) {
            json_error(implode(', ', $errors));
        }
        
        // Run success callback with full request object
        $success_callback($request);
        
    } catch (Exception $e) {
        json_error($e->getMessage(), 500);
    }
}

/**
 * Ensures the request method matches the expected method
 * @param string $expected_method Expected HTTP method
 * @param string $actual_method Actual HTTP method
 */
function require_method($expected_method, $actual_method) {
    if ($actual_method !== strtoupper($expected_method)) {
        json_error('Method not allowed', 405);
    }
}
