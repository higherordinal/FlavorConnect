<?php
/**
 * 404 Error Test Page
 * 
 * This file provides a simple interface to test various 404 error scenarios
 * in the FlavorConnect application.
 */

// Initialize the application
require_once('../private/core/initialize.php');

// Set page title
$page_title = "404 Error Test";

// Include header
include(SHARED_PATH . '/member_header.php');

// Define test scenarios
$test_scenarios = [
    [
        'name' => 'Non-existent Page',
        'url' => url_for('/this-page-does-not-exist.php'),
        'description' => 'Tests a direct request to a page that does not exist.'
    ],
    [
        'name' => 'Non-existent Recipe',
        'url' => url_for('/recipes/show.php?id=999999'),
        'description' => 'Tests accessing a recipe with an ID that does not exist in the database.'
    ],
    [
        'name' => 'Invalid Recipe ID Format',
        'url' => url_for('/recipes/show.php?id=not-a-number'),
        'description' => 'Tests accessing a recipe with an invalid ID format.'
    ],
    [
        'name' => 'Non-existent Category',
        'url' => url_for('/recipes/index.php?category=999999'),
        'description' => 'Tests accessing a category that does not exist.'
    ],
    [
        'name' => 'Restricted Admin Area',
        'url' => url_for('/admin/'),
        'description' => 'Tests accessing the admin area without proper authentication.'
    ],
    [
        'name' => 'API Error',
        'url' => url_for('/api/non-existent-endpoint.php'),
        'description' => 'Tests accessing a non-existent API endpoint.'
    ],
    [
        'name' => 'Malformed URL',
        'url' => url_for('/recipes/show.php?id=1&malformed=true%3Cscript%3Ealert(1)%3C/script%3E'),
        'description' => 'Tests a URL with potentially malicious parameters.'
    ],
    [
        'name' => 'Force 404 via Code',
        'url' => url_for('/test-404.php?force=true'),
        'description' => 'Tests forcing a 404 error via PHP code.'
    ]
];

// Force a 404 error if requested
if (isset($_GET['force']) && $_GET['force'] === 'true') {
    error_404();
}
?>

<div class="container">
    <h1>404 Error Test Page</h1>
    <p class="lead">Use this page to test various 404 error scenarios in your local environment.</p>
    
    <div class="card mb-4">
        <div class="card-header">
            <h2>Current Server Information</h2>
        </div>
        <div class="card-body">
            <pre><?php
                echo 'SERVER_NAME: ' . ($_SERVER['SERVER_NAME'] ?? 'Not set') . "\n";
                echo 'REQUEST_URI: ' . ($_SERVER['REQUEST_URI'] ?? 'Not set') . "\n";
                echo 'SCRIPT_NAME: ' . ($_SERVER['SCRIPT_NAME'] ?? 'Not set') . "\n";
                echo 'DOCUMENT_ROOT: ' . ($_SERVER['DOCUMENT_ROOT'] ?? 'Not set') . "\n";
                echo 'PHP_SELF: ' . ($_SERVER['PHP_SELF'] ?? 'Not set') . "\n";
                echo 'HTTP_HOST: ' . ($_SERVER['HTTP_HOST'] ?? 'Not set') . "\n";
                echo 'HTTPS: ' . (isset($_SERVER['HTTPS']) ? $_SERVER['HTTPS'] : 'Not set') . "\n";
            ?></pre>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h2>Test Scenarios</h2>
        </div>
        <div class="card-body">
            <div class="list-group">
                <?php foreach($test_scenarios as $scenario): ?>
                <div class="list-group-item">
                    <h3><?php echo h($scenario['name']); ?></h3>
                    <p><?php echo h($scenario['description']); ?></p>
                    <div class="d-flex justify-content-between align-items-center">
                        <code><?php echo h($scenario['url']); ?></code>
                        <a href="<?php echo $scenario['url']; ?>" class="btn btn-primary" target="_blank">Test</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <div class="card mt-4">
        <div class="card-header">
            <h2>404 Page Configuration</h2>
        </div>
        <div class="card-body">
            <p>Your 404 page is configured in the following files:</p>
            <ul>
                <li><code>public/404.php</code> - The main 404 error page</li>
                <li><code>public/.htaccess</code> - Apache configuration for error handling</li>
                <li><code>private/functions.php</code> - Contains the <code>error_404()</code> function</li>
            </ul>
            <p>To debug 404 issues, check:</p>
            <ol>
                <li>Apache error logs</li>
                <li>PHP error logs</li>
                <li>The REQUEST_URI and REDIRECT_STATUS variables in your 404.php file</li>
            </ol>
        </div>
    </div>
</div>

<?php include(SHARED_PATH . '/member_footer.php'); ?>
