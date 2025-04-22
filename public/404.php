<?php
// Suppress all warnings and notices for 404 page
error_reporting(E_ERROR | E_PARSE);

// Set proper HTTP status code
if (!headers_sent()) {
    http_response_code(404);
}

// For direct access via ErrorDocument, REQUEST_URI might be empty
// In that case, we need to use REDIRECT_URL instead
$request_uri = $_SERVER['REQUEST_URI'] ?? ($_SERVER['REDIRECT_URL'] ?? '');

// Determine if we're in XAMPP or production
$in_xampp = (strpos($request_uri, '/FlavorConnect/') === 0);

// Set the correct path to initialize.php based on environment
if ($in_xampp) {
    // XAMPP environment
    $initialize_path = $_SERVER['DOCUMENT_ROOT'] . '/FlavorConnect/private/core/initialize.php';
} else {
    // Production environment
    $initialize_path = __DIR__ . '/../private/core/initialize.php';
}

// Make sure the initialize file exists
if (!file_exists($initialize_path)) {
    // Fallback to other possible paths
    $possible_paths = [
        __DIR__ . '/../private/core/initialize.php',
        $_SERVER['DOCUMENT_ROOT'] . '/FlavorConnect/private/core/initialize.php',
        '../private/core/initialize.php'
    ];
    
    foreach ($possible_paths as $path) {
        if (file_exists($path)) {
            $initialize_path = $path;
            break;
        }
    }
}

// Initialize the application if needed
if (!defined('PRIVATE_PATH') && $initialize_path) {
    include_once($initialize_path);
}

// If we have the application initialized, use the proper headers and templates
if (defined('PRIVATE_PATH')) {
    // Set the page title and style
    $page_title = '404 - Page Not Found';
    $page_style = '404';
    
    // Scripts
    // Note: 'common' and 'back-link' are already loaded in public_header.php
    $utility_scripts = [];
    
    // Use member header if user is logged in, otherwise use public header
    if (function_exists('is_logged_in') && is_logged_in()) {
        include(SHARED_PATH . '/member_header.php');
    } else if (defined('SHARED_PATH')) {
        include(SHARED_PATH . '/public_header.php');
    }
} else {
    // Fallback if initialization failed - output a basic HTML header
    header('HTTP/1.1 404 Not Found');
    echo '<!DOCTYPE html>';
    echo '<html lang="en">';
    echo '<head>';
    echo '<meta charset="UTF-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
    echo '<title>404 - Page Not Found</title>';
    echo '<style>';
    echo 'body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 800px; margin: 0 auto; padding: 20px; }';
    echo 'h1 { font-size: 3rem; color: #fff; background-color: #ff6b6b; padding: 10px 20px; display: inline-block; }';
    echo 'a { color: #ff6b6b; text-decoration: none; }';
    echo 'a:hover { text-decoration: underline; }';
    echo '.error-actions { margin-top: 30px; }';
    echo '.error-actions a { margin-right: 10px; padding: 10px 20px; background: #ff6b6b; color: white; border-radius: 4px; }';
    echo '.error-actions a:hover { background: #ff5252; text-decoration: none; }';
    echo '</style>';
    echo '</head>';
    echo '<body>';
}

// Main content for the 404 page - this is shown regardless of initialization method
?>

<main class="error-404">
    <div class="container">
        <div class="error-content">
            <h1>404</h1>
            <h2>Oops! Page Not Found</h2>
            <p>
                <?php
                // Display custom message from session if available
                if (defined('PRIVATE_PATH') && isset($GLOBALS['session'])) {
                    $message = $GLOBALS['session']->message();
                    if (!empty($message)) {
                        echo h($message);
                    } else {
                        echo 'The page you requested could not be found. It may have been moved or deleted.';
                    }
                } else {
                    echo 'The page you requested could not be found. It may have been moved or deleted.';
                }
                ?>
            </p>
            <div class="error-actions">
                <a href="<?php echo defined('PRIVATE_PATH') ? url_for('/') : '/'; ?>" class="btn btn-primary">
                    <i class="fas fa-home"></i> Return Home
                </a>
                <a href="<?php echo defined('PRIVATE_PATH') ? url_for('/recipes') : '/recipes'; ?>" class="btn btn-secondary">
                    <i class="fas fa-utensils"></i> Browse Recipes
                </a>
            </div>
        </div>
    </div>
</main>

<?php
// Include footer if application is initialized
if (defined('SHARED_PATH')) {
    include(SHARED_PATH . '/footer.php');
} else {
    // Close the HTML tags if we're using the fallback
    echo '</body>';
    echo '</html>';
}
?>


