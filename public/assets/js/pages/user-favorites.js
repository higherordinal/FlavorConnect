/**
 * @fileoverview User Favorites page functionality for FlavorConnect
 * @author Henry Vaughn
 * @license MIT
 */

'use strict';

// Initialize FlavorConnect namespace
window.FlavorConnect = window.FlavorConnect || {};

// Create favorites page namespace
window.FlavorConnect.favoritesPage = {
    /**
     * Initializes the favorites page functionality
     */
    init: function() {
        this.setupEventListeners();
        this.loadFavorites();
    },

    /**
     * Sets up event listeners for the page
     */
    setupEventListeners: function() {
        // Check if we're on the favorites page
        const isOnFavoritesPage = window.location.pathname.includes('favorites') || 
                                  document.querySelector('.favorites-page, .user-favorites') !== null;
        
        // On the favorites page, all buttons should be treated as favorited
        if (isOnFavoritesPage) {
            // Get all favorite buttons on the page
            const allButtons = document.querySelectorAll('.favorite-btn');
            
            // Mark all buttons as favorited if they aren't already
            allButtons.forEach(button => {
                // Add favorited class if not present
                if (!button.classList.contains('favorited')) {
                    button.classList.add('favorited');
                }
                
                // Update icon to solid heart if needed
                const icon = button.querySelector('i');
                if (icon) {
                    if (!icon.classList.contains('fas')) {
                        icon.classList.remove('far');
                        icon.classList.add('fas');
                    }
                }
                
                // Update aria-label
                button.setAttribute('aria-label', 'Remove from favorites');
                
                // Remove existing listeners to prevent duplicates
                button.removeEventListener('click', this.handleUnfavorite.bind(this));
                // Add click handler
                button.addEventListener('click', this.handleUnfavorite.bind(this));
            });
        } else {
            // Regular behavior for non-favorites pages
            // Find all favorite buttons with solid heart icons (favorited state)
            const favoritedButtons = [];
            document.querySelectorAll('.favorite-btn').forEach(button => {
                const icon = button.querySelector('i');
                if (icon && icon.classList.contains('fas') && icon.classList.contains('fa-heart')) {
                    favoritedButtons.push(button);
                }
            });
            
            // Handle unfavorite buttons (identified by solid heart icons)
            favoritedButtons.forEach(button => {
                // Remove existing listeners to prevent duplicates
                button.removeEventListener('click', this.handleUnfavorite.bind(this));
                // Add click handler
                button.addEventListener('click', this.handleUnfavorite.bind(this));
            });
        }

        // Handle sort select
        const sortSelect = document.querySelector('#sort-select');
        if (sortSelect) {
            sortSelect.addEventListener('change', this.handleSort.bind(this));
        }

        // Initialize favorite buttons if not already handled
        if (window.FlavorConnect.favorites) {
            window.FlavorConnect.favorites.initButtons();
        }
    },

    /**
     * Loads favorite recipes from the server
     */
    loadFavorites: async function() {
        this.showLoadingState(true);
        
        try {
            // Check for recipe cards
            const recipeCards = document.querySelectorAll('.recipe-card');
            
            // Check for all favorite buttons regardless of state
            const allFavoriteButtons = document.querySelectorAll('.favorite-btn');
            
            // Check if we're on the favorites page
            const isOnFavoritesPage = window.location.pathname.includes('favorites') || 
                                      document.querySelector('.favorites-page, .user-favorites') !== null;
            
            // If we're on the favorites page, all recipes should be marked as favorited
            if (isOnFavoritesPage && recipeCards.length > 0) {
                // Mark all buttons as favorited
                allFavoriteButtons.forEach(btn => {
                    // Add favorited class
                    btn.classList.add('favorited');
                    
                    // Update icon to solid heart
                    const icon = btn.querySelector('i');
                    if (icon) {
                        icon.classList.remove('far');
                        icon.classList.add('fas');
                    }
                    
                    // Update aria-label
                    btn.setAttribute('aria-label', 'Remove from favorites');
                });
                
                // Re-attach event listeners
                this.setupEventListeners();
                return;
            }
            
            // Find favorited recipes by checking for solid heart icons inside buttons
            let favoriteButtons = [];
            
            // Check each button for solid heart icon
            allFavoriteButtons.forEach(btn => {
                const icon = btn.querySelector('i');
                if (icon && icon.classList.contains('fas') && icon.classList.contains('fa-heart')) {
                    // This is a favorited button (solid heart icon)
                    favoriteButtons.push(btn);
                }
            });
            
            if (favoriteButtons.length > 0) {
                // Re-attach event listeners to make sure they work after AJAX pagination
                this.setupEventListeners();
                return; // Favorites are already loaded in the DOM
            }
            
            // We're already on the favorites page but no favorited recipes were found
            if (isOnFavoritesPage) {
                // Only show empty state if we're actually on the favorites page
                this.updateEmptyState();
            }
        } catch (error) {
            console.error('Error loading favorites:', error);
            this.showError('Failed to load your favorite recipes. Please try again later.');
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
        
        if (!recipeId) return;
        
        try {
            // Use the favorites utility if available
            if (window.FlavorConnect.favorites && typeof window.FlavorConnect.favorites.toggle === 'function') {
                const result = await window.FlavorConnect.favorites.toggle(recipeId);
                
                if (result.success && !result.isFavorited) {
                    // Remove the recipe card with animation
                    this.removeRecipeCard(button);
                    
                    // Check if we need to show empty state after removing
                    setTimeout(() => {
                        this.updateEmptyState();
                    }, 350); // Wait a bit longer than the animation duration
                } else if (result.html_response) {
                    // If we got an HTML response, try the direct approach
                    await this.directUnfavorite(recipeId, button);
                }
            } else {
                // Fallback to direct fetch
                await this.directUnfavorite(recipeId, button);
            }
        } catch (error) {
            console.error('Error unfavoriting recipe:', error);
            this.showError('Failed to unfavorite recipe. Please try again later.');
        }
    },
    
    /**
     * Direct unfavorite implementation as a fallback
     * @param {string|number} recipeId - The recipe ID to unfavorite
     * @param {HTMLElement} button - The button element that was clicked
     */
    directUnfavorite: async function(recipeId, button) {
        // Use the toggle_favorite.php endpoint with POST method
        const baseUrl = window.FlavorConnect.config.baseUrl;
        // Remove trailing slash if present
        const url = baseUrl.endsWith('/') ? baseUrl.slice(0, -1) : baseUrl;
        
        // Add cache-busting parameter
        const cacheBuster = new Date().getTime();
        const fullUrl = `${url}/api/toggle_favorite.php?_=${cacheBuster}`;
        console.log('Direct unfavorite URL:', fullUrl);
        
        // Use URLSearchParams for the body
        const params = new URLSearchParams();
        params.append('recipe_id', recipeId);
        
        const response = await fetch(fullUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'Accept': 'application/json',
                'Cache-Control': 'no-cache, no-store, must-revalidate',
                'Pragma': 'no-cache',
                'Expires': '0'
            },
            body: params
        });
        
        // Log the response status for debugging
        console.log('Response status:', response.status);
        
        // Handle unauthorized response (user not logged in)
        if (response.status === 401) {
            console.log('User not logged in, redirecting to login page');
            window.location.href = `${window.FlavorConnect.config.baseUrl}login.php`;
            return;
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
            throw new Error('Invalid response format');
        }
        
        try {
            const data = await response.json();
            console.log('Unfavorite response data:', data);
            
            if (data.success && !data.is_favorited) {
                this.removeRecipeCard(button);
            }
        } catch (jsonError) {
            console.error('Error parsing JSON:', jsonError);
            throw new Error('Failed to parse JSON response');
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
            // Directly use fetch for API requests
            const response = await fetch(`${window.FlavorConnect.config.apiBaseUrl}recipes/favorites?sort=${sortBy}`);
            const data = await response.json();
            
            if (data.error) {
                throw new Error(data.error);
            }
            
            this.updateFavoritesList(data.favorites || []);
        } catch (error) {
            console.error('Error sorting favorites:', error);
            this.showError('Failed to sort recipes. Please try again.');
        } finally {
            this.showLoadingState(false);
        }
    },

    /**
     * Updates the favorites list with new data
     * @param {Array} favorites - Array of favorite recipe objects
     */
    updateFavoritesList: function(favorites) {
        const container = document.querySelector('.recipe-gallery');
        if (!container) return;
        
        if (favorites.length === 0) {
            container.innerHTML = this.createEmptyState();
            return;
        }
        
        const html = favorites.map(recipe => this.createRecipeCard(recipe)).join('');
        container.innerHTML = html;
        
        // Re-initialize favorite buttons
        if (window.FlavorConnect.favorites) {
            window.FlavorConnect.favorites.initButtons();
        }
    },

    /**
     * Creates HTML for a recipe card
     * @param {Object} recipe - Recipe object
     * @returns {string} HTML string for the recipe card
     */
    createRecipeCard: function(recipe) {
        return `
            <div class="recipe-card" data-recipe-id="${recipe.id}">
                <div class="recipe-image">
                    <img src="${recipe.image_url || window.FlavorConnect.config.baseUrl + 'assets/images/recipe-placeholder.jpg'}" alt="${recipe.title}">
                    <button class="favorite-btn favorited" data-recipe-id="${recipe.id}" aria-label="Remove from favorites">
                        <i class="fas fa-heart"></i>
                    </button>
                </div>
                <div class="recipe-content">
                    <h3 class="recipe-title">
                        <a href="${window.FlavorConnect.config.baseUrl}recipes/show.php?id=${recipe.id}">${recipe.title}</a>
                    </h3>
                    <div class="recipe-meta">
                        <span class="recipe-time"><i class="far fa-clock"></i> ${recipe.cook_time} min</span>
                        <span class="recipe-difficulty"><i class="fas fa-signal"></i> ${recipe.difficulty}</span>
                    </div>
                    <p class="recipe-description">${recipe.description.substring(0, 100)}${recipe.description.length > 100 ? '...' : ''}</p>
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
                <i class="far fa-heart"></i>
                <h3>No Favorite Recipes</h3>
                <p>You haven't added any recipes to your favorites yet.</p>
                <a href="${window.FlavorConnect.config.baseUrl}recipes" class="btn btn-primary">Explore Recipes</a>
            </div>
        `;
    },

    /**
     * Updates empty state if no recipes remain
     */
    updateEmptyState: function() {
        const container = document.querySelector('.recipe-gallery');
        if (!container) return;
        
        if (document.querySelectorAll('.recipe-card').length === 0) {
            container.innerHTML = this.createEmptyState();
        }
    },

    /**
     * Removes a recipe card from the DOM with animation
     * @param {HTMLElement} button - The button element inside the recipe card
     */
    removeRecipeCard: function(button) {
        const recipeCard = button.closest('.recipe-card');
        if (!recipeCard) return;
        
        // Remove with animation
        recipeCard.classList.add('removing');
        setTimeout(() => {
            recipeCard.remove();
            
            // Check if we need to show empty state
            this.updateEmptyState();
        }, 300);
    },

    /**
     * Shows/hides loading state
     * @param {boolean} show - Whether to show or hide loading state
     */
    showLoadingState: function(show) {
        const container = document.querySelector('.recipe-gallery');
        if (!container) return;
        
        if (show) {
            container.classList.add('loading');
        } else {
            container.classList.remove('loading');
        }
    },

    /**
     * Shows error message to user
     * @param {string} message - Error message to display
     */
    showError: function(message) {
        const errorContainer = document.querySelector('.error-message');
        if (!errorContainer) {
            const container = document.querySelector('.recipe-gallery-container');
            if (container) {
                const errorDiv = document.createElement('div');
                errorDiv.className = 'error-message';
                errorDiv.textContent = message;
                container.insertBefore(errorDiv, container.firstChild);
            }
        } else {
            errorContainer.textContent = message;
        }
    }
};

// For backward compatibility
window.initializeFavorites = function() {
    window.FlavorConnect.favoritesPage.init();
};

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    if (window.FlavorConnect.favoritesPage) {
        window.FlavorConnect.favoritesPage.init();
    } else if (typeof window.initializeFavorites === 'function') {
        window.initializeFavorites();
    }
});
