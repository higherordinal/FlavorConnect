/**
 * @fileoverview User Favorites page functionality for FlavorConnect
 * @author Henry Vaughn
 * @version 1.3.0
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
        
        // Handle unfavorite buttons
        document.querySelectorAll('.favorite-btn.favorited').forEach(button => {
            if (!button._hasUnfavoriteListener) {
                button.addEventListener('click', this.handleUnfavorite.bind(this));
                button._hasUnfavoriteListener = true;
            }
        });

        // Handle sort select
        const sortSelect = document.querySelector('#sort-select');
        if (sortSelect) {
            sortSelect.addEventListener('change', this.handleSort.bind(this));
        }

        // Add a global event listener for any dynamically added favorite buttons
        document.addEventListener('click', (event) => {
            const button = event.target.closest('.favorite-btn.favorited');
            if (button && !button._hasUnfavoriteListener) {
                this.handleUnfavorite.call(this, event);
                button._hasUnfavoriteListener = true;
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
        if (event.preventDefault) event.preventDefault();
        if (event.stopPropagation) event.stopPropagation();
        
        const button = event.target.closest('.favorite-btn') || event.currentTarget;
        const recipeId = button.dataset.recipeId;
        
        if (!recipeId) {
            console.error('No recipe ID found on button');
            return;
        }
        
        console.log('Unfavoriting recipe ID:', recipeId);
        
        // First, remove the card immediately for better UX
        const card = button.closest('.recipe-card');
        if (card) {
            this.removeRecipeCard(button);
            this.updateEmptyState();
        }
        
        // Then make the API call
        try {
            // Use XMLHttpRequest to avoid any caching issues
            const xhr = new XMLHttpRequest();
            
            // Get the base URL
            const baseUrl = window.FlavorConnect.config?.baseUrl || '/';
            const url = baseUrl.endsWith('/') ? baseUrl.slice(0, -1) : baseUrl;
            const fullUrl = `${url}/api/toggle_favorite.php?_=${new Date().getTime()}`; // Add cache buster
            
            console.log('Making API call to:', fullUrl);
            
            xhr.open('POST', fullUrl, true);
            xhr.setRequestHeader('Content-Type', 'application/json');
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            
            xhr.onload = () => {
                if (xhr.status >= 200 && xhr.status < 300) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        console.log('Unfavorite response:', response);
                        
                        if (!response.success) {
                            console.error('API reported error:', response.message || 'Unknown error');
                        }
                    } catch (e) {
                        console.error('Error parsing API response:', e);
                    }
                } else {
                    console.error('API request failed with status:', xhr.status);
                }
            };
            
            xhr.onerror = () => {
                console.error('Network error during API request');
            };
            
            xhr.send(JSON.stringify({ recipe_id: recipeId }));
        } catch (error) {
            console.error('Error unfavoriting recipe:', error);
        }
    },

    /**
     * Handles sorting of favorite recipes
     * @param {Event} e - Change event from sort select
     */
    handleSort: function(e) {
        const sortBy = e.target.value;
        console.log('Sorting by:', sortBy);
        
        this.showLoadingState(true);
        
        // Reload favorites with new sort
        if (window.FlavorConnect.favorites && typeof window.FlavorConnect.favorites.getAllFavorites === 'function') {
            window.FlavorConnect.favorites.getAllFavorites(sortBy)
                .then(favorites => {
                    this.updateFavoritesList(favorites);
                    this.showLoadingState(false);
                })
                .catch(error => {
                    console.error('Error loading sorted favorites:', error);
                    this.showError('Failed to sort favorites');
                    this.showLoadingState(false);
                });
        } else {
            this.showError('Sorting functionality not available');
            this.showLoadingState(false);
        }
    },

    /**
     * Updates the favorites list with new data
     * @param {Array} favorites - Array of favorite recipe objects
     */
    updateFavoritesList: function(favorites) {
        const container = document.querySelector('#favorites-container');
        if (!container) {
            console.error('Favorites container not found');
            return;
        }
        
        if (!favorites || favorites.length === 0) {
            container.innerHTML = this.createEmptyState();
            return;
        }
        
        let html = '';
        favorites.forEach(recipe => {
            html += this.createRecipeCard(recipe);
        });
        
        container.innerHTML = html;
        
        // Re-initialize event listeners for the new buttons
        this.setupEventListeners();
    },

    /**
     * Creates HTML for a recipe card
     * @param {Object} recipe - Recipe object
     * @returns {string} HTML string for the recipe card
     */
    createRecipeCard: function(recipe) {
        const baseUrl = window.FlavorConnect.config?.baseUrl || '/';
        const recipeUrl = `${baseUrl}recipes/show.php?id=${recipe.id}`;
        const imageUrl = recipe.image_url || `${baseUrl}assets/images/recipe-placeholder.jpg`;
        
        return `
            <div class="recipe-card" data-recipe-id="${recipe.id}">
                <div class="recipe-card-inner">
                    <div class="recipe-image">
                        <a href="${recipeUrl}">
                            <img src="${imageUrl}" alt="${recipe.title}">
                        </a>
                        <button class="favorite-btn favorited" data-recipe-id="${recipe.id}">
                            <i class="fas fa-heart"></i>
                        </button>
                    </div>
                    <div class="recipe-details">
                        <h3><a href="${recipeUrl}">${recipe.title}</a></h3>
                        <p class="recipe-meta">${recipe.cook_time || ''} | ${recipe.difficulty || ''}</p>
                    </div>
                </div>
            </div>
        `;
    },

    /**
     * Creates empty state message
     * @returns {string} HTML string for empty state
     */
    createEmptyState: function() {
        return `
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="far fa-heart"></i>
                </div>
                <h2>No Favorites Yet</h2>
                <p>You haven't added any recipes to your favorites yet.</p>
                <a href="/" class="btn btn-primary">Explore Recipes</a>
            </div>
        `;
    },

    /**
     * Updates empty state if no recipes remain
     */
    updateEmptyState: function() {
        const container = document.querySelector('#favorites-container');
        if (!container) return;
        
        const cards = container.querySelectorAll('.recipe-card');
        if (cards.length === 0) {
            container.innerHTML = this.createEmptyState();
        }
    },

    /**
     * Removes a recipe card from the DOM with animation
     * @param {HTMLElement} button - The button element inside the recipe card
     */
    removeRecipeCard: function(button) {
        const card = button.closest('.recipe-card');
        if (!card) return;
        
        // Add animation class
        card.classList.add('removing');
        
        // Remove after animation completes
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
        const container = document.querySelector('#favorites-container');
        const loadingEl = document.querySelector('#loading-state');
        
        if (!container) return;
        
        if (show) {
            if (!loadingEl) {
                const loading = document.createElement('div');
                loading.id = 'loading-state';
                loading.className = 'loading-state';
                loading.innerHTML = '<div class="spinner"></div><p>Loading your favorites...</p>';
                
                container.parentNode.insertBefore(loading, container);
            }
            container.classList.add('loading');
        } else {
            if (loadingEl) {
                loadingEl.remove();
            }
            container.classList.remove('loading');
        }
    },

    /**
     * Shows error message to user
     * @param {string} message - Error message to display
     */
    showError: function(message) {
        console.error(message);
        
        const container = document.querySelector('#favorites-container');
        if (!container) return;
        
        const errorEl = document.querySelector('#error-message');
        if (errorEl) {
            errorEl.textContent = message;
            return;
        }
        
        const error = document.createElement('div');
        error.id = 'error-message';
        error.className = 'error-message';
        error.textContent = message;
        
        container.parentNode.insertBefore(error, container);
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            if (error.parentNode) {
                error.remove();
            }
        }, 5000);
    }
};

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    if (window.FlavorConnect.favoritesPage) {
        window.FlavorConnect.favoritesPage.init();
    }
});
