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

'use strict';

// Add to FlavorConnect namespace
window.FlavorConnect = window.FlavorConnect || {};
window.FlavorConnect.components = window.FlavorConnect.components || {};

// Pagination component
window.FlavorConnect.components.pagination = (function() {
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
        debug: false,                 // Set to false in production
        ajaxRequestCount: 0           // Track AJAX requests
    };
    
    // Store original content for fallback
    let originalContent = null;
    let contentContainer = null;
    
    /**
     * Initialize pagination enhancement
     */
    function initPagination() {
        log('Initializing pagination component...');
        
        // Find all pagination containers
        const paginationContainers = document.querySelectorAll(config.paginationSelector);
        
        if (paginationContainers.length === 0) {
            log('No pagination containers found on this page');
            return; // No pagination on this page
        }
        
        // Update active page based on current URL
        const currentUrl = new URL(window.location.href);
        updateActivePage(currentUrl);
        
        // Find the content container that will be updated
        contentContainer = findContentContainer();
        if (!contentContainer) {
            log('Could not find a suitable content container, pagination will use traditional page loads');
            return;
        }
        
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
        
        if (paginationLinks.length === 0) {
            log('No pagination links found, disabling AJAX pagination');
            return;
        }
        
        // Add click handlers to all pagination links
        paginationLinks.forEach(function(link) {
            // Remove any existing event listeners to prevent duplicates
            link.removeEventListener('click', handlePaginationClick);
            
            // Add the click handler
            link.addEventListener('click', handlePaginationClick);
            
            // Mark this link as AJAX-enabled
            link.setAttribute('data-ajax-pagination', 'true');
        });
        
        log('Pagination component initialized successfully');
    }
    
    /**
     * Find the content container that should be updated
     * @return {HTMLElement|null} The content container or null if not found
     */
    function findContentContainer() {
        // Try each selector in order until we find a match
        for (const selector of config.contentSelectors) {
            const container = document.querySelector(selector);
            if (container) {
                log(`Found content container with selector: ${selector}`);
                return container;
            }
        }
        
        // If no specific container found, use main content area as fallback
        const mainContent = document.querySelector('main') || document.getElementById('main-content');
        if (mainContent) {
            log('Using main content area as fallback container');
            return mainContent;
        }
        
        log('No content container found', true);
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
        
        event.preventDefault();
        const url = this.getAttribute('href');
        
        if (!url) {
            log('No URL found in pagination link', true);
            return;
        }
        
        // Ensure we have a full URL with proper query parameters
        const fullUrl = new URL(url, window.location.origin).href;
        
        log(`Handling pagination click for URL: ${fullUrl}`);
        
        // For recipe gallery, preload content before showing loading indicator
        const isRecipeGallery = window.location.pathname.includes('/recipes/');
        
        // Show loading indicator (subtle for recipe gallery)
        showLoadingIndicator();
        
        // Increment AJAX request counter
        config.ajaxRequestCount++;
        log(`AJAX request count: ${config.ajaxRequestCount}`);
        
        // Create XMLHttpRequest object
        const xhr = new XMLHttpRequest();
        xhr.open('GET', fullUrl, true);
        
        // Handle response
        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 400) {
                // Success!
                const responseHTML = xhr.responseText;
                processNewContent(responseHTML, fullUrl);
                hideLoadingIndicator();
                log(`Successfully loaded content from ${fullUrl}`);
            } else {
                // Error
                log(`Error loading content: ${xhr.status} ${xhr.statusText}`, true);
                restoreOriginalContent();
                hideLoadingIndicator();
                showErrorMessage(`Failed to load content (${xhr.status})`);
                
                // Fallback to traditional navigation
                window.location.href = fullUrl;
            }
        };
        
        xhr.onerror = function() {
            // Network error
            log('Network error while loading content', true);
            restoreOriginalContent();
            hideLoadingIndicator();
            showErrorMessage('Network error. Please try again.');
            
            // Fallback to traditional navigation
            window.location.href = fullUrl;
        };
        
        // Send the request
        xhr.send();
    }
    
    /**
     * Show a loading indicator while content is being fetched
     */
    function showLoadingIndicator() {
        if (contentContainer) {
            // For recipe gallery, use a more subtle loading indicator
            if (window.location.pathname.includes('/recipes/')) {
                // Just add the loading class without the message
                contentContainer.classList.add(config.loadingClass);
                log('Showing subtle loading indicator for recipe gallery');
            } else {
                // Standard loading indicator for other pages
                contentContainer.classList.add(config.loadingClass);
                
                // Create a loading message if it doesn't exist
                if (!document.getElementById('ajax-loading-message')) {
                    const loadingMessage = document.createElement('div');
                    loadingMessage.id = 'ajax-loading-message';
                    loadingMessage.className = 'ajax-loading-message';
                    loadingMessage.innerHTML = '<span class="spinner"></span> Loading...';
                    
                    // Add to the top of the content container
                    contentContainer.insertBefore(loadingMessage, contentContainer.firstChild);
                }
                
                log('Showing loading indicator');
            }
        }
    }
    
    /**
     * Hide the loading indicator
     */
    function hideLoadingIndicator() {
        if (contentContainer) {
            contentContainer.classList.remove(config.loadingClass);
            
            // Remove the loading message if it exists
            const loadingMessage = document.getElementById('ajax-loading-message');
            if (loadingMessage && loadingMessage.parentNode) {
                loadingMessage.parentNode.removeChild(loadingMessage);
            }
            
            log('Hiding loading indicator');
        }
    }
    
    /**
     * Process the new content and update the container
     * @param {string} html The HTML content
     * @param {string} url The URL that was fetched
     */
    function processNewContent(html, url) {
        if (!contentContainer) {
            log('No content container available', true);
            return;
        }
        
        // Parse the HTML
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        
        // Find the corresponding content in the new page
        let newContent = null;
        
        // For recipe gallery, try to find the recipe-grid specifically
        if (window.location.pathname.includes('/recipes/')) {
            log('Processing recipe gallery content');
            // First try to find the recipe grid
            newContent = doc.querySelector('.recipe-grid');
            // Also get pagination if available
            const newPagination = doc.querySelector('.pagination');
            if (newPagination) {
                // Store pagination for later use
                window.newPaginationContent = newPagination.outerHTML;
            }
        }
        
        // If not found or not on recipe page, use standard approach
        if (!newContent) {
            // Try to find the same container by ID or class
            if (contentContainer.id) {
                newContent = doc.getElementById(contentContainer.id);
            }
            
            if (!newContent && contentContainer.className) {
                // Try each class individually
                const classes = contentContainer.className.split(' ');
                for (const className of classes) {
                    if (className && className !== config.loadingClass) {
                        const elements = doc.querySelectorAll('.' + className);
                        if (elements.length === 1) {
                            newContent = elements[0];
                            break;
                        }
                    }
                }
            }
            
            // If we couldn't find an exact match, look for common content containers
            if (!newContent) {
                for (const selector of config.contentSelectors) {
                    newContent = doc.querySelector(selector);
                    if (newContent) break;
                }
            }
        }
        
        // Update the container with the new content
        if (newContent) {
            // Special handling for recipe gallery
            if (window.location.pathname.includes('/recipes/')) {
                // Create a temporary container for the new content
                const tempContainer = document.createElement('div');
                tempContainer.innerHTML = newContent.innerHTML;
                tempContainer.style.opacity = '0';
                
                // Replace content with a smooth transition
                contentContainer.style.transition = 'opacity 0.1s ease-out';
                contentContainer.style.opacity = '0';
                
                // Short timeout to allow opacity transition to complete
                setTimeout(() => {
                    // Update recipe grid content
                    contentContainer.innerHTML = newContent.innerHTML;
                    
                    // Update pagination if we have it
                    if (window.newPaginationContent) {
                        const paginationContainer = document.querySelector('.pagination');
                        if (paginationContainer) {
                            paginationContainer.outerHTML = window.newPaginationContent;
                            // Clear the stored pagination
                            window.newPaginationContent = null;
                        }
                    }
                    
                    // Update records info if present
                    const newRecordsInfo = doc.querySelector('.records-info');
                    const currentRecordsInfo = document.querySelector('.records-info');
                    if (newRecordsInfo && currentRecordsInfo) {
                        currentRecordsInfo.innerHTML = newRecordsInfo.innerHTML;
                    }
                    
                    // Fade content back in
                    contentContainer.style.opacity = '1';
                }, 50);
                
                // After the content is updated, preserve filter selections
                setTimeout(() => {
                    const parsedUrl = new URL(url, window.location.origin);
                    const params = new URLSearchParams(parsedUrl.search);
                    
                    // Update style filter
                    const styleFilter = document.querySelector('#style-filter');
                    if (styleFilter && params.has('style')) {
                        styleFilter.value = params.get('style');
                    }
                    
                    // Update diet filter
                    const dietFilter = document.querySelector('#diet-filter');
                    if (dietFilter && params.has('diet')) {
                        dietFilter.value = params.get('diet');
                    }
                    
                    // Update type filter
                    const typeFilter = document.querySelector('#type-filter');
                    if (typeFilter && params.has('type')) {
                        typeFilter.value = params.get('type');
                    }
                    
                    // Update sort filter
                    const sortFilter = document.querySelector('#sort');
                    if (sortFilter && params.has('sort')) {
                        sortFilter.value = params.get('sort');
                    }
                }, 60);
            } else {
                // Standard content update for other pages
                contentContainer.innerHTML = newContent.innerHTML;
            }
            
            // Update browser history
            updateHistory(url);
            
            // Re-initialize pagination for the new content
            initPagination();
            
            // Re-initialize favorites functionality
            if (window.FlavorConnect && window.FlavorConnect.favorites) {
                window.FlavorConnect.favorites.initButtons();
            }
            
            // Re-initialize favorites page functionality if on favorites page
            if (window.FlavorConnect && window.FlavorConnect.favoritesPage) {
                window.FlavorConnect.favoritesPage.setupEventListeners();
                window.FlavorConnect.favoritesPage.loadFavorites();
            }
            
            // Re-initialize recipe gallery functionality if on recipe gallery page
            if (typeof window.initializeFavorites === 'function') {
                window.initializeFavorites();
            }
            
            log(`Updated content container with new content from ${url}`);
            
            // Scroll to top of content if needed
            if (contentContainer.getBoundingClientRect().top < 0) {
                contentContainer.scrollIntoView({ behavior: 'smooth' });
            }
        } else {
            log('Could not find matching content in the new page', true);
            restoreOriginalContent();
            
            // Fallback to traditional navigation
            window.location.href = url;
        }
    }
    
    /**
     * Restore the original content if something goes wrong
     */
    function restoreOriginalContent() {
        if (contentContainer && originalContent) {
            contentContainer.innerHTML = originalContent;
            log('Restored original content');
        }
    }
    
    /**
     * Show an error message to the user
     * @param {string} message The error message to display
     */
    function showErrorMessage(message) {
        if (contentContainer) {
            const errorMessage = document.createElement('div');
            errorMessage.className = 'error-message';
            errorMessage.textContent = message;
            contentContainer.appendChild(errorMessage);
            
            // Remove error message after a delay
            setTimeout(function() {
                if (errorMessage.parentNode) {
                    errorMessage.parentNode.removeChild(errorMessage);
                }
            }, 5000);
            
            log(`Showing error message: ${message}`, true);
        }
    }
    
    /**
     * Update browser history to maintain back/forward navigation
     * @param {string} url The URL to add to history
     */
    function updateHistory(url) {
        if (window.history && window.history.pushState) {
            // Parse the URL to handle complex query parameters properly
            const parsedUrl = new URL(url, window.location.origin);
            
            // Store the full URL with all query parameters
            window.history.pushState({ url: parsedUrl.href }, '', parsedUrl.href);
            
            // Update active page in pagination links
            updateActivePage(parsedUrl);
            
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
    
    /**
     * Update active page in pagination links based on the URL
     * @param {URL} url The parsed URL object
     */
    function updateActivePage(url) {
        // Get page number from URL
        const params = new URLSearchParams(url.search);
        const currentPage = params.get('page') || '1';
        
        // Update active page in pagination links
        const paginationLinks = document.querySelectorAll('.pagination a');
        
        // First remove active class from all links
        paginationLinks.forEach(link => {
            link.classList.remove('active');
            link.removeAttribute('aria-current');
        });
        
        // Then add active class to current page link
        paginationLinks.forEach(link => {
            // Parse the link URL to get its page parameter
            const linkUrl = new URL(link.href, window.location.origin);
            const linkParams = new URLSearchParams(linkUrl.search);
            let linkPage = linkParams.get('page') || '1';
            
            // Also check for data-page attribute
            if (link.hasAttribute('data-page')) {
                linkPage = link.getAttribute('data-page');
            }
            
            // Compare as strings to avoid type conversion issues
            if (linkPage === currentPage) {
                link.classList.add('active');
                link.setAttribute('aria-current', 'page');
            }
        });
        
        // Update records-info if present
        const recordsInfo = document.querySelector('.records-info');
        if (recordsInfo) {
            log('Updated active page to: ' + currentPage);
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
    }
    
    // Wait for DOM to be fully loaded with a slight delay to ensure all scripts are loaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(initPagination, 50);
        });
    } else {
        setTimeout(initPagination, 50);
    }
    
    // Public API
    return {
        init: initPagination,
        enable: function() {
            initPagination();
            log('AJAX pagination enabled');
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
        },
        getConfig: function() {
            return config;
        }
    };
})();
