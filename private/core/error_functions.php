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
 * This function redirects to the 404.php page, which is the best practice approach
 * for handling 404 errors consistently across all environments.
 * 
 * @param string $message Optional custom message to display
 * @return void This function exits and does not return
 */
function error_404($message = '') {
    // Set proper HTTP status code if headers haven't been sent yet
    if (!headers_sent()) {
        header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found");
    }
    
    // Store the error message in the session if provided
    if (!empty($message) && isset($GLOBALS['session'])) {
        $GLOBALS['session']->message($message);
    }
    
    // Redirect to the 404.php page
    $redirect_url = url_for('/404.php');
    header("Location: {$redirect_url}");
    
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
