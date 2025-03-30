<?php
/**
 * Error handling and display functions
 */

/**
 * Displays validation errors in a formatted list
 * @param array $errors Array of error messages to display
 * @param string $display_type Type of error display (traditional or inline)
 * @return string HTML formatted error messages
 */
function display_errors($errors=array(), $display_type='traditional') {
    $output = '';
    if(!empty($errors)) {
        if($display_type == 'traditional') {
            $output .= "<div class=\"message error\">";
            $output .= "<p>Please fix the following errors:</p>";
            $output .= "<ul>";
            foreach($errors as $error) {
                $output .= "<li>" . h($error) . "</li>";
            }
            $output .= "</ul>";
            $output .= "</div>";
        } elseif($display_type == 'inline') {
            $output .= "<div class=\"message error\">";
            foreach($errors as $error) {
                $output .= "<p>" . h($error) . "</p>";
            }
            $output .= "</div>";
        }
    }
    return $output;
}

/**
 * Displays a 404 Not Found error page
 * 
 * This function is designed to work across all environments (Apache, Docker, production)
 * by using the 404.php file which includes the site header and footer.
 * 
 * @param string $message Optional custom message to display
 * @return void This function exits and does not return
 */
function error_404($message = '') {
    // Set proper HTTP status code
    header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found");
    
    // Set a custom error message if provided
    $error_message = $message;
    
    // Include the 404.php file which will handle displaying the error page
    include(PUBLIC_PATH . '/404.php');
    
    // Terminate script execution
    exit();
}

/**
 * Displays a session message (flash message)
 * @return string HTML formatted message or empty string if no message
 */
function display_session_message() {
    $session = Session::get_instance();
    if($session) {
        $msg = $session->message();
        if(isset($msg) && $msg != '') {
            return '<div class="message success">' . h($msg) . '</div>';
        }
    }
    return '';
}
?>
