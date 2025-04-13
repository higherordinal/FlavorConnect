/**
 * Unified Navigation Component
 * 
 * This script enhances navigation elements:
 * 1. Back links: Uses browser history when possible or falls back to href
 * 2. Breadcrumbs: Adds active class to current page breadcrumb
 * 3. Unified navigation: Handles both components together
 * 
 * Works in conjunction with the PHP unified_navigation() function for
 * complete progressive enhancement - everything works without JavaScript.
 */

(function() {
    'use strict';
    
    // Wait for DOM to be fully loaded
    document.addEventListener('DOMContentLoaded', function() {
        // Handle back links
        enhanceBackLinks();
        
        // Handle breadcrumbs
        highlightCurrentBreadcrumb();
    });
    
    /**
     * Enhances back links with browser history functionality
     */
    function enhanceBackLinks() {
        // Find all back-link elements
        const backLinks = document.querySelectorAll('.back-link');
        
        // Process each back link
        backLinks.forEach(function(link) {
            // Skip if the link has data-force-href attribute
            if (link.hasAttribute('data-force-href')) {
                return;
            }
            
            // Add click event listener
            link.addEventListener('click', function(event) {
                // If we have history and we're not on the first page
                if (window.history.length > 1) {
                    event.preventDefault();
                    window.history.back();
                }
                // Otherwise, the default link behavior will be used (PHP fallback)
            });
        });
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
})();
