/**
 * FlavorConnect Favorites Utility
 * Provides functions for managing recipe favorites
 * @author Henry Vaughn
 * @version 1.1.0
 */

'use strict';

// Initialize FlavorConnect namespace
window.FlavorConnect = window.FlavorConnect || {};

// Favorites utility functions
window.FlavorConnect.favorites = (function() {
    // Private variables and functions
    const config = {
        baseUrl: window.FlavorConnect.config ? window.FlavorConnect.config.baseUrl : '/',
        isLoggedIn: window.FlavorConnect.config ? window.FlavorConnect.config.isLoggedIn : false
    };
    
    // Track button processing state to prevent multiple rapid clicks
    const processingButtons = new Map();
    
    /**
     * Update button appearance based on favorite status
     * @param {HTMLElement} button - The button to update
     * @param {boolean} isFavorited - Whether the recipe is favorited
     */
    function updateButtonState(button, isFavorited) {
        // Update button class
        button.classList.toggle('favorited', isFavorited);
        
        // Update icon
        const icon = button.querySelector('i');
        if (icon) {
            if (isFavorited) {
                icon.classList.remove('far');
                icon.classList.add('fas');
            } else {
                icon.classList.remove('fas');
                icon.classList.add('far');
            }
        }
        
        // Update aria-label
        const actionText = isFavorited ? 'Remove from' : 'Add to';
        button.setAttribute('aria-label', `${actionText} favorites`);
    }
    
    // Public API
    return {
        /**
         * Initialize favorite buttons on the page
         */
        initButtons: function() {
            const buttons = document.querySelectorAll('.favorite-btn:not([data-initialized])');
            
            buttons.forEach(button => {
                const recipeId = button.dataset.recipeId;
                if (!recipeId) return;
                
                // Check if we already have the favorite status in the config
                if (window.FlavorConnect && window.FlavorConnect.config && typeof window.FlavorConnect.config.isFavorited !== 'undefined') {
                    // Use the value from the config directly
                    updateButtonState(button, window.FlavorConnect.config.isFavorited);
                } else {
                    // Fall back to checking status via API
                    this.checkStatus(recipeId)
                        .then(isFavorited => {
                            // Update button state based on favorite status
                            updateButtonState(button, isFavorited);
                        })
                        .catch(error => {/* Silent error handling */});
                }
                
                // Mark as initialized
                button.dataset.initialized = 'true';
                
                // Add click handler with debouncing
                button._clickHandler = async (e) => {
                    e.preventDefault();
                    
                    // Prevent rapid successive clicks
                    if (processingButtons.get(recipeId)) {
                        return; // Ignore clicks while processing
                    }
                    
                    // Mark this button as processing
                    processingButtons.set(recipeId, true);
                    
                    // Add visual feedback
                    button.classList.add('processing');
                    
                    // Optimistically update the UI immediately
                    const currentState = button.classList.contains('favorited');
                    updateButtonState(button, !currentState);
                    
                    try {
                        const result = await this.toggle(recipeId);
                        
                        if (result.success) {
                            // Update button state using the shared function
                            updateButtonState(button, result.isFavorited);
                        } else {
                            // Revert to original state if there was an error
                            updateButtonState(button, currentState);
                        }
                    } catch (error) {
                        // Revert to original state if there was an error
                        updateButtonState(button, currentState);
                    } finally {
                        // Remove processing state after a shorter delay
                        setTimeout(() => {
                            processingButtons.set(recipeId, false);
                            button.classList.remove('processing');
                        }, 250);
                    }
                };
                
                button.addEventListener('click', button._clickHandler);
            });
        },
    
        /**
         * Check if a recipe is favorited by the current user
         * @param {number} recipeId - The ID of the recipe to check
         * @returns {Promise<boolean>} - Whether the recipe is favorited
         */
        checkStatus: async function(recipeId) {
            try {
                if (!config.isLoggedIn) return false;
                
                // Use the same robust URL construction as the toggle function
                let apiUrl;
                
                // Get the base URL from the config if available
                if (window.FlavorConnect && window.FlavorConnect.config && window.FlavorConnect.config.baseUrl) {
                    apiUrl = window.FlavorConnect.config.baseUrl;
                    // Remove trailing slash if present
                    if (apiUrl.endsWith('/')) {
                        apiUrl = apiUrl.slice(0, -1);
                    }
                } else {
                    // Fallback to constructing the URL manually
                    const protocol = window.location.protocol;
                    const host = window.location.host;
                    apiUrl = `${protocol}//${host}`;
                    
                    // For XAMPP, we need to include the project folder in the path
                    if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
                        // Extract the project folder from the current path
                        const pathParts = window.location.pathname.split('/');
                        if (pathParts.length > 1 && pathParts[1]) {
                            apiUrl += `/${pathParts[1]}`;
                        }
                    }
                }
                
                // Construct the full API URL with the recipe_id parameter
                const fullUrl = `${apiUrl}/api/toggle_favorite.php?recipe_id=${recipeId}`;
            
                // Add cache-busting parameter to prevent caching
                const cacheBuster = new Date().getTime();
                const finalUrl = `${fullUrl}&_=${cacheBuster}`;
                
                const response = await fetch(finalUrl, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Cache-Control': 'no-cache, no-store, must-revalidate',
                        'Pragma': 'no-cache',
                        'Expires': '0'
                    },
                    // Ensure credentials are included for proper session handling
                    credentials: 'same-origin',
                    // Add cache control to prevent caching issues
                    cache: 'no-store'
                });
                
                // Handle unauthorized response (user not logged in)
                if (response.status === 401) {
                    return false;
                }
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    const text = await response.text();
                    return false;
                }
                
                const data = await response.json();
                return data.is_favorited === true;
            } catch (error) {
                return false;
            }
        },
        
        /**
         * Toggle the favorite status of a recipe
         * @param {number} recipeId - The ID of the recipe to toggle
         * @returns {Promise<Object>} - Object with success and isFavorited properties
         */
        toggle: async function(recipeId) {
            try {
                if (!window.FlavorConnect.config.isLoggedIn) {
                    window.location.href = `${window.FlavorConnect.config.baseUrl}login.php`;
                    return { success: false, error: 'User not logged in' };
                }
                
                // Ensure we're using a clean URL without any navigation parameters
                let apiUrl;
                
                // Get the base URL from the config if available
                if (window.FlavorConnect && window.FlavorConnect.config && window.FlavorConnect.config.baseUrl) {
                    apiUrl = window.FlavorConnect.config.baseUrl;
                    // Remove trailing slash if present
                    if (apiUrl.endsWith('/')) {
                        apiUrl = apiUrl.slice(0, -1);
                    }
                } else {
                    // Fallback to constructing the URL manually
                    const protocol = window.location.protocol;
                    const host = window.location.host;
                    apiUrl = `${protocol}//${host}`;
                    
                    // For XAMPP, we need to include the project folder in the path
                    if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
                        // Extract the project folder from the current path
                        const pathParts = window.location.pathname.split('/');
                        if (pathParts.length > 1 && pathParts[1]) {
                            apiUrl += `/${pathParts[1]}`;
                        }
                    }
                }
                
                // Construct the full API URL - the API is directly in the /api directory
                const fullApiUrl = `${apiUrl}/api/toggle_favorite.php`;
                
                const response = await fetch(fullApiUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    // Add cache control to prevent caching issues
                    cache: 'no-store',
                    // Include credentials to ensure cookies are sent
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        recipe_id: recipeId
                    })
                });
                
                // Handle unauthorized response (user not logged in)
                if (response.status === 401) {
                    window.location.href = `${window.FlavorConnect.config.baseUrl}login.php`;
                    return { success: false, error: 'User not logged in' };
                }
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                // Check if the response is JSON
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    const text = await response.text();
                    
                    // Try to extract error message from HTML
                    let errorMessage = 'Invalid response format';
                    try {
                        // Look for PHP error message in the HTML
                        const errorMatch = text.match(/\<b\>([^\<]+)\<\/b\>/);
                        if (errorMatch && errorMatch[1]) {
                            errorMessage = errorMatch[1];
                        }
                    } catch (e) {
                        // Silent catch - we'll use the default error message
                    }
                    
                    return { 
                        success: false, 
                        error: errorMessage,
                        html_response: true
                    };
                }
                
                try {
                    const data = await response.json();
                    
                    // Check if we're on the favorites page and need to remove the card
                    if (data.success === true && data.is_favorited === false) {
                        // If we're on the favorites page, check if we need to remove the recipe card
                        const currentPath = window.location.pathname;
                        // More robust check for favorites page that works in both development and production
                        if (currentPath.includes('/users/favorites.php') || currentPath.endsWith('/favorites.php')) {
                            // Find the recipe card for this recipe
                            const button = document.querySelector(`.favorite-btn[data-recipe-id="${recipeId}"]`);
                            if (button && window.FlavorConnect.favoritesPage) {
                                // Use the favoritesPage.removeRecipeCard method to remove it with animation
                                window.FlavorConnect.favoritesPage.removeRecipeCard(button);
                            }
                        }
                    }
                    
                    return {
                        success: data.success === true,
                        isFavorited: data.is_favorited === true
                    };
                } catch (jsonError) {
                    return { 
                        success: false, 
                        error: 'Failed to parse JSON response' 
                    };
                }
            } catch (error) {
                return { success: false, error: error.message };
            }
        },
        
        /**
         * Get all favorite recipes for the current user
         * @param {string} sortBy - Sort parameter (date, name, etc.)
         * @returns {Promise<Array>} Array of favorite recipe objects
         */
        getAllFavorites: async function(sortBy = 'date') {
            try {
                if (!config.isLoggedIn) return [];
            
                // In this implementation, we're relying on the server-side rendering
                // to provide the favorites data directly in the DOM
                
                // Get recipe IDs from the DOM
                const favoriteButtons = document.querySelectorAll('.favorite-btn.favorited, .unfavorite-btn');
                const favorites = Array.from(favoriteButtons).map(button => {
                    const recipeCard = button.closest('.recipe-card');
                    if (!recipeCard) return null;
                    
                    const recipeId = button.dataset.recipeId;
                    const titleEl = recipeCard.querySelector('.recipe-title');
                    const imageEl = recipeCard.querySelector('.recipe-image');
                    
                    return {
                        recipe_id: recipeId,
                        title: titleEl ? titleEl.textContent : '',
                        img_file_path: imageEl ? imageEl.src : '',
                        is_favorited: true
                    };
                }).filter(f => f !== null);
                
                return favorites;
            } catch (error) {
                return [];
            }
        },
        
        /**
         * Create empty state HTML for favorites page
         * @returns {string} HTML for empty state
         */
        createEmptyState: function() {
            return `
                <div class="empty-state">
                    <i class="far fa-heart empty-icon"></i>
                    <h3>No Favorite Recipes Yet</h3>
                    <p>Explore recipes and click the heart icon to add them to your favorites.</p>
                    <a href="${config.baseUrl}recipes" class="btn btn-primary">Explore Recipes</a>
                </div>
            `;
        }
    };
})();

// For backward compatibility (DEPRECATED - use window.FlavorConnect.favorites.initButtons instead)
window.initializeFavoriteButtons = function() {
    if (window.FlavorConnect && window.FlavorConnect.favorites) {
        window.FlavorConnect.favorites.initButtons();
    }
};

// For backward compatibility (DEPRECATED - use window.FlavorConnect.favorites.toggle instead)
window.toggleFavorite = async function(recipeId) {
    if (window.FlavorConnect && window.FlavorConnect.favorites) {
        return await window.FlavorConnect.favorites.toggle(recipeId);
    } else {
        return { success: false, error: 'Favorite functionality not available' };
    }
};

// Initialize buttons when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    if (window.FlavorConnect && window.FlavorConnect.favorites) {
        window.FlavorConnect.favorites.initButtons();
    }
});

// Also listen for custom event that can be triggered after AJAX content loads
document.addEventListener('flavorconnect:content-updated', function(event) {
    if (window.FlavorConnect && window.FlavorConnect.favorites) {
        // Clear initialization flags
        document.querySelectorAll('.favorite-btn[data-initialized]').forEach(btn => {
            delete btn.dataset.initialized;
        });
        // Reinitialize
        window.FlavorConnect.favorites.initButtons();
    }
});
