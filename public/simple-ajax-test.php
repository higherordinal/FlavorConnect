<?php
require_once('../private/core/initialize.php');

$page_title = 'Simple AJAX Pagination Test';

// Get current page
$current_page = $_GET['page'] ?? 1;
$current_page = max(1, (int)$current_page);

// Set items per page
$per_page = 5;

// Create sample data (50 items)
$total_items = 50;
$items = [];
for ($i = 1; $i <= $total_items; $i++) {
    $items[] = [
        'id' => $i,
        'title' => "Test Item #{$i}",
        'description' => "This is a test item to demonstrate AJAX pagination. Item #{$i}."
    ];
}

// Create pagination object
$pagination = new Pagination($current_page, $per_page, $total_items);

// Calculate offset
$offset = ($current_page - 1) * $per_page;

// Get items for current page
$current_items = array_slice($items, $offset, $per_page);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo h($page_title); ?></title>
    <link rel="stylesheet" href="<?php echo url_for('/assets/css/main.css'); ?>">
    <link rel="stylesheet" href="<?php echo url_for('/assets/css/components/pagination.css'); ?>">
    <style>
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .test-item {
            background-color: #fff;
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .test-item h3 {
            margin-top: 0;
            color: #3498db;
        }
        
        .status-panel {
            background-color: #f8f9fa;
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .ajax-loading {
            position: relative;
            opacity: 0.7;
        }
        
        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-left: 10px;
            vertical-align: middle;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Simple AJAX Pagination Test</h1>
        
        <div class="status-panel">
            <div id="status-message">Status: <span id="status-text">Ready</span> <span id="loading-indicator" style="display:none;"><div class="loading-spinner"></div></span></div>
            <div id="page-loads">Page Loads: <span id="page-load-count">1</span></div>
            <div id="ajax-requests">AJAX Requests: <span id="ajax-request-count">0</span></div>
        </div>
        
        <div id="content-container">
            <h2>Test Items (Page <?php echo $current_page; ?> of <?php echo $pagination->total_pages(); ?>)</h2>
            
            <div class="test-items">
                <?php foreach($current_items as $item): ?>
                    <div class="test-item">
                        <h3><?php echo h($item['title']); ?></h3>
                        <p><?php echo h($item['description']); ?></p>
                        <div class="item-meta">
                            Item ID: <?php echo h($item['id']); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php if($pagination->total_pages() > 1): ?>
                <!-- Pagination Controls -->
                <div id="pagination-container">
                    <?php 
                    $url_pattern = url_for('/simple-ajax-test.php') . '?page={page}';
                    echo $pagination->page_links($url_pattern);
                    ?>
                </div>
                
                <div class="records-info">
                    Showing <?php echo count($current_items); ?> of <?php echo $total_items; ?> total items
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
    // Simple AJAX pagination script
    (function() {
        // Track page loads and AJAX requests
        let pageLoadCount = 1;
        let ajaxRequestCount = 0;
        
        // Get from session storage if available
        if (sessionStorage.getItem('simplePageLoadCount')) {
            pageLoadCount = parseInt(sessionStorage.getItem('simplePageLoadCount')) + 1;
        }
        sessionStorage.setItem('simplePageLoadCount', pageLoadCount);
        
        // Update counters
        document.getElementById('page-load-count').textContent = pageLoadCount;
        document.getElementById('ajax-request-count').textContent = ajaxRequestCount;
        
        // Get DOM elements
        const contentContainer = document.getElementById('content-container');
        const paginationContainer = document.getElementById('pagination-container');
        const statusText = document.getElementById('status-text');
        const loadingIndicator = document.getElementById('loading-indicator');
        
        // Function to handle AJAX loading
        function loadPageContent(url) {
            // Show loading state
            statusText.textContent = 'Loading...';
            statusText.style.color = 'blue';
            loadingIndicator.style.display = 'inline-block';
            
            // Increment AJAX counter
            ajaxRequestCount++;
            document.getElementById('ajax-request-count').textContent = ajaxRequestCount;
            
            // Create XMLHttpRequest object
            const xhr = new XMLHttpRequest();
            xhr.open('GET', url, true);
            
            xhr.onload = function() {
                if (xhr.status >= 200 && xhr.status < 400) {
                    // Success!
                    const responseHTML = xhr.responseText;
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(responseHTML, 'text/html');
                    
                    // Extract content
                    const newContent = doc.getElementById('content-container');
                    
                    if (newContent) {
                        // Replace content
                        contentContainer.innerHTML = newContent.innerHTML;
                        
                        // Re-attach event handlers
                        attachPaginationHandlers();
                        
                        // Update browser history
                        history.pushState({}, '', url);
                        
                        // Update status
                        statusText.textContent = 'Content loaded successfully';
                        statusText.style.color = 'green';
                    } else {
                        statusText.textContent = 'Error: Could not find content in response';
                        statusText.style.color = 'red';
                    }
                } else {
                    // Error
                    statusText.textContent = 'Error: ' + xhr.status;
                    statusText.style.color = 'red';
                }
                
                // Hide loading indicator
                loadingIndicator.style.display = 'none';
            };
            
            xhr.onerror = function() {
                // Connection error
                statusText.textContent = 'Network error occurred';
                statusText.style.color = 'red';
                loadingIndicator.style.display = 'none';
            };
            
            xhr.send();
        }
        
        // Function to attach click handlers to pagination links
        function attachPaginationHandlers() {
            const paginationLinks = document.querySelectorAll('.pagination a');
            
            paginationLinks.forEach(function(link) {
                link.addEventListener('click', function(e) {
                    // Prevent default link behavior
                    e.preventDefault();
                    
                    // Load the page content via AJAX
                    loadPageContent(this.href);
                });
                
                // Add visual indicator
                link.style.textDecoration = 'none';
                link.style.fontWeight = 'bold';
            });
        }
        
        // Initialize
        attachPaginationHandlers();
        
        // Update status
        statusText.textContent = 'AJAX Pagination Ready';
        statusText.style.color = 'green';
    })();
    </script>
</body>
</html>
