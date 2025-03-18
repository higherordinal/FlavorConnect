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
 * @return void
 */
function error_404() {
    header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found");
    include(PUBLIC_PATH . '/404.php');
    exit();
}

/**
 * Displays a 500 Internal Server Error page
 * @return void
 */
function error_500() {
    header($_SERVER["SERVER_PROTOCOL"] . " 500 Internal Server Error");
    include(PUBLIC_PATH . '/500.php');
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
