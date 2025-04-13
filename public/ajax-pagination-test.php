<?php
require_once('../private/core/initialize.php');

$page_title = 'AJAX Pagination Test';
$page_style = 'recipe-gallery';
$component_styles = ['pagination'];

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

// Include a minimal header
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
        
        .item-meta {
            font-size: 0.9rem;
            color: #777;
            margin-top: 0.5rem;
            padding-top: 0.5rem;
            border-top: 1px solid #eee;
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
            min-height: 100px;
            opacity: 0.7;
        }
        
        .ajax-loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(255, 255, 255, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
            border-radius: 8px;
        }
        
        .loading-spinner {
            display: inline-block;
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>AJAX Pagination Test</h1>
        
        <div class="status-panel">
            <div id="status-message">Status: Ready</div>
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
                    $url_pattern = url_for('/ajax-pagination-test.php') . '?page={page}';
                    echo $pagination->page_links($url_pattern);
                    ?>
                </div>
                
                <div class="records-info">
                    Showing <?php echo count($current_items); ?> of <?php echo $total_items; ?> total items
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Direct script injection to ensure it runs immediately -->
    <script>
    // Immediately log that script is running
    console.log('%c[AJAX Test] Script tag executed', 'color: green; font-weight: bold');
    
    // Function to initialize pagination
    function initAjaxPagination() {
        console.log('%c[AJAX Test] Initializing AJAX pagination', 'color: blue; font-weight: bold');
        
        // Track page loads and AJAX requests
        let pageLoadCount = 1;
        let ajaxRequestCount = 0;
        
        // Get from session storage if available
        if (sessionStorage.getItem('pageLoadCount')) {
            pageLoadCount = parseInt(sessionStorage.getItem('pageLoadCount')) + 1;
        }
        sessionStorage.setItem('pageLoadCount', pageLoadCount);
        
        // Update counters
        document.getElementById('page-load-count').textContent = pageLoadCount;
        document.getElementById('ajax-request-count').textContent = ajaxRequestCount;
        
        // Find content container
        const contentContainer = document.getElementById('content-container');
        if (!contentContainer) {
            console.error('[AJAX Test] Content container not found');
            document.getElementById('status-message').textContent = 'Status: Error - Content container not found';
            return;
        }
        
        // Find pagination links
        const paginationContainer = document.getElementById('pagination-container');
        if (!paginationContainer) {
            console.error('[AJAX Test] Pagination container not found');
            document.getElementById('status-message').textContent = 'Status: Error - Pagination container not found';
            return;
        }
        
        // Get all links in the pagination container
        const paginationLinks = paginationContainer.querySelectorAll('a');
        console.log(`[AJAX Test] Found ${paginationLinks.length} pagination links:`, paginationLinks);
        
        if (paginationLinks.length === 0) {
            console.error('[AJAX Test] No pagination links found');
            document.getElementById('status-message').textContent = 'Status: Error - No pagination links found';
            return;
        }
        
        // Function to handle pagination link clicks
        function handlePaginationClick(event) {
            event.preventDefault();
            console.log('%c[AJAX Test] Pagination link clicked: ' + this.href, 'color: blue; font-weight: bold');
            
            // Update status
            document.getElementById('status-message').textContent = 'Status: Loading...';
            document.getElementById('status-message').style.color = 'blue';
            
            // Show loading indicator
            contentContainer.classList.add('ajax-loading');
            const overlay = document.createElement('div');
            overlay.className = 'ajax-loading-overlay';
            overlay.innerHTML = '<div class="loading-spinner"></div>';
            contentContainer.appendChild(overlay);
            
            // Increment AJAX request counter
            ajaxRequestCount++;
            document.getElementById('ajax-request-count').textContent = ajaxRequestCount;
            console.log(`[AJAX Test] AJAX request count: ${ajaxRequestCount}`);
            
            // Fetch the page content
            fetch(this.href)
                .then(response => {
                    console.log(`[AJAX Test] Fetch response received, status: ${response.status}`);
                    if (!response.ok) {
                        throw new Error(`Network response was not ok: ${response.status}`);
                    }
                    return response.text();
                })
                .then(html => {
                    console.log('[AJAX Test] HTML content received, length:', html.length);
                    
                    // Parse the HTML
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    
                    // Get the new content
                    const newContent = doc.getElementById('content-container');
                    if (newContent) {
                        console.log('[AJAX Test] Found content container in response');
                        contentContainer.innerHTML = newContent.innerHTML;
                        
                        // Re-initialize pagination for the new content
                        initAjaxPagination();
                        
                        // Update browser history
                        history.pushState({}, '', this.href);
                        
                        // Update status
                        document.getElementById('status-message').textContent = 'Status: Loaded successfully';
                        document.getElementById('status-message').style.color = 'green';
                    } else {
                        console.error('[AJAX Test] Could not find content container in the response');
                        document.getElementById('status-message').textContent = 'Status: Error - Could not find content';
                        document.getElementById('status-message').style.color = 'red';
                    }
                    
                    // Remove loading indicator
                    contentContainer.classList.remove('ajax-loading');
                    const existingOverlay = contentContainer.querySelector('.ajax-loading-overlay');
                    if (existingOverlay) {
                        existingOverlay.remove();
                    }
                })
                .catch(error => {
                    console.error('[AJAX Test] Error fetching page:', error);
                    document.getElementById('status-message').textContent = 'Status: Error - ' + error.message;
                    document.getElementById('status-message').style.color = 'red';
                    
                    // Remove loading indicator
                    contentContainer.classList.remove('ajax-loading');
                    const existingOverlay = contentContainer.querySelector('.ajax-loading-overlay');
                    if (existingOverlay) {
                        existingOverlay.remove();
                    }
                });
        }
        
        // Add click handlers to pagination links
        paginationLinks.forEach(function(link, index) {
            console.log(`[AJAX Test] Adding click handler to link ${index}:`, link.href);
            
            // Remove any existing event listeners
            link.removeEventListener('click', handlePaginationClick);
            
            // Add the click handler
            link.addEventListener('click', handlePaginationClick);
            
            // Add a visual indicator that this link has AJAX enabled
            link.style.position = 'relative';
            link.setAttribute('data-ajax-enabled', 'true');
            
            // Add a hover effect to show it's clickable
            link.addEventListener('mouseover', function() {
                this.style.backgroundColor = '#3498db';
                this.style.color = 'white';
            });
            
            link.addEventListener('mouseout', function() {
                this.style.backgroundColor = '';
                this.style.color = '';
            });
        });
        
        console.log('%c[AJAX Test] AJAX pagination initialized successfully', 'color: green; font-weight: bold');
        document.getElementById('status-message').textContent = 'Status: AJAX Pagination Ready';
    }
    
    // Initialize immediately
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAjaxPagination);
        console.log('[AJAX Test] Added DOMContentLoaded listener');
    } else {
        // DOM already loaded, run now
        console.log('[AJAX Test] DOM already loaded, initializing now');
        initAjaxPagination();
    }
    </script>
    
    <!-- Backup initialization script at the end of the body -->
    <script>
    // Double-check initialization
    console.log('[AJAX Test] Backup initialization check');
    setTimeout(function() {
        const links = document.querySelectorAll('#pagination-container a');
        const enabledLinks = document.querySelectorAll('[data-ajax-enabled="true"]');
        
        console.log(`[AJAX Test] Found ${links.length} pagination links, ${enabledLinks.length} have AJAX enabled`);
        
        if (links.length > 0 && enabledLinks.length === 0) {
            console.log('%c[AJAX Test] AJAX not initialized properly, running backup initialization', 'color: red; font-weight: bold');
            initAjaxPagination();
        }
    }, 1000);
    </script>
</body>
</html>
