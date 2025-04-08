/**
 * @fileoverview Recipe gallery functionality for FlavorConnect
 * @author Henry Vaughn
 * @version 1.2.0
 * @license MIT
 * @description Manages the recipe gallery page functionality including:
 * - Filter and sort controls
 * - Recipe card rendering
 * - Pagination
 * - Favorites integration
 * - Dynamic URL updates
 * 
 * This script works with server-side rendered recipe cards and enhances
 * the user experience with client-side filtering and sorting when available.
 */

// Global state
const state = {
    isLoggedIn: window.initialUserData?.isLoggedIn || false,
    userId: window.initialUserData?.userId || null,
    recipes: window.initialRecipesData || [],
    currentFilters: {
        search: '',
        style: '',
        diet: '',
        type: '',
        sort: 'newest',
        page: 1
    }
};

// Initialize API Configuration
const API_CONFIG = {
    baseUrl: window.initialUserData?.apiBaseUrl,
    endpoints: {
        favorites: '/favorites'
    }
};

/**
 * Initialize the recipe gallery
 * Sets up event listeners, loads filters from URL, and initializes favorite buttons
 */
window.initializeGallery = function() {
    setupEventListeners();
    loadFiltersFromURL();
    if (window.FlavorConnect && window.FlavorConnect.favorites) {
        window.FlavorConnect.favorites.initButtons();
    }
}

/**
 * Set up event listeners for filter and sort controls
 */
function setupEventListeners() {
    // Filter select listeners
    const filterSelects = document.querySelectorAll('.filter-select');
    filterSelects.forEach(select => {
        select.addEventListener('change', (e) => {
            const filterType = e.target.dataset.filterType;
            const value = e.target.value;
            
            // Build the URL with the updated filter
            const currentUrl = new URL(window.location.href);
            const params = new URLSearchParams(currentUrl.search);
            
            // Update or remove the parameter based on the value
            if (value === '') {
                params.delete(filterType);
            } else {
                params.set(filterType, value);
            }
            
            // Reset to page 1 when changing filters
            params.set('page', 1);
            
            // Keep other parameters
            currentUrl.search = params.toString();
            
            // Navigate to the new URL
            window.location.href = currentUrl.toString();
        });
    });

    // Sort field listener
    const sortSelect = document.querySelector('#sort');
    if (sortSelect) {
        sortSelect.addEventListener('change', (e) => {
            // Build the URL with the updated sort parameter
            const currentUrl = new URL(window.location.href);
            const params = new URLSearchParams(currentUrl.search);
            
            // Update sort parameter
            params.set('sort', e.target.value);
            
            // Reset to page 1 when changing sort
            params.set('page', 1);
            
            // Keep other parameters
            currentUrl.search = params.toString();
            
            // Navigate to the new URL
            window.location.href = currentUrl.toString();
        });
    }
    
    // Initialize pagination links
    updatePagination();
}

/**
 * Applies current filters to recipes and updates the display
 * Filters, sorts, and renders recipes based on current filter state
 */
function applyFilters() {
    const filteredRecipes = state.recipes.filter(recipe => {
        // Search filter
        const searchMatch = !state.currentFilters.search || 
            recipe.title.toLowerCase().includes(state.currentFilters.search.toLowerCase()) ||
            recipe.description.toLowerCase().includes(state.currentFilters.search.toLowerCase());

        // Style filter
        const styleMatch = !state.currentFilters.style || 
            recipe.style_id === parseInt(state.currentFilters.style);

        // Diet filter
        const dietMatch = !state.currentFilters.diet || 
            recipe.diet_id === parseInt(state.currentFilters.diet);

        // Type filter
        const typeMatch = !state.currentFilters.type || 
            recipe.type_id === parseInt(state.currentFilters.type);

        return searchMatch && styleMatch && dietMatch && typeMatch;
    });

    // Sort recipes
    let sortedRecipes = [...filteredRecipes];
    switch (state.currentFilters.sort) {
        case 'rating':
            sortedRecipes.sort((a, b) => {
                // Handle null ratings
                const ratingA = a.rating || 0;
                const ratingB = b.rating || 0;
                // Sort by rating (descending) and then by number of ratings (descending)
                if (ratingA === ratingB) {
                    return (b.rating_count || 0) - (a.rating_count || 0);
                }
                return ratingB - ratingA;
            });
            break;
        case 'title':
            sortedRecipes.sort((a, b) => a.title.localeCompare(b.title));
            break;
        case 'oldest':
            sortedRecipes.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
            break;
        case 'newest':
        default:
            sortedRecipes.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
            break;
    }

    updateRecipeGrid(sortedRecipes);
    updatePagination(sortedRecipes.length);
}

/**
 * Updates the recipe grid with filtered recipes
 * @param {Array} recipes - Array of recipe objects to display
 */
