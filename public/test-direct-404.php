<?php
// Use a path that works in both XAMPP and Docker environments
$init_path = file_exists('../private/core/initialize.php') ? '../private/core/initialize.php' : '/var/www/html/private/core/initialize.php';
require_once($init_path);

// Get the test type from the URL parameter
$type = $_GET['type'] ?? 'default';

// Display different error messages based on the test type
switch ($type) {
    case 'recipe':
        $error_message = "The recipe you're looking for could not be found. It may have been removed or never existed.";
        error_404($error_message);
        break;
    case 'custom':
        $error_message = "This is a custom error message to test the flexibility of the error_404() function.";
        error_404($error_message);
        break;
    default:
        // Uses the default message
        error_404();
        break;
}
// This line should never be reached due to exit() in error_404()
?>
