/**
 * @fileoverview Recipe Favorites page functionality for FlavorConnect
 * @author FlavorConnect Team
 * @version 1.0.0
 * @license MIT
 */

import { fetchData } from '../utils/common.js';

/**
 * Initializes the favorites page functionality
 */
function initializeFavorites() {
    setupEventListeners();
    loadFavorites();
}

/**
 * Sets up event listeners for the page
 */
function setupEventListeners() {
    // Handle unfavorite buttons
    document.querySelectorAll('.unfavorite-btn').forEach(button => {
        button.addEventListener('click', handleUnfavorite);
    });

    // Handle sort select
    const sortSelect = document.querySelector('#sort-select');
    if (sortSelect) {
        sortSelect.addEventListener('change', handleSort);
    }

    // Get all favorite buttons
    const favoriteButtons = document.querySelectorAll('.favorite-btn');

    // Add click event listener to each button
    favoriteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            const recipeId = this.dataset.recipeId;
            const isFavorited = this.dataset.isFavorited === 'true';
            const heartIcon = this.querySelector('.fa-heart');

            // Toggle favorite status
            fetch('/api/favorites', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `recipe_id=${recipeId}&is_favorited=${!isFavorited}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update button state
                    this.dataset.isFavorited = (!isFavorited).toString();
                    this.classList.toggle('active');
                    heartIcon.classList.toggle('fas');
                    heartIcon.classList.toggle('far');
                } else {
                    console.error('Failed to toggle favorite:', data.message);
                }
            })
            .catch(error => {
                console.error('Error toggling favorite:', error);
            });
        });
    });
}

/**
 * Loads favorite recipes from the server
 */
async function loadFavorites() {
    try {
        showLoadingState(true);
        const response = await fetchData('/api/recipes?action=get_favorites');
        
        if (response.success) {
            updateFavoritesList(response.favorites);
        } else {
            showError('Failed to load favorites');
        }
    } catch (error) {
        console.error('Error loading favorites:', error);
        showError('Failed to load favorites. Please try again later.');
    } finally {
        showLoadingState(false);
    }
}

/**
 * Handles unfavoriting a recipe
 * @param {Event} e - Click event from unfavorite button
 */
async function handleUnfavorite(e) {
    const button = e.currentTarget;
    const recipeId = button.dataset.recipeId;
    const card = button.closest('.recipe-card');

    try {
        const response = await fetchData('/api/recipes', {
            method: 'POST',
            body: JSON.stringify({
                action: 'toggle_favorite',
                recipe_id: recipeId
            })
        });

        if (response.success) {
            // Animate and remove card
            card.style.opacity = '0';
            setTimeout(() => {
                card.remove();
                updateEmptyState();
            }, 300);
        }
    } catch (error) {
        console.error('Error unfavoriting recipe:', error);
        showError('Failed to unfavorite recipe. Please try again.');
    }
}

/**
 * Handles sorting of favorite recipes
 * @param {Event} e - Change event from sort select
 */
function handleSort(e) {
    const sortBy = e.target.value;
    const grid = document.querySelector('.recipe-grid');
    const cards = Array.from(grid.children);

    cards.sort((a, b) => {
        const aValue = a.dataset[sortBy];
        const bValue = b.dataset[sortBy];

        switch(sortBy) {
            case 'date':
                return new Date(bValue) - new Date(aValue);
            case 'name':
                return aValue.localeCompare(bValue);
            default:
                return 0;
        }
    });

    // Clear and re-append sorted cards
    grid.innerHTML = '';
    cards.forEach(card => grid.appendChild(card));
}

/**
 * Updates the favorites list with new data
 * @param {Array} favorites - Array of favorite recipe objects
 */
function updateFavoritesList(favorites) {
    const grid = document.querySelector('.recipe-grid');
    
    if (!favorites.length) {
        grid.innerHTML = createEmptyState();
        return;
    }

    grid.innerHTML = favorites.map(recipe => createRecipeCard(recipe)).join('');
}

/**
 * Creates HTML for a recipe card
 * @param {Object} recipe - Recipe object
 * @returns {string} HTML string for the recipe card
 */
function createRecipeCard(recipe) {
    return `
        <article class="recipe-card" 
                 data-name="${recipe.title}"
                 data-date="${recipe.favorited_at}">
            <button type="button" 
                    class="unfavorite-btn" 
                    data-recipe-id="${recipe.id}">
                <i class="fas fa-heart"></i>
            </button>
            <a href="/FlavorConnect/public/recipes/show.php?id=${recipe.id}" 
               class="recipe-link">
                <div class="recipe-image">
                    <img src="${recipe.image_url || '/FlavorConnect/public/assets/images/recipe-placeholder.png'}" 
                         alt="${recipe.title}">
                </div>
                <div class="recipe-content">
                    <h2 class="recipe-title">${recipe.title}</h2>
                    <div class="recipe-meta">
                        <span class="favorited-date">
                            <i class="far fa-clock"></i>
                            Favorited on ${new Date(recipe.favorited_at).toLocaleDateString()}
                        </span>
                    </div>
                </div>
            </a>
        </article>
    `;
}

/**
 * Creates empty state message
 * @returns {string} HTML string for empty state
 */
function createEmptyState() {
    return `
        <div class="empty-state">
            <h3>No favorite recipes yet</h3>
            <p>Start exploring recipes and save your favorites!</p>
            <a href="/FlavorConnect/public/recipes" class="btn btn-primary">
                Browse Recipes
            </a>
        </div>
    `;
}

/**
 * Updates empty state if no recipes remain
 */
function updateEmptyState() {
    const grid = document.querySelector('.recipe-grid');
    if (!grid.children.length) {
        grid.innerHTML = createEmptyState();
    }
}

/**
 * Shows/hides loading state
 * @param {boolean} show - Whether to show or hide loading state
 */
function showLoadingState(show) {
    const loader = document.querySelector('.loader');
    if (loader) {
        loader.style.display = show ? 'block' : 'none';
    }
}

/**
 * Shows error message to user
 * @param {string} message - Error message to display
 */
function showError(message) {
    const errorElement = document.querySelector('.error-message');
    if (errorElement) {
        errorElement.textContent = message;
        errorElement.style.display = 'block';
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', initializeFavorites);