function updateRecipeGrid(recipes) {
    const recipeGrid = document.querySelector('.recipe-grid');
    if (!recipeGrid) return;

    if (recipes.length === 0) {
        recipeGrid.innerHTML = createEmptyState();
    } else {
        recipeGrid.innerHTML = recipes.map(recipe => createRecipeCard(recipe)).join('');
        // Initialize favorite buttons for new cards
        if (window.FlavorConnect && window.FlavorConnect.favorites) {
            window.FlavorConnect.favorites.initButtons();
        }
    }
}

/**
 * Creates HTML for a recipe card
 * @param {Object} recipe - Recipe object
 * @returns {string} HTML string for the recipe card
 */
function createRecipeCard(recipe) {
    return `
        <article class="recipe-card" role="article">
            <a href="/recipes/show.php?id=${recipe.recipe_id}" 
               class="recipe-link"
               aria-labelledby="recipe-title-${recipe.recipe_id}">
                <div class="recipe-image-container">
                    ${state.isLoggedIn ? `
                        <button class="favorite-btn ${recipe.is_favorited ? 'favorited' : ''}"
                                data-recipe-id="${recipe.recipe_id}"
                                aria-label="${recipe.is_favorited ? 'Remove from' : 'Add to'} favorites">
                            <i class="${recipe.is_favorited ? 'fas' : 'far'} fa-heart"></i>
                        </button>
                    ` : ''}
                    <img src="${recipe.img_file_path}" 
                         alt="Photo of ${recipe.title}" 
                         class="recipe-image">
                </div>
                
                <div class="recipe-content">
                    <h2 class="recipe-title" id="recipe-title-${recipe.recipe_id}">${recipe.title}</h2>
                    
                    <div class="recipe-meta">
                        <span class="rating" aria-label="Rating: ${recipe.rating || 0} out of 5 stars">
                            ${createRatingStars(recipe.rating, recipe.rating_count)}
                        </span>
                        <span class="time">
                            ${recipe.prep_time + recipe.cook_time} mins
                        </span>
                    </div>

                    <div class="recipe-attributes" role="list">
                        ${recipe.style ? `<span class="recipe-attribute">${recipe.style}</span>` : ''}
                        ${recipe.diet ? `<span class="recipe-attribute">${recipe.diet}</span>` : ''}
                        ${recipe.type ? `<span class="recipe-attribute">${recipe.type}</span>` : ''}
                    </div>
                </div>

                <div class="recipe-footer">
                    <div class="recipe-author">
                        <span class="author-name">By ${recipe.username}</span>
                    </div>
                </div>
            </a>
        </article>
    `;
}

/**
 * Creates rating stars HTML
 * @param {number} rating - Rating value
 * @param {number} count - Number of ratings
 * @returns {string} HTML string for rating stars
 */
function createRatingStars(rating = 0, count = 0) {
    const fullStars = Math.floor(rating);
    const hasHalfStar = rating - fullStars >= 0.5;
    const emptyStars = 5 - Math.ceil(rating);
    
    return `
        ${'★'.repeat(fullStars)}
        ${hasHalfStar ? '⯨' : ''}
        ${'☆'.repeat(emptyStars)}
        <span class="review-count">(${count})</span>
    `;
}

/**
 * Creates empty state message when no recipes match filters
 * @returns {string} HTML string for empty state
 */
function createEmptyState() {
    return `
        <div class="empty-state">
            <i class="fas fa-search"></i>
            <h2>No recipes found</h2>
            <p>Try adjusting your filters or search terms</p>
        </div>
    `;
}

/**
 * Updates pagination
 * @param {number} totalRecipes - Total number of recipes
 */
function updatePagination(totalRecipes) {
    // Server-side pagination is now used
    // No client-side handling needed
}

/**
 * Clears all filters and resets the display
 */
function clearFilters() {
    // Reset all filters to default values
    state.currentFilters = {
        search: '',
        style: '',
        diet: '',
        type: '',
        sort: 'newest',
        page: 1
    };

    // Reset form inputs
    document.querySelector('.search-input').value = '';
    document.querySelectorAll('.filter-select').forEach(select => {
        select.value = '';
    });

    // Update display and URL
    applyFilters();
    updateURL();
}

/**
 * Updates URL with current filter state
 */
function updateURL() {
    const params = new URLSearchParams();
    Object.entries(state.currentFilters).forEach(([key, value]) => {
        if (value) params.set(key, value);
    });
    window.history.replaceState({}, '', `${window.location.pathname}?${params}`);
}

/**
 * Loads filters from URL parameters
 */
function loadFiltersFromURL() {
    const params = new URLSearchParams(window.location.search);
    params.forEach((value, key) => {
        if (state.currentFilters.hasOwnProperty(key)) {
            state.currentFilters[key] = value;
            const input = document.querySelector(`[data-filter="${key}"]`);
            if (input) input.value = value;
        }
    });
}

/**
 * Shows/hides loading state
 * @param {boolean} show - Whether to show or hide loading state
 */
function showLoadingState(show) {
    const grid = document.querySelector('.recipe-grid');
    if (show) {
        grid.classList.add('loading');
    } else {
        grid.classList.remove('loading');
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', window.initializeGallery);
