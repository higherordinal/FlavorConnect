<?php
/**
 * Comprehensive 404 Error Testing System
 * 
 * This file provides an advanced interface to test and diagnose
 * all aspects of the 404 error handling system in FlavorConnect.
 * 
 * Features:
 * - Tests different types of 404 error scenarios
 * - Verifies proper HTTP status code responses
 * - Checks .htaccess configuration
 * - Tests error_404() function behavior
 * - Validates custom error messages
 * - Provides detailed diagnostics
 */

// Initialize the application
require_once('../private/core/initialize.php');

// Set page title and styles
$page_title = "Comprehensive 404 Error Test";
$page_style = "admin";
$custom_styles = true;

// Include header
include(SHARED_PATH . '/member_header.php');

// Define test scenarios with more detailed information
$test_scenarios = [
    [
        'name' => 'Non-existent Page',
        'url' => url_for('/this-page-does-not-exist.php'),
        'description' => 'Tests a direct request to a page that does not exist.',
        'expected_status' => 404,
        'expected_behavior' => 'Apache should return a 404 status code and display the 404.php page.'
    ],
    [
        'name' => 'Non-existent Recipe',
        'url' => url_for('/recipes/show.php?id=999999'),
        'description' => 'Tests accessing a recipe with an ID that does not exist in the database.',
        'expected_status' => 404,
        'expected_behavior' => 'The show.php script should call error_404() when the recipe is not found.'
    ],
    [
        'name' => 'Invalid Recipe ID Format',
        'url' => url_for('/recipes/show.php?id=not-a-number'),
        'description' => 'Tests accessing a recipe with an invalid ID format.',
        'expected_status' => 404,
        'expected_behavior' => 'The show.php script should validate the ID and call error_404() for invalid formats.'
    ],
    [
        'name' => 'Missing Required Parameter',
        'url' => url_for('/recipes/show.php'),
        'description' => 'Tests accessing a page without a required parameter (recipe ID).',
        'expected_status' => 404,
        'expected_behavior' => 'The show.php script should check for required parameters and call error_404() if missing.'
    ],
    [
        'name' => 'Restricted Admin Area',
        'url' => url_for('/admin/'),
        'description' => 'Tests accessing the admin area without proper authentication.',
        'expected_status' => 403,
        'expected_behavior' => 'Should redirect to login page or display an access denied message.'
    ],
    [
        'name' => 'API Error',
        'url' => url_for('/api/non-existent-endpoint.php'),
        'description' => 'Tests accessing a non-existent API endpoint.',
        'expected_status' => 404,
        'expected_behavior' => 'Should return a JSON error response with a 404 status code.'
    ],
    [
        'name' => 'Force 404 via Code',
        'url' => url_for('/404-test-comprehensive.php?force=true'),
        'description' => 'Tests forcing a 404 error via the error_404() function.',
        'expected_status' => 404,
        'expected_behavior' => 'The error_404() function should set the status code to 404 and display the 404.php page.'
    ],
    [
        'name' => 'Force 404 with Custom Message',
        'url' => url_for('/404-test-comprehensive.php?force=true&message=Custom+error+message'),
        'description' => 'Tests forcing a 404 error with a custom error message.',
        'expected_status' => 404,
        'expected_behavior' => 'The 404.php page should display the custom error message.'
    ],
    [
        'name' => 'Malformed URL',
        'url' => url_for('/recipes/show.php?id=1&malformed=true%3Cscript%3Ealert(1)%3C/script%3E'),
        'description' => 'Tests a URL with potentially malicious parameters.',
        'expected_status' => 200,
        'expected_behavior' => 'The application should properly sanitize the input and display the recipe.'
    ]
];

// Function to check HTTP status code of a URL
function check_status_code($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_exec($ch);
    $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return $status_code;
}

// Function to check if .htaccess is properly configured
function check_htaccess_configuration() {
    $issues = [];
    
    // Check if .htaccess file exists
    if (!file_exists(PUBLIC_PATH . '/.htaccess')) {
        $issues[] = 'The .htaccess file is missing in the public directory.';
    } else {
        // Check if ErrorDocument directive is present
        $htaccess_content = file_get_contents(PUBLIC_PATH . '/.htaccess');
        if (strpos($htaccess_content, 'ErrorDocument 404') === false) {
            $issues[] = 'The ErrorDocument 404 directive is missing in the .htaccess file.';
        }
    }
    
    return $issues;
}

// Function to check if the error_404 function is properly defined
function check_error_404_function() {
    $issues = [];
    
    if (!function_exists('error_404')) {
        $issues[] = 'The error_404() function is not defined.';
    }
    
    return $issues;
}

// Force a 404 error if requested
if (isset($_GET['force']) && $_GET['force'] === 'true') {
    $message = isset($_GET['message']) ? $_GET['message'] : null;
    error_404($message);
}

// Run diagnostics if requested
$run_diagnostics = isset($_GET['diagnostics']) && $_GET['diagnostics'] === 'true';
$htaccess_issues = [];
$function_issues = [];

if ($run_diagnostics) {
    $htaccess_issues = check_htaccess_configuration();
    $function_issues = check_error_404_function();
}

// Check status codes if requested
$check_status = isset($_GET['check_status']) && $_GET['check_status'] === 'true';
$status_results = [];

