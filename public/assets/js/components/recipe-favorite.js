/**
 * FlavorConnect Favorites Utility
 * Provides functions for managing recipe favorites
 */

// Initialize FlavorConnect namespace
window.FlavorConnect = window.FlavorConnect || {};

// Favorites utility functions
window.FlavorConnect.favorites = {
    /**
     * Initialize favorite buttons on the page
     */
    initButtons: function() {
        const buttons = document.querySelectorAll('.favorite-btn:not([data-initialized])');
        
        buttons.forEach(button => {
            const recipeId = button.dataset.recipeId;
            if (!recipeId) return;
            
            // Check initial favorite status
            this.checkStatus(recipeId)
                .then(isFavorited => {
                    // Update button state based on favorite status
                    if (isFavorited) {
                        button.classList.add('favorited');
                        const icon = button.querySelector('i');
                        if (icon) {
                            icon.classList.remove('far');
                            icon.classList.add('fas');
                        }
                        // Set aria-label for favorited state
                        button.setAttribute('aria-label', 'Remove from favorites');
                    } else {
                        // Ensure aria-label is set for non-favorited state
                        button.setAttribute('aria-label', 'Add to favorites');
                    }
                })
                .catch(error => console.error('Error checking favorite status:', error));
            
            // Mark as initialized
            button.dataset.initialized = 'true';
            
            // Add click handler
            button.addEventListener('click', async (e) => {
                e.preventDefault();
                
                try {
                    const result = await this.toggle(recipeId);
                    
                    if (result.success) {
                        // Update button state
                        button.classList.toggle('favorited', result.isFavorited);
                        
                        // Update icon
                        const icon = button.querySelector('i');
                        if (icon) {
                            if (result.isFavorited) {
                                icon.classList.remove('far');
                                icon.classList.add('fas');
                            } else {
                                icon.classList.remove('fas');
                                icon.classList.add('far');
                            }
                        }
                        
                        // Update aria-label
                        const actionText = result.isFavorited ? 'Remove from' : 'Add to';
                        button.setAttribute('aria-label', `${actionText} favorites`);
                    } else if (result.error) {
                        console.error('Failed to toggle favorite:', result.error);
                    }
                } catch (error) {
                    console.error('Error toggling favorite:', error);
                }
            });
        });
    },
    
    /**
     * Check if a recipe is favorited by the current user
     * @param {number} recipeId - The ID of the recipe to check
     * @returns {Promise<boolean>} - Whether the recipe is favorited
     */
    checkStatus: async function(recipeId) {
        try {
            if (!window.FlavorConnect.config.isLoggedIn) return false;
            
            // Use the toggle_favorite.php endpoint with GET method
            const baseUrl = window.FlavorConnect.config.baseUrl;
            // Remove trailing slash if present
            const url = baseUrl.endsWith('/') ? baseUrl.slice(0, -1) : baseUrl;
            const fullUrl = `${url}/api/toggle_favorite.php?recipe_id=${recipeId}`;
            console.log('Checking favorite status URL:', fullUrl);
            
            // Add cache-busting parameter to prevent caching
            const cacheBuster = new Date().getTime();
            const finalUrl = `${fullUrl}&_=${cacheBuster}`;
            
            const response = await fetch(finalUrl, {
                headers: {
                    'Accept': 'application/json',
                    'Cache-Control': 'no-cache, no-store, must-revalidate',
                    'Pragma': 'no-cache',
                    'Expires': '0'
                }
            });
            
            // Log the response headers for debugging
            console.log('Response status:', response.status);
            
            // Handle unauthorized response (user not logged in)
            if (response.status === 401) {
                console.log('User not logged in, returning false for favorite status');
                return false;
            }
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                console.error('Response is not JSON:', contentType);
                const text = await response.text();
                console.error('Response text:', text);
                return false;
            }
            
            const data = await response.json();
            console.log('Check status response data:', data);
            return data.is_favorited === true;
        } catch (error) {
            console.error('Error checking favorite status:', error);
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

            // Use the toggle_favorite.php endpoint with POST method
            const baseUrl = window.FlavorConnect.config.baseUrl;
            // Remove trailing slash if present
            const url = baseUrl.endsWith('/') ? baseUrl.slice(0, -1) : baseUrl;
            const fullUrl = `${url}/api/toggle_favorite.php`;
            console.log('Toggle favorite URL:', fullUrl);

            // Add cache-busting parameter to prevent caching
            const cacheBuster = new Date().getTime();
            const finalUrl = `${fullUrl}?_=${cacheBuster}`;

            // Create a simple JSON payload instead of URLSearchParams
            const payload = JSON.stringify({ recipe_id: recipeId });

            const response = await fetch(finalUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Cache-Control': 'no-cache, no-store, must-revalidate',
                    'Pragma': 'no-cache',
                    'Expires': '0'
                },
                body: payload
            });

            // Log the response headers for debugging
            console.log('Response status:', response.status);
            console.log('Response headers:', response.headers);
            
            // Handle unauthorized response (user not logged in)
            if (response.status === 401) {
                console.log('User not logged in, redirecting to login page');
                window.location.href = `${window.FlavorConnect.config.baseUrl}login.php`;
                return { success: false, error: 'User not logged in' };
            }
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            // Check if the response is JSON
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                console.error('Response is not JSON:', contentType);
                const text = await response.text();
                console.error('Response text:', text);
                
                // Try to extract error message from HTML
                let errorMessage = 'Invalid response format';
                try {
                    // Look for PHP error message in the HTML
                    const errorMatch = text.match(/<b>([^<]+)<\/b>/);
                    if (errorMatch && errorMatch[1]) {
                        errorMessage = errorMatch[1];
                    }
                } catch (e) {
                    console.error('Error parsing HTML error:', e);
                }
                
                return { 
                    success: false, 
                    error: errorMessage,
                    html_response: true
                };
            }

            try {
                const data = await response.json();
                console.log('Toggle response data:', data);
                
                return {
                    success: data.success === true,
                    isFavorited: data.is_favorited === true
                };
            } catch (jsonError) {
                console.error('Error parsing JSON:', jsonError);
                return { 
                    success: false, 
                    error: 'Failed to parse JSON response' 
                };
            }
        } catch (error) {
            console.error('Error toggling favorite:', error);
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
            if (!window.FlavorConnect.config.isLoggedIn) return [];
            
            // In this implementation, we're relying on the server-side rendering
            // to provide the favorites data directly in the DOM
            console.log('Getting favorites from DOM');
            
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
            
            console.log('Found favorites in DOM:', favorites.length);
            return favorites;
        } catch (error) {
            console.error('Error getting favorites:', error);
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
                <a href="${window.FlavorConnect.config.baseUrl}recipes" class="btn btn-primary">Explore Recipes</a>
            </div>
        `;
    }
};

// For backward compatibility (DEPRECATED - use window.FlavorConnect.favorites.initButtons instead)
window.initializeFavoriteButtons = function() {
    console.warn('DEPRECATED: window.initializeFavoriteButtons is deprecated. Use window.FlavorConnect.favorites.initButtons instead.');
    if (window.FlavorConnect && window.FlavorConnect.favorites) {
        window.FlavorConnect.favorites.initButtons();
    }
};

// For backward compatibility (DEPRECATED - use window.FlavorConnect.favorites.toggle instead)
window.toggleFavorite = async function(recipeId) {
    console.warn('DEPRECATED: window.toggleFavorite is deprecated. Use window.FlavorConnect.favorites.toggle instead.');
    if (window.FlavorConnect && window.FlavorConnect.favorites) {
        return await window.FlavorConnect.favorites.toggle(recipeId);
    } else {
        console.error('Favorite functionality not available');
        return { success: false, error: 'Favorite functionality not available' };
    }
};

// Initialize buttons when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    if (window.FlavorConnect && window.FlavorConnect.favorites) {
        window.FlavorConnect.favorites.initButtons();
    }
});
