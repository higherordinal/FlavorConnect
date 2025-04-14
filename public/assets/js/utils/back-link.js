/**
 * Unified Navigation Component
 * 
 * This script enhances navigation elements:
 * 1. Back links: Uses the href attribute directly to ensure consistent navigation
 * 2. Breadcrumbs: Adds active class to current page breadcrumb
 * 3. Unified navigation: Handles both components together
 * 
 * Works in conjunction with the PHP unified_navigation() function for
 * complete progressive enhancement - everything works without JavaScript.
 * 
 * @author Henry Vaughn
 */

// Add to FlavorConnect namespace
window.FlavorConnect = window.FlavorConnect || {};
window.FlavorConnect.utils = window.FlavorConnect.utils || {};

// Back Link utility
window.FlavorConnect.utils.backLink = (function() {
    'use strict';
    
    /**
     * Initialize the back link functionality
     */
    function initialize() {
        // Handle breadcrumbs
        highlightCurrentBreadcrumb();
        
        // Store the current page in session storage for back navigation
        storeCurrentPageForBackNavigation();
    }
    
    /**
     * Stores the current page URL in session storage for better back navigation
     */
    function storeCurrentPageForBackNavigation() {
        // Get the current page URL
        const currentUrl = window.location.pathname;
        
        // Store previous page before updating
        const currentPage = sessionStorage.getItem('currentPage');
        if (currentPage) {
            sessionStorage.setItem('previousPage', currentPage);
        }
        
        // Update current page
        sessionStorage.setItem('currentPage', currentUrl);
    }
    
    /**
     * Highlights the current page in breadcrumbs if not already highlighted
     */
    function highlightCurrentBreadcrumb() {
        const breadcrumbs = document.querySelectorAll('.breadcrumbs');
        
        breadcrumbs.forEach(function(breadcrumbContainer) {
            // Get all breadcrumb items
            const items = breadcrumbContainer.querySelectorAll('.breadcrumb-item');
            
            // If no items have the active class, mark the last one as active
            const hasActive = Array.from(items).some(item => 
                item.classList.contains('breadcrumb-active'));
                
            if (!hasActive && items.length > 0) {
                const lastItem = items[items.length - 1];
                lastItem.classList.add('breadcrumb-active');
                // Also add aria-current attribute for accessibility
                lastItem.setAttribute('aria-current', 'page');
                
                // If it's a link, convert it to a span
                if (lastItem.tagName === 'A') {
                    const span = document.createElement('span');
                    span.className = lastItem.className;
                    span.textContent = lastItem.textContent;
                    lastItem.parentNode.replaceChild(span, lastItem);
                }
            }
        });
    }
    // Wait for DOM to be fully loaded
    document.addEventListener('DOMContentLoaded', initialize);
    
    // Return public API
    return {
        init: initialize,
        highlightBreadcrumbs: highlightCurrentBreadcrumb,
        storePage: storeCurrentPageForBackNavigation
    };
})();
