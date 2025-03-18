/**
 * @fileoverview User Favorites page functionality for FlavorConnect
 * @author Henry Vaughn
 * @version 1.2.0
 * @license MIT
 */

// Initialize FlavorConnect namespace
window.FlavorConnect = window.FlavorConnect || {};

// Create favorites page namespace
window.FlavorConnect.favoritesPage = {
    /**
     * Initializes the favorites page functionality
     */
    init: function() {
        console.log('Initializing favorites page');
        
        // Make sure we have the config object
        if (!window.FlavorConnect.config) {
            window.FlavorConnect.config = {
                baseUrl: '/',
                isLoggedIn: true
            };
        }
        
        this.setupEventListeners();
        this.loadFavorites();
    },

    /**
     * Sets up event listeners for the page
     */
    setupEventListeners: function() {
        console.log('Setting up event listeners for favorites page');
        
        // Handle unfavorite buttons (both dedicated unfavorite buttons and regular favorite buttons)
        document.querySelectorAll('.favorite-btn.favorited').forEach(button => {
            console.log('Found favorite button with recipe ID:', button.dataset.recipeId);
            
            // Remove any existing event listeners (not perfect but helps)
            button.removeEventListener('click', this.handleUnfavorite.bind(this));
            
            // Add new event listener
            const boundHandler = this.handleUnfavorite.bind(this);
            button.addEventListener('click', boundHandler);
            
            // Mark the button as having a listener
            button._hasUnfavoriteListener = true;
        });

        // Handle sort select
        const sortSelect = document.querySelector('#sort-select');
        if (sortSelect) {
            sortSelect.addEventListener('change', this.handleSort.bind(this));
        }

        // Make sure favorite buttons are initialized
        if (window.FlavorConnect.favorites && typeof window.FlavorConnect.favorites.initButtons === 'function') {
            window.FlavorConnect.favorites.initButtons();
        }
        
        // Add a global event listener for any dynamically added favorite buttons
        document.addEventListener('click', (event) => {
            const button = event.target.closest('.favorite-btn.favorited');
            if (button && !button._hasUnfavoriteListener) {
                console.log('Handling click on dynamically added favorite button');
                this.handleUnfavorite.call(this, { 
                    preventDefault: () => {},
                    stopPropagation: () => {},
                    currentTarget: button 
                });
                event.preventDefault();
                event.stopPropagation();
                
                // Mark the button as having been handled
                button._hasUnfavoriteListener = true;
            }
        });
        
        // Legacy support for old favorites.js code
        // This ensures compatibility with any code that might be expecting the old behavior
        document.querySelectorAll('.favorite-btn').forEach(button => {
            // Only add this handler if it's not already handled by our main handler
            if (!button._hasUnfavoriteListener) {
                button.addEventListener('click', async (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const recipeId = button.dataset.recipeId;
                    if (!recipeId) return;
                    
                    let result;
                    
                    if (typeof window.toggleFavorite === 'function') {
                        result = await window.toggleFavorite(recipeId);
                    } else {
                        console.error('toggleFavorite function not available');
                        return;
                    }
                    
                    if (result && result.success) {
                        // If unfavorited from favorites page, remove the card with animation
                        const card = button.closest('.recipe-card');
                        if (card && !result.isFavorited) {
                            this.removeRecipeCard(button);
                            
                            // Check for empty state
                            this.updateEmptyState();
                        } else if (button) {
                            // Update button appearance
                            if (result.isFavorited) {
                                button.classList.add('favorited');
                                const icon = button.querySelector('i');
                                if (icon) {
                                    icon.classList.remove('far');
                                    icon.classList.add('fas');
                                }
                            } else {
                                button.classList.remove('favorited');
                                const icon = button.querySelector('i');
                                if (icon) {
                                    icon.classList.remove('fas');
                                    icon.classList.add('far');
                                }
                            }
                        }
                    } else {
                        console.error('Failed to toggle favorite:', result ? result.error : 'Unknown error');
                    }
                });
            }
        });
    },

    /**
     * Loads favorite recipes from the server
     */
    loadFavorites: async function() {
        this.showLoadingState(true);
        
        try {
            // Get favorites directly from the DOM
            const favoriteButtons = document.querySelectorAll('.favorite-btn.favorited');
            if (favoriteButtons.length > 0) {
                // We already have favorites loaded in the DOM
                this.showLoadingState(false);
                return;
            }
            
            // Try to use the favorites utility
            if (window.FlavorConnect.favorites && typeof window.FlavorConnect.favorites.getAllFavorites === 'function') {
                const favorites = await window.FlavorConnect.favorites.getAllFavorites();
                this.updateFavoritesList(favorites);
            } else {
                // Fallback to initial data if available
                if (window.initialFavoritesData) {
                    this.updateFavoritesList(window.initialFavoritesData);
                } else {
                    this.showError('Unable to load favorites');
                }
            }
        } catch (error) {
            console.error('Error loading favorites:', error);
            this.showError('Failed to load favorites');
        } finally {
            this.showLoadingState(false);
        }
    },

    /**
     * Handles unfavoriting a recipe
     * @param {Event} event - The click event
     */
    handleUnfavorite: async function(event) {
        event.preventDefault();
        event.stopPropagation();
        
        const button = event.currentTarget;
        const recipeId = button.dataset.recipeId;
        
        if (!recipeId) {
            console.error('No recipe ID found on button');
            return;
        }
        
        try {
            console.log('Unfavoriting recipe ID:', recipeId);
            
            // Use XMLHttpRequest to avoid any caching issues
            const xhr = new XMLHttpRequest();
            
            // Get the base URL
            const baseUrl = window.FlavorConnect.config && window.FlavorConnect.config.baseUrl 
                ? window.FlavorConnect.config.baseUrl 
                : '';
            const url = baseUrl.endsWith('/') ? baseUrl.slice(0, -1) : baseUrl;
            const fullUrl = `${url}/api/toggle_favorite.php?_=${new Date().getTime()}`; // Add cache buster
            
            console.log('Making direct API call to:', fullUrl);
            
            // Set up the request
            xhr.open('POST', fullUrl, true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.setRequestHeader('Cache-Control', 'no-cache, no-store, must-revalidate');
            xhr.setRequestHeader('Pragma', 'no-cache');
            xhr.setRequestHeader('Expires', '0');
            
            // Set up the callback
            xhr.onreadystatechange = () => {
                if (xhr.readyState === 4) {
                    console.log('API response status:', xhr.status);
                    console.log('API response text:', xhr.responseText);
                    
                    if (xhr.status === 200) {
                        try {
                            const result = JSON.parse(xhr.responseText);
                            console.log('API response parsed:', result);
                            
                            if (result.success) {
                                console.log('Successfully processed favorite toggle');
                                // Always remove the card from the favorites page
                                this.removeRecipeCard(button);
                            } else {
                                console.error('Failed to process favorite toggle:', result.error || 'Unknown error');
                            }
                        } catch (parseError) {
                            console.error('Error parsing JSON response:', parseError);
                        }
                    } else {
                        console.error('HTTP error:', xhr.status);
                    }
                }
            };
            
            // Send the request
            xhr.send(`recipe_id=${recipeId}`);
            
            // Remove the card immediately for better UX
            // The API call will happen in the background
            this.removeRecipeCard(button);
            
        } catch (error) {
            console.error('Error unfavoriting recipe:', error);
        }
    },

    /**
     * Direct unfavorite implementation as a fallback
     * @param {string|number} recipeId - The recipe ID to unfavorite
     * @param {HTMLElement} button - The button element that was clicked
     */
    directUnfavorite: async function(recipeId, button) {
        try {
            const baseUrl = window.FlavorConnect && window.FlavorConnect.config && window.FlavorConnect.config.baseUrl 
                ? window.FlavorConnect.config.baseUrl 
                : '';
            
            // Remove trailing slash if present
            const url = baseUrl.endsWith('/') ? baseUrl.slice(0, -1) : baseUrl;
            const fullUrl = `${url}/api/toggle_favorite.php`;
            
            const formData = new FormData();
            formData.append('recipe_id', recipeId);
            
            const response = await fetch(fullUrl, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const contentType = response.headers.get('content-type');
            let result;
            
            if (contentType && contentType.includes('application/json')) {
                result = await response.json();
            } else {
                const text = await response.text();
                try {
                    // Try to parse the response as JSON
                    result = JSON.parse(text);
                } catch (e) {
                    console.error('Failed to parse response as JSON:', text);
                    throw new Error('Invalid response format');
                }
            }
            
            if (result.success && !result.isFavorited) {
                this.removeRecipeCard(button);
            } else {
                console.error('Failed to unfavorite recipe:', result.error || 'Unknown error');
            }
        } catch (error) {
            console.error('Error in directUnfavorite:', error);
            throw error;
        }
    },

    /**
     * Handles sorting of favorite recipes
     * @param {Event} e - Change event from sort select
     */
    handleSort: async function(e) {
        const sortBy = e.target.value;
        this.showLoadingState(true);
        
        try {
            // Try to use the favorites utility
            if (window.FlavorConnect.favorites && typeof window.FlavorConnect.favorites.getAllFavorites === 'function') {
                const favorites = await window.FlavorConnect.favorites.getAllFavorites(sortBy);
                this.updateFavoritesList(favorites);
            } else {
                // Fallback to initial data if available
                if (window.initialFavoritesData) {
                    // Sort the data client-side
                    const sorted = [...window.initialFavoritesData].sort((a, b) => {
                        if (sortBy === 'name') {
                            return a.title.localeCompare(b.title);
                        } else if (sortBy === 'date') {
                            return new Date(b.date_added) - new Date(a.date_added);
                        }
                        return 0;
                    });
                    
                    this.updateFavoritesList(sorted);
                }
            }
        } catch (error) {
            console.error('Error sorting favorites:', error);
            this.showError('Failed to sort favorites');
        } finally {
            this.showLoadingState(false);
        }
    },

    /**
     * Updates the favorites list with new data
     * @param {Array} favorites - Array of favorite recipe objects
     */
    updateFavoritesList: function(favorites) {
        const recipeGrid = document.querySelector('.recipe-grid');
        if (!recipeGrid) return;
        
        // Clear existing recipes
        recipeGrid.innerHTML = '';
        
        if (!favorites || favorites.length === 0) {
            // Show empty state
            const emptyState = this.createEmptyState();
            recipeGrid.insertAdjacentHTML('beforebegin', emptyState);
            recipeGrid.style.display = 'none';
            return;
        }
        
        // Show the grid
        recipeGrid.style.display = 'grid';
        
        // Remove any existing empty state
        const existingEmptyState = document.querySelector('.no-recipes');
        if (existingEmptyState) {
            existingEmptyState.remove();
        }
        
        // Add recipe cards
        favorites.forEach(recipe => {
            const card = this.createRecipeCard(recipe);
            recipeGrid.insertAdjacentHTML('beforeend', card);
        });
        
        // Initialize favorite buttons
        if (window.FlavorConnect.favorites && typeof window.FlavorConnect.favorites.initButtons === 'function') {
            window.FlavorConnect.favorites.initButtons();
        }
    },

    /**
     * Creates HTML for a recipe card
     * @param {Object} recipe - Recipe object
     * @returns {string} HTML string for the recipe card
     */
    createRecipeCard: function(recipe) {
        const baseUrl = window.FlavorConnect.config && window.FlavorConnect.config.baseUrl 
            ? window.FlavorConnect.config.baseUrl 
            : '';
            
        return `
            <article class="recipe-card">
                <div class="recipe-card-image">
                    <img src="${baseUrl}${recipe.image_path || '/assets/images/recipe-placeholder.jpg'}" alt="${recipe.title}" loading="lazy">
                    <button class="favorite-btn favorited" data-recipe-id="${recipe.id}" aria-label="Remove from favorites">
                        <i class="fas fa-heart"></i>
                    </button>
                </div>
                <div class="recipe-card-content">
                    <h3 class="recipe-card-title"><a href="${baseUrl}/recipes/show.php?id=${recipe.id}">${recipe.title}</a></h3>
                    <p class="recipe-card-meta">
                        <span class="recipe-card-time"><i class="far fa-clock"></i> ${recipe.cook_time || 'N/A'}</span>
                        <span class="recipe-card-difficulty"><i class="fas fa-signal"></i> ${recipe.difficulty || 'Easy'}</span>
                    </p>
                </div>
            </article>
        `;
    },

    /**
     * Creates empty state message
     * @returns {string} HTML string for empty state
     */
    createEmptyState: function() {
        const baseUrl = window.FlavorConnect.config && window.FlavorConnect.config.baseUrl 
            ? window.FlavorConnect.config.baseUrl 
            : '';
            
        return `
            <div class="no-recipes">
                <p>You haven't favorited any recipes yet. Browse our recipes and click the heart icon to add them to your favorites!</p>
                <a href="${baseUrl}/recipes/index.php" class="btn btn-primary">Browse Recipes</a>
            </div>
        `;
    },

    /**
     * Updates empty state if no recipes remain
     */
    updateEmptyState: function() {
        const recipeGrid = document.querySelector('.recipe-grid');
        if (!recipeGrid) return;
        
        // Check if there are any recipe cards left (either article.recipe-card or div.recipe-card)
        const remainingCards = recipeGrid.querySelectorAll('article.recipe-card, .recipe-card');
        
        if (remainingCards.length === 0) {
            // Remove any existing empty state
            const existingEmptyState = document.querySelector('.no-recipes');
            if (existingEmptyState) {
                existingEmptyState.remove();
            }
            
            // Create new empty state
            const emptyState = this.createEmptyState();
            recipeGrid.insertAdjacentHTML('beforebegin', emptyState);
            recipeGrid.style.display = 'none';
        }
    },

    /**
     * Removes a recipe card from the DOM with animation
     * @param {HTMLElement} button - The button element inside the recipe card
     */
    removeRecipeCard: function(button) {
        // Try to find the recipe card using different selectors
        // First try article.recipe-card (used in favorites.php)
        let card = button.closest('article.recipe-card');
        
        // If not found, try div.recipe-card (used in dynamically created cards)
        if (!card) {
            card = button.closest('.recipe-card');
        }
        
        if (!card) {
            console.error('Could not find recipe card to remove');
            return;
        }
        
        // Animate removal
        card.style.transition = 'opacity 0.3s ease-out, transform 0.3s ease-out';
        card.style.opacity = '0';
        card.style.transform = 'scale(0.95)';
        
        setTimeout(() => {
            card.remove();
            this.updateEmptyState();
        }, 300);
    },

    /**
     * Shows/hides loading state
     * @param {boolean} show - Whether to show or hide loading state
     */
    showLoadingState: function(show) {
        let loadingEl = document.querySelector('.loading-spinner');
        
        if (show) {
            if (!loadingEl) {
                loadingEl = document.createElement('div');
                loadingEl.className = 'loading-spinner';
                loadingEl.innerHTML = '<div class="spinner"></div><p>Loading your favorites...</p>';
                
                const container = document.querySelector('.recipe-grid');
                if (container) {
                    container.parentNode.insertBefore(loadingEl, container);
                }
            }
            loadingEl.style.display = 'flex';
        } else if (loadingEl) {
            loadingEl.style.display = 'none';
        }
    },

    /**
     * Shows error message to user
     * @param {string} message - Error message to display
     */
    showError: function(message) {
        let errorEl = document.querySelector('.error-message');
        
        if (!errorEl) {
            errorEl = document.createElement('div');
            errorEl.className = 'error-message';
            
            const container = document.querySelector('.recipe-grid');
            if (container) {
                container.parentNode.insertBefore(errorEl, container);
            }
        }
        
        errorEl.innerHTML = `
            <div class="alert alert-danger">
                <p><strong>Error:</strong> ${message}</p>
                <button type="button" class="close-btn" aria-label="Close">×</button>
            </div>
        `;
        
        errorEl.style.display = 'block';
        
        // Add close button functionality
        const closeBtn = errorEl.querySelector('.close-btn');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                errorEl.style.display = 'none';
            });
        }
    }
};

// For backward compatibility
function initializeFavorites() {
    console.warn('initializeFavorites is deprecated, use window.FlavorConnect.favoritesPage.init instead');
    if (window.FlavorConnect && window.FlavorConnect.favoritesPage) {
        window.FlavorConnect.favoritesPage.init();
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    if (window.FlavorConnect.favoritesPage) {
        window.FlavorConnect.favoritesPage.init();
    }
});
