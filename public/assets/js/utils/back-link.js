/**
 * Unified Navigation Component
 * 
 * This script enhances navigation elements:
 * 1. Back links: Uses the href attribute directly to ensure consistent navigation
 * 2. Breadcrumbs: Adds active class to current page breadcrumb
 * 3. Unified navigation: Handles both components together
 * 
 * IMPORTANT: Dual Approach to Back Navigation
 * This JavaScript works in conjunction with PHP server-side navigation:
 * - PHP: Uses $_SESSION['from_recipe_id'] for server-side back link generation
 * - JS: Uses sessionStorage.fromRecipeId for client-side link enhancement
 * 
 * This dual approach ensures consistent navigation with or without JavaScript.
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
        
        // Enhance back links with recipe context
        enhanceBackLinks();
    }
    
    /**
     * Enhances back links to preserve recipe and user context
     * 
     * This function adds context to back links when navigating away from recipe or user pages.
     * It works alongside the PHP back-link system which uses the ref_page parameter.
     */
    function enhanceBackLinks() {
        const backLinks = document.querySelectorAll('.back-link');
        
        backLinks.forEach(function(link) {
            // Check if the link already has data attributes from PHP
            const hasRefPage = link.hasAttribute('data-ref-page');
            const hasRecipeId = link.hasAttribute('data-recipe-id');
            const hasUserId = link.hasAttribute('data-user-id');
            
            // Only add event listener if we don't already have data attributes
            if (!hasRefPage && !hasRecipeId && !hasUserId) {
                link.addEventListener('click', function(e) {
                    const href = link.getAttribute('href');
                    if (!href) return;
                    
                    // Get the recipe ID from session storage
                    const fromRecipeId = sessionStorage.getItem('fromRecipeId');
                    
                    // Get the user ID from session storage
                    const fromUserId = sessionStorage.getItem('fromUserId');
                    
                    // If we have a recipe ID and the link doesn't already have recipe context
                    if (fromRecipeId && !href.includes('recipe_id=') && !href.includes('/recipes/show.php?id=')) {
                        // Modify the link to include the recipe context
                        e.preventDefault();
                        
                        // Add the recipe_id parameter to the URL
                        const separator = href.includes('?') ? '&' : '?';
                        window.location.href = href + separator + 'ref_page=/recipes/show.php&recipe_id=' + fromRecipeId;
                        return;
                    }
                    
                    // If we have a user ID and the link doesn't already have user context
                    if (fromUserId && !href.includes('user_id=') && !href.includes('/admin/users/edit.php?user_id=') && !href.includes('/admin/users/delete.php?user_id=')) {
                        // Modify the link to include the user context
                        e.preventDefault();
                        
                        // Add the user_id parameter to the URL
                        const separator = href.includes('?') ? '&' : '?';
                        const refPage = href.includes('/admin/users/delete.php') ? '/admin/users/delete.php' : '/admin/users/edit.php';
                        window.location.href = href + separator + 'ref_page=' + refPage + '&user_id=' + fromUserId;
                        return;
                    }
                    
                    // If we have category context and the link doesn't already have category context
                    const fromCategoryId = sessionStorage.getItem('fromCategoryId');
                    const fromCategoryType = sessionStorage.getItem('fromCategoryType');
                    
                    if (fromCategoryId && fromCategoryType && 
                        !href.includes('category_id=') && 
                        !href.includes('category_type=') && 
                        !href.includes('/admin/categories/' + fromCategoryType)) {
                        // Modify the link to include the category context
                        e.preventDefault();
                        
                        // Determine the reference page based on the category type and current URL
                        const actionType = determineActionType(currentUrl);
                        const refPage = '/admin/categories/' + fromCategoryType + '/' + actionType + '.php';
                        
                        // Add the category parameters to the URL
                        const separator = href.includes('?') ? '&' : '?';
                        window.location.href = href + separator + 'ref_page=' + refPage + 
                                               '&category_id=' + fromCategoryId + 
                                               '&category_type=' + fromCategoryType;
                    }
                });
            }
        });
    }
    
    /**
     * Stores the current page URL and context in session storage for better back navigation
     * 
     * This function maintains client-side navigation state that complements the server-side
     * state managed by PHP. The recipe ID is stored with the same naming convention as
     * the PHP session for consistency.
     */
    function storeCurrentPageForBackNavigation() {
        // Get the current page URL and search params
        const currentUrl = window.location.pathname;
        const searchParams = new URLSearchParams(window.location.search);
        
        // Store previous page before updating
        const currentPage = sessionStorage.getItem('currentPage');
        if (currentPage) {
            sessionStorage.setItem('previousPage', currentPage);
        }
        
        // Update current page
        sessionStorage.setItem('currentPage', currentUrl);
        
        // Store recipe context if available
        const recipeId = searchParams.get('id');
        if (recipeId && currentUrl.includes('/recipes/show.php')) {
            // Store in session storage for client-side use
            // Use fromRecipeId to match PHP's $_SESSION['from_recipe_id'] naming convention
            sessionStorage.setItem('fromRecipeId', recipeId);
            // Note: PHP session is updated directly in recipes/show.php when it loads
            // This ensures synchronization between client and server without AJAX
        }
        
        // Store user context if available
        const userId = searchParams.get('user_id');
        if (userId && (currentUrl.includes('/admin/users/edit.php') || currentUrl.includes('/admin/users/delete.php'))) {
            // Store in session storage for client-side use
            sessionStorage.setItem('fromUserId', userId);
        }
        
        // Store category context if available
        const categoryId = searchParams.get('id');
        const categoryType = currentUrl.includes('/admin/categories/') ? extractCategoryTypeFromPath(currentUrl) : null;
        
        if (categoryId && categoryType) {
            // Store both category ID and type in session storage
            sessionStorage.setItem('fromCategoryId', categoryId);
            sessionStorage.setItem('fromCategoryType', categoryType);
        }
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
    
    /**
     * Extracts the category type from the current path
     * 
     * @param {string} path - The current URL path
     * @return {string|null} - The category type or null if not found
     */
    function extractCategoryTypeFromPath(path) {
        // Check for each category type in the path
        const categoryTypes = ['diet', 'style', 'type', 'measurement'];
        
        for (const type of categoryTypes) {
            if (path.includes('/admin/categories/' + type + '/')) {
                return type;
            }
        }
        
        return null;
    }
    
    /**
     * Determines the action type from the current URL
     * 
     * @param {string} url - The current URL
     * @return {string} - The action type (edit, delete, or index)
     */
    function determineActionType(url) {
        if (url.includes('/edit.php')) {
            return 'edit';
        } else if (url.includes('/delete.php')) {
            return 'delete';
        } else {
            return 'index';
        }
    }
    
    // Return public API
    return {
        init: initialize,
        highlightBreadcrumbs: highlightCurrentBreadcrumb,
        storePage: storeCurrentPageForBackNavigation,
        extractCategoryType: extractCategoryTypeFromPath
    };
})();
