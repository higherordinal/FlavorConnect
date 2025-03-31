<?php
// Use a path that works in both XAMPP and Docker environments
$init_path = file_exists('../private/core/initialize.php') ? '../private/core/initialize.php' : '/var/www/html/private/core/initialize.php';
require_once($init_path);

$page_title = '404 Error Test Cases';
$page_style = 'admin'; // Using admin style for the test page

include(SHARED_PATH . '/public_header.php');
?>

<div class="container mt-5 mb-5">
    <h1>404 Error Test Cases</h1>
    <p class="lead">This page tests various scenarios where 404 errors should be triggered.</p>
    
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h2 class="h4 mb-0">Test Categories</h2>
        </div>
        <div class="card-body">
            <ul class="nav nav-tabs" id="errorTestTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="direct-tab" data-toggle="tab" href="#direct" role="tab">Direct URL Tests</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="apache-tab" data-toggle="tab" href="#apache" role="tab">Apache 404 Tests</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="api-tab" data-toggle="tab" href="#api" role="tab">API Tests</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="recipe-tab" data-toggle="tab" href="#recipe" role="tab">Recipe Tests</a>
                </li>
            </ul>
            
            <div class="tab-content p-3" id="errorTestTabsContent">
                <!-- Direct URL Tests -->
                <div class="tab-pane fade show active" id="direct" role="tabpanel">
                    <h3>Direct URL Tests</h3>
                    <p>These tests directly call the error_404() function with different parameters.</p>
                    
                    <div class="list-group">
                        <a href="<?php echo url_for('/test-direct-404.php?type=default'); ?>" target="_blank" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h5 class="mb-1">Default Error Message</h5>
                                <small>Opens in new tab</small>
                            </div>
                            <p class="mb-1">Tests the default error message with no custom text.</p>
                        </a>
                        
                        <a href="<?php echo url_for('/test-direct-404.php?type=recipe'); ?>" target="_blank" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h5 class="mb-1">Recipe Not Found Message</h5>
                                <small>Opens in new tab</small>
                            </div>
                            <p class="mb-1">Tests a custom error message for recipe not found.</p>
                        </a>
                        
                        <a href="<?php echo url_for('/test-direct-404.php?type=custom'); ?>" target="_blank" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h5 class="mb-1">Custom Error Message</h5>
                                <small>Opens in new tab</small>
                            </div>
                            <p class="mb-1">Tests a completely custom error message.</p>
                        </a>
                    </div>
                </div>
                
                <!-- Apache 404 Tests -->
                <div class="tab-pane fade" id="apache" role="tabpanel">
                    <h3>Apache 404 Tests</h3>
                    <p>These tests trigger Apache's 404 handling for non-existent files and directories.</p>
                    
                    <div class="list-group">
                        <a href="<?php echo url_for('/non-existent-page.php'); ?>" target="_blank" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h5 class="mb-1">Non-existent PHP File</h5>
                                <small>Opens in new tab</small>
                            </div>
                            <p class="mb-1">Tests Apache's 404 handling for a non-existent PHP file.</p>
                        </a>
                        
                        <a href="<?php echo url_for('/non-existent-directory/'); ?>" target="_blank" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h5 class="mb-1">Non-existent Directory</h5>
                                <small>Opens in new tab</small>
                            </div>
                            <p class="mb-1">Tests Apache's 404 handling for a non-existent directory.</p>
                        </a>
                        
                        <a href="<?php echo url_for('/assets/images/non-existent-image.jpg'); ?>" target="_blank" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h5 class="mb-1">Non-existent Image</h5>
                                <small>Opens in new tab</small>
                            </div>
                            <p class="mb-1">Tests Apache's 404 handling for a non-existent image file.</p>
                        </a>
                    </div>
                </div>
                
                <!-- API Tests -->
                <div class="tab-pane fade" id="api" role="tabpanel">
                    <h3>API Tests</h3>
                    <p>These tests check 404 handling for API endpoints.</p>
                    
                    <div class="list-group">
                        <a href="<?php echo url_for('/api/non-existent-endpoint'); ?>" target="_blank" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h5 class="mb-1">Non-existent API Endpoint</h5>
                                <small>Opens in new tab</small>
                            </div>
                            <p class="mb-1">Tests 404 handling for a non-existent API endpoint.</p>
                        </a>
                        
                        <a href="<?php echo url_for('/api/recipes/9999'); ?>" target="_blank" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h5 class="mb-1">Non-existent Recipe ID in API</h5>
                                <small>Opens in new tab</small>
                            </div>
                            <p class="mb-1">Tests 404 handling for a non-existent recipe ID in the API.</p>
                        </a>
                    </div>
                </div>
                
                <!-- Recipe Tests -->
                <div class="tab-pane fade" id="recipe" role="tabpanel">
                    <h3>Recipe Tests</h3>
                    <p>These tests check 404 handling for recipe-related pages.</p>
                    
                    <div class="list-group">
                        <a href="<?php echo url_for('/recipes/show.php?id=9999'); ?>" target="_blank" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h5 class="mb-1">Non-existent Recipe ID</h5>
                                <small>Opens in new tab</small>
                            </div>
                            <p class="mb-1">Tests 404 handling for a non-existent recipe ID.</p>
                        </a>
                        
                        <a href="<?php echo url_for('/recipes/show.php?id=abc'); ?>" target="_blank" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h5 class="mb-1">Invalid Recipe ID Format</h5>
                                <small>Opens in new tab</small>
                            </div>
                            <p class="mb-1">Tests 404 handling for an invalid recipe ID format.</p>
                        </a>
                        
                        <a href="<?php echo url_for('/recipes/edit.php?id=9999'); ?>" target="_blank" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h5 class="mb-1">Edit Non-existent Recipe</h5>
                                <small>Opens in new tab</small>
                            </div>
                            <p class="mb-1">Tests 404 handling when trying to edit a non-existent recipe.</p>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header bg-info text-white">
            <h2 class="h4 mb-0">Test Results</h2>
        </div>
        <div class="card-body">
            <p>For each test case, check the following:</p>
            <ol>
                <li>The 404 page is displayed with the correct styling</li>
                <li>The HTTP status code is 404 (check browser developer tools)</li>
                <li>The error message is appropriate for the context</li>
                <li>The "Return Home" and "Browse Recipes" buttons work correctly</li>
                <li>The site header and footer are displayed correctly</li>
            </ol>
            
            <div class="alert alert-warning">
                <strong>Note:</strong> Some tests may not trigger the 404 page if the application handles the error differently (e.g., redirects to login page for authenticated sections).
            </div>
            
            <a href="<?php echo url_for('/index.php'); ?>" class="btn btn-primary">
                <i class="fas fa-home"></i> Return to Home
            </a>
        </div>
    </div>
</div>

<!-- Bootstrap JS and dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<?php include(SHARED_PATH . '/footer.php'); ?>