if ($check_status) {
    foreach ($test_scenarios as $scenario) {
        $status_code = check_status_code($scenario['url']);
        $status_results[$scenario['name']] = [
            'url' => $scenario['url'],
            'expected' => $scenario['expected_status'],
            'actual' => $status_code,
            'passed' => $status_code == $scenario['expected_status']
        ];
    }
}
?>

<div class="container">
    <h1>Comprehensive 404 Error Test</h1>
    <p class="lead">This tool helps you test and diagnose all aspects of the 404 error handling system in FlavorConnect.</p>
    
    <div class="alert alert-info">
        <h4>How to Use This Tool</h4>
        <ol>
            <li>Click on the "Test" buttons to try different 404 error scenarios</li>
            <li>Use the "Run Diagnostics" button to check your .htaccess and error_404() function</li>
            <li>Use the "Check Status Codes" button to verify proper HTTP status code responses</li>
        </ol>
    </div>
    
    <div class="row mb-4">
        <div class="col-md-6">
            <a href="?diagnostics=true" class="btn btn-info btn-lg btn-block">Run Diagnostics</a>
        </div>
        <div class="col-md-6">
            <a href="?check_status=true" class="btn btn-warning btn-lg btn-block">Check Status Codes</a>
        </div>
    </div>
    
    <?php if ($run_diagnostics): ?>
    <div class="card mb-4">
        <div class="card-header bg-info text-white">
            <h2>Diagnostics Results</h2>
        </div>
        <div class="card-body">
            <h3>.htaccess Configuration</h3>
            <?php if (empty($htaccess_issues)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> .htaccess file is properly configured.
                </div>
            <?php else: ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> Issues found with .htaccess configuration:
                    <ul>
                        <?php foreach ($htaccess_issues as $issue): ?>
                            <li><?php echo h($issue); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <h3>error_404() Function</h3>
            <?php if (empty($function_issues)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> error_404() function is properly defined.
                </div>
            <?php else: ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> Issues found with error_404() function:
                    <ul>
                        <?php foreach ($function_issues as $issue): ?>
                            <li><?php echo h($issue); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if ($check_status): ?>
    <div class="card mb-4">
        <div class="card-header bg-warning">
            <h2>Status Code Check Results</h2>
        </div>
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Scenario</th>
                        <th>URL</th>
                        <th>Expected Status</th>
                        <th>Actual Status</th>
                        <th>Result</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($status_results as $name => $result): ?>
                    <tr>
                        <td><?php echo h($name); ?></td>
                        <td><code><?php echo h($result['url']); ?></code></td>
                        <td><?php echo h($result['expected']); ?></td>
                        <td><?php echo h($result['actual']); ?></td>
                        <td>
                            <?php if ($result['passed']): ?>
                                <span class="badge badge-success">PASSED</span>
                            <?php else: ?>
                                <span class="badge badge-danger">FAILED</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="card mb-4">
        <div class="card-header">
            <h2>Server Information</h2>
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
                echo 'PHP Version: ' . phpversion() . "\n";
                echo 'Web Server: ' . ($_SERVER['SERVER_SOFTWARE'] ?? 'Not set') . "\n";
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
                    <div class="d-flex w-100 justify-content-between">
                        <h3><?php echo h($scenario['name']); ?></h3>
                        <span class="badge badge-primary">Expected: <?php echo h($scenario['expected_status']); ?></span>
                    </div>
                    <p><?php echo h($scenario['description']); ?></p>
                    <div class="mb-2">
                        <strong>Expected Behavior:</strong> <?php echo h($scenario['expected_behavior']); ?>
                    </div>
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
            <h2>404 Error Handling System</h2>
        </div>
        <div class="card-body">
            <h3>Key Components</h3>
            <ul>
                <li><code>public/404.php</code> - The main 404 error page that displays the error message</li>
                <li><code>public/.htaccess</code> - Apache configuration with ErrorDocument directive</li>
                <li><code>private/functions.php</code> - Contains the <code>error_404()</code> function</li>
                <li><code>private/shared/header.php</code> - Includes the header for the 404 page</li>
                <li><code>private/shared/footer.php</code> - Includes the footer for the 404 page</li>
            </ul>
            
            <h3>How 404 Errors Are Triggered</h3>
            <ol>
                <li><strong>Apache Level</strong>: When a requested file doesn't exist, Apache triggers the ErrorDocument directive</li>
                <li><strong>PHP Level</strong>: When a resource is not found in the database, the script calls error_404()</li>
                <li><strong>Manual Trigger</strong>: You can manually call error_404() with a custom message</li>
            </ol>
            
            <h3>Debugging 404 Issues</h3>
            <p>To debug 404 issues, check:</p>
            <ol>
                <li>Apache error logs (typically in /var/log/apache2/error.log or XAMPP/logs/error.log)</li>
                <li>PHP error logs (check php.ini for error_log setting)</li>
                <li>The REQUEST_URI and REDIRECT_STATUS variables in your 404.php file</li>
                <li>Verify that .htaccess is enabled and properly configured</li>
                <li>Ensure the error_404() function is properly defined and called</li>
            </ol>
        </div>
    </div>
</div>

<style>
    .badge-success {
        background-color: #28a745;
    }
    .badge-danger {
        background-color: #dc3545;
    }
    .badge-primary {
        background-color: #007bff;
    }
    pre {
        background-color: #f8f9fa;
        padding: 15px;
        border-radius: 5px;
    }
    .list-group-item {
        margin-bottom: 10px;
    }
</style>

<?php include(SHARED_PATH . '/footer.php'); ?>
