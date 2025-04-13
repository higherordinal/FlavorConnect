/**
 * Pagination Component
 * 
 * Enhances pagination with AJAX-based navigation to avoid full page reloads.
 * Features:
 * - AJAX-based content loading
 * - History API integration for proper back/forward navigation
 * - Fallback to traditional pagination if JavaScript is disabled
 * - Maintains scroll position when appropriate
 *
 * Works with the Pagination.class.php output format
 */

// Add to FlavorConnect namespace
window.FlavorConnect = window.FlavorConnect || {};
window.FlavorConnect.components = window.FlavorConnect.components || {};

(function() {
    'use strict';
    
    // Configuration
    const config = {
        contentSelectors: [
            '.recipe-grid',           // Recipe gallery
            '.user-list',             // User listings
            '.admin-table-container',  // Admin tables
            '.content-container',      // Generic content container
            '#main-content',           // Main content area
            'main'                     // HTML5 main element
        ],
        paginationSelector: '.pagination',
        paginationLinkSelector: '.pagination a', // Simplified selector to match all links
        loadingClass: 'fc-loading',
        debug: true,
        ajaxRequestCount: 0 // Track AJAX requests
    };
    
    // Store original content for fallback
    let originalContent = null;
    let contentContainer = null;
    
    // Wait for DOM to be fully loaded with a slight delay to ensure all scripts are loaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(initPagination, 50);
        });
    } else {
        setTimeout(initPagination, 50);
    }
    
    /**
     * Initialize pagination enhancement
     */
    function initPagination() {
        log('Initializing pagination component...');
        console.info('%c[Pagination] Initializing AJAX pagination', 'color: green; font-weight: bold');
        
        // Find all pagination containers
        const paginationContainers = document.querySelectorAll(config.paginationSelector);
        
        if (paginationContainers.length === 0) {
            log('No pagination containers found on this page');
            return; // No pagination on this page
        }
        
        log(`Found ${paginationContainers.length} pagination containers`);
        
        // Find the content container that will be updated
        contentContainer = findContentContainer();
        if (!contentContainer) {
            log('Could not find a suitable content container, pagination will use traditional page loads');
            return;
        }
        
        log(`Using content container: ${contentContainer.tagName}${contentContainer.id ? ' #' + contentContainer.id : ''}${contentContainer.className ? ' .' + contentContainer.className.replace(/ /g, ' .') : ''}`);
        
        // Store original content for fallback
        originalContent = contentContainer.innerHTML;
        
        // Get all links in all pagination containers
        let paginationLinks = [];
        paginationContainers.forEach(function(container) {
            // First try the specific selector
            const links = container.querySelectorAll(config.paginationLinkSelector);
            if (links.length > 0) {
                paginationLinks = [...paginationLinks, ...links];
            } else {
                // If no links found with specific selector, try a more general approach
                const allLinks = container.querySelectorAll('a');
                allLinks.forEach(function(link) {
                    if (link.href.includes('page=')) {
                        paginationLinks.push(link);
                    }
                });
            }
        });
        
        log(`Found ${paginationLinks.length} pagination links`);
        
        if (paginationLinks.length === 0) {
            log('No pagination links found, disabling AJAX pagination');
            return;
        }
        
        // Add click handlers to all pagination links
        paginationLinks.forEach(function(link, index) {
            // Remove any existing event listeners to prevent duplicates
            link.removeEventListener('click', handlePaginationClick);
            
            // Add the click handler
            link.addEventListener('click', handlePaginationClick);
            
            // Mark this link as AJAX-enabled
            link.setAttribute('data-ajax-pagination', 'true');
            
            log(`Added click handler to link ${index}: ${link.href}`);
        });
        
        log('Pagination component initialized successfully');
        console.info('%c[Pagination] AJAX Pagination Ready - ' + paginationLinks.length + ' links enabled', 'color: green; font-weight: bold');
    }
    
    /**
     * Find the content container that should be updated
     * @return {HTMLElement|null} The content container or null if not found
     */
    function findContentContainer() {
        // Try each of the possible content selectors
        for (const selector of config.contentSelectors) {
            const container = document.querySelector(selector);
            if (container) {
                log(`Found content container with selector: ${selector}`);
                return container;
            }
        }
        
        // If we couldn't find a container using our predefined selectors,
        // look for any container that contains the pagination
        const pagination = document.querySelector(config.paginationSelector);
        if (pagination) {
            // Find the closest parent that could be a content container
            let parent = pagination.parentNode;
            while (parent && parent !== document.body) {
                if (parent.classList.length > 0) {
                    log(`Found content container by traversing up from pagination: ${parent.className}`);
                    return parent;
                }
                parent = parent.parentNode;
            }
        }
        
        log('Could not find any suitable content container', true);
        return null;
    }
    
    /**
     * Handle pagination link clicks
     * @param {Event} event Click event
     */
    function handlePaginationClick(event) {
        // Don't intercept if modifier keys are pressed (new tab, etc.)
        if (event.ctrlKey || event.metaKey || event.shiftKey) {
            return;
        }
        
        // Prevent default link behavior
        event.preventDefault();
        
        // Get the URL from the link
        const url = this.href || this.getAttribute('href');
        
        if (!contentContainer) {
            // Fallback to traditional navigation if we can't find the content container
            log('No content container found, falling back to traditional navigation', true);
            window.location.href = url;
            return;
        }
        
        log(`Handling pagination click for URL: ${url}`);
        console.info(`%c[Pagination] Loading page via AJAX: ${url}`, 'color: green; font-weight: bold;');
        
        // Show loading indicator
        showLoadingIndicator();
        
        // Increment AJAX request counter
        config.ajaxRequestCount++;
        log(`AJAX request count: ${config.ajaxRequestCount}`);
        
        // Create XMLHttpRequest object (more widely supported than fetch)
        const xhr = new XMLHttpRequest();
        xhr.open('GET', url, true);
        
        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 400) {
                // Success!
                log('Page loaded successfully via AJAX');
                
                // Process the new content
                processNewContent(xhr.responseText, url);
                
                // Update browser history
                updateHistory(url);
            } else {
                // Error
                log(`Error loading page: ${xhr.status}`, true);
                restoreOriginalContent();
                showErrorMessage(`Failed to load content (${xhr.status}). Please try again.`);
                
                // Fall back to traditional navigation
                window.location.href = url;
            }
            
            // Hide loading indicator
            hideLoadingIndicator();
        };
        
        xhr.onerror = function() {
            // Connection error
            log('Network error occurred', true);
            restoreOriginalContent();
            showErrorMessage('Network error. Please try again.');
            hideLoadingIndicator();
            
            // Fall back to traditional navigation
            window.location.href = url;
        };
        
        xhr.send();
    }
    
    /**
     * Show a loading indicator while content is being fetched
     */
    function showLoadingIndicator() {
        // Add loading class to content container
        contentContainer.classList.add(config.loadingClass);
        
        // Create loading overlay if it doesn't exist
        if (!document.querySelector('.pagination-loading-overlay')) {
            const overlay = document.createElement('div');
            overlay.className = 'pagination-loading-overlay';
            overlay.innerHTML = `
                <div class="loading-spinner">
                    <i class="fas fa-spinner fa-spin"></i>
                    <span>Loading...</span>
                </div>
            `;
            contentContainer.appendChild(overlay);
        }
    }
    
    /**
     * Hide the loading indicator
     */
    function hideLoadingIndicator() {
        // Remove loading class
        contentContainer.classList.remove(config.loadingClass);
        
        // Remove loading overlay if it exists
        const overlay = document.querySelector('.pagination-loading-overlay');
        if (overlay && overlay.parentNode) {
            overlay.parentNode.removeChild(overlay);
        }
    }
    
    /**
     * Fetch new content via AJAX
     * @param {string} url The URL to fetch
     */
    function fetchContent(url) {
        log(`Fetching content from ${url}`);
        
        fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Network response was not ok: ${response.status}`);
                }
                return response.text();
            })
            .then(html => {
                processNewContent(html, url);
            })
            .catch(error => {
                log(`Error fetching content: ${error.message}`, true);
                restoreOriginalContent();
                
                // Show error message
                showErrorMessage('Failed to load content. Please try again.');
            });
    }
    
    /**
     * Process the new content and update the container
     * @param {string} html The HTML content
     * @param {string} url The URL that was fetched
     */
    function processNewContent(html, url) {
        log('Processing new content');
        
        // Create a temporary element to parse the HTML
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        
        // Find the corresponding content in the new page
        let newContentContainer = null;
        
        // Try each of the possible content selectors
        for (const selector of config.contentSelectors) {
            const container = doc.querySelector(selector);
            if (container) {
                newContentContainer = container;
                log(`Found content container using selector: ${selector}`);
                break;
            }
        }
        
        // Update the container with the new content
        if (newContentContainer) {
            log('Found new content container, updating');
            
            // Extract the pagination container from the new content
            const newPagination = tempDiv.querySelector(config.paginationSelector);
            
            // Update the content container
            contentContainer.innerHTML = newContentContainer.innerHTML;
            
            // If we found a new pagination container, replace the old one
            if (newPagination) {
                const oldPagination = document.querySelector(config.paginationSelector);
                if (oldPagination && oldPagination.parentNode) {
                    log('Replacing pagination container');
                    oldPagination.parentNode.replaceChild(newPagination, oldPagination);
                }
            } else {
                log('No pagination container found in new content', true);
            }
            
            // Re-initialize pagination for the new content
            initPagination();
            
            // Re-initialize other components if needed
            if (window.FlavorConnect && window.FlavorConnect.initComponents) {
                log('Initializing other FlavorConnect components');
                window.FlavorConnect.initComponents();
            }
            
            // Re-initialize recipe favorite functionality if it exists
            if (window.FlavorConnect.initFavorites) {
                log('Re-initializing favorites functionality');
                window.FlavorConnect.initFavorites();
            }
            
            // Scroll to top of content container
            contentContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
        } else {
            log('Could not find new content container, restoring original', true);
            restoreOriginalContent();
        }
        
        // Hide loading indicator
        hideLoadingIndicator();
    }
    
    /**
     * Restore the original content if something goes wrong
     */
    function restoreOriginalContent() {
        if (originalContent && contentContainer) {
            contentContainer.innerHTML = originalContent;
            hideLoadingIndicator();
            
            // Re-initialize pagination
            initPagination();
        }
    }
    
    /**
     * Show an error message to the user
     * @param {string} message The error message to display
     */
    function showErrorMessage(message) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'pagination-error-message';
        errorDiv.textContent = message;
        
        // Add to content container
        contentContainer.appendChild(errorDiv);
        
        // Remove after 5 seconds
        setTimeout(() => {
            if (errorDiv.parentNode) {
                errorDiv.parentNode.removeChild(errorDiv);
            }
        }, 5000);
    }
    
    /**
     * Update browser history to maintain back/forward navigation
     * @param {string} url The URL to add to history
     */
    function updateHistory(url) {
        if (window.history && window.history.pushState) {
            log(`Updating history with URL: ${url}`);
            window.history.pushState({ ajaxPagination: true, url: url }, '', url);
            
            // Handle browser back/forward buttons
            if (!window.onpopstate) {
                window.onpopstate = function(event) {
                    if (event.state && event.state.ajaxPagination) {
                        log('Handling popstate event');
                        fetchContent(event.state.url);
                    } else {
                        // If no state or not our state, reload the page
                        window.location.reload();
                    }
                };
            }
        }
    }
    
    /**
     * Log messages to console if debug is enabled
     * @param {string} message The message to log
     * @param {boolean} isError Whether this is an error message
     */
    function log(message, isError = false) {
        if (config.debug) {
            if (isError) {
                console.error(`[Pagination] ${message}`);
            } else {
                console.log(`[Pagination] ${message}`);
            }
        }
        
        // Also update the status message on the test page if it exists
        const statusMessage = document.getElementById('status-message');
        if (statusMessage && isError) {
            statusMessage.textContent = `Error: ${message}`;
            statusMessage.style.color = 'red';
        }
    }
    
    // Expose the pagination component to the FlavorConnect namespace
    window.FlavorConnect.components.pagination = {
        init: initPagination,
        config: config, // Expose config for testing
        enable: function() {
            // Re-initialize pagination with AJAX enabled
            initPagination();
        },
        disable: function() {
            // Remove event listeners from pagination links
            const paginationLinks = document.querySelectorAll(config.paginationLinkSelector);
            paginationLinks.forEach(function(link) {
                // Clone and replace to remove all event listeners
                const newLink = link.cloneNode(true);
                link.parentNode.replaceChild(newLink, link);
            });
            log('AJAX pagination disabled');
        }
    };
})();
    
    /**
     * Handle pagination link clicks
     * @param {Event} event Click event
     */
    function handlePaginationClick(event) {
        // Don't intercept if modifier keys are pressed (new tab, etc.)
        if (event.ctrlKey || event.metaKey || event.shiftKey) {
            return;
        }
        
        event.preventDefault();
        const url = this.getAttribute('href');
        const targetContainer = findContentContainer(this);
        
        if (!targetContainer) {
            // Fallback to traditional navigation if we can't find the content container
            window.location.href = url;
            return;
        }
        
        // Show loading indicator
        showLoadingIndicator(targetContainer);
        
        // Load the new content via AJAX
        fetchContent(url, targetContainer);
        
        // Update browser history
        updateHistory(url);
    }
    
    /**
     * Find the content container that should be updated
     * @param {HTMLElement} paginationLink The clicked pagination link
     * @return {HTMLElement|null} The content container or null if not found
     */
    function findContentContainer(paginationLink) {
        // First check for a data attribute that explicitly defines the target
        const targetSelector = paginationLink.getAttribute('data-target');
        if (targetSelector) {
            return document.querySelector(targetSelector);
        }
        
        // Otherwise, look for common content containers
        let container = paginationLink.closest('.pagination-container');
        if (container) {
            // Look for the content container as a sibling of the pagination container
            const parent = container.parentNode;
            const contentContainers = parent.querySelectorAll('.content-container, .recipe-grid, .user-list, table');
            if (contentContainers.length > 0) {
                return contentContainers[0];
            }
        }
        
        // If we can't determine the container, return null
        return null;
    }
    
    /**
     * Show a loading indicator while content is being fetched
     * @param {HTMLElement} container The container to show loading in
     */
    function showLoadingIndicator(container) {
        // Save the original content
        container.setAttribute('data-original-content', container.innerHTML);
        
        // Create and show loading indicator
        const loadingHTML = `
            <div class="loading-indicator">
                <div class="spinner"></div>
                <p>Loading content...</p>
            </div>
        `;
        
        // Only replace content if this isn't already a loading indicator
        if (!container.querySelector('.loading-indicator')) {
            container.innerHTML = loadingHTML;
        }
    }
    
    /**
     * Fetch new content via AJAX
     * @param {string} url The URL to fetch
     * @param {HTMLElement} container The container to update
     */
    function fetchContent(url, container) {
        fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.text();
            })
            .then(html => {
                processNewContent(html, container, url);
            })
            .catch(error => {
                console.error('Error fetching content:', error);
                restoreOriginalContent(container);
                
                // Show error message
                const errorMessage = document.createElement('div');
                errorMessage.className = 'error-message';
                errorMessage.textContent = 'Failed to load content. Please try again.';
                container.appendChild(errorMessage);
                
                // Remove error message after a delay
                setTimeout(() => {
                    if (errorMessage.parentNode) {
                        errorMessage.parentNode.removeChild(errorMessage);
                    }
                }, 5000);
            });
    }
    
    /**
     * Process the new content and update the container
     * @param {string} html The HTML content
     * @param {HTMLElement} container The container to update
     * @param {string} url The URL that was fetched
     */
    function processNewContent(html, container) {
        // Create a temporary element to parse the HTML
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = html;
        
        // Find the corresponding content in the new page
        let newContent;
        
        // Try to find the same container by ID or class
        if (container.id) {
            newContent = tempDiv.querySelector('#' + container.id);
        }
        
        if (!newContent && container.className) {
            // Try each class individually
            const classes = container.className.split(' ');
            for (const className of classes) {
                if (className && className !== 'loading') {
                    const elements = tempDiv.querySelectorAll('.' + className);
                    if (elements.length === 1) {
                        newContent = elements[0];
                        break;
                    }
                }
            }
        }
        
        // If we couldn't find an exact match, look for common content containers
        if (!newContent) {
            newContent = tempDiv.querySelector('.content-container, .recipe-grid, .user-list, table');
        }
        
        // Update the container with the new content
        if (newContent) {
            container.innerHTML = newContent.innerHTML;
            
            // Re-initialize pagination for the new content
            initPagination();
            
            // Re-initialize other components if needed
            if (window.FlavorConnect && window.FlavorConnect.initComponents) {
                window.FlavorConnect.initComponents();
            }
        } else {
            // If we couldn't find the new content, restore the original
            restoreOriginalContent(container);
        }
    }
    
    /**
     * Restore the original content if something goes wrong
     * @param {HTMLElement} container The container to restore
     */
    function restoreOriginalContent(container) {
        const originalContent = container.getAttribute('data-original-content');
        if (originalContent) {
            container.innerHTML = originalContent;
            
            // Re-initialize pagination
            initPagination();
        }
    }
    
    /**
     * Update browser history to maintain back/forward navigation
     * @param {string} url The URL to add to history
     */
    function updateHistory(url) {
        if (window.history && window.history.pushState) {
            window.history.pushState({ url: url }, '', url);
            
            // Handle browser back/forward buttons
            window.onpopstate = function(event) {
                if (event.state && event.state.url) {
                    const containers = document.querySelectorAll('.content-container, .recipe-grid, .user-list, table');
                    if (containers.length > 0) {
                        fetchContent(event.state.url, containers[0]);
                    } else {
                        // Fallback to traditional navigation
                        window.location.href = event.state.url;
                    }
                }
            };
        }
    }
})();
