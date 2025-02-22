/**
 * @fileoverview Recipe gallery functionality
 */

// Initialize API Configuration
const API_CONFIG = {
    baseUrl: window.initialUserData.apiBaseUrl,
    endpoints: {
        favorites: '/favorites'
    }
};

// Global state
const state = {
    isLoggedIn: window.initialUserData.isLoggedIn,
    userId: window.initialUserData.userId,
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

// Initialize gallery
function initializeGallery() {
    setupEventListeners();
    loadFiltersFromURL();
}

// Set up event listeners
function setupEventListeners() {
    // Event delegation for favorite buttons
    document.querySelector('.recipe-grid').addEventListener('click', async (e) => {
        const favoriteBtn = e.target.closest('.favorite-btn');
        if (favoriteBtn) {
            e.preventDefault();
            await handleFavoriteToggle(e);
        }
    });

    // Sort field listener
    const sortSelect = document.querySelector('#sort');
    if (sortSelect) {
        sortSelect.addEventListener('change', (e) => {
            state.currentFilters.sort = e.target.value;
            applyFilters();
        });
    }
}

/**
 * Applies current filters to recipes and updates the display
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
    const grid = document.querySelector('.recipe-grid');
    grid.innerHTML = recipes.length ? recipes.map(recipe => createRecipeCard(recipe)).join('') 
                                  : createEmptyState();
}

/**
 * Creates HTML for a recipe card
 * @param {Object} recipe - Recipe object
 * @returns {string} HTML string for the recipe card
 */
function createRecipeCard(recipe) {
    const totalTime = formatTime(recipe.prep_time + recipe.cook_time);
    return `
        <article class="recipe-card">
            ${state.isLoggedIn ? `
                <button type="button" class="favorite-btn ${recipe.is_favorited ? 'favorited' : ''}" 
                        data-recipe-id="${recipe.recipe_id}"
                        aria-label="${recipe.is_favorited ? 'Remove from favorites' : 'Add to favorites'}">
                    <i class="fa-heart ${recipe.is_favorited ? 'fas' : 'far'}"></i>
                </button>
            ` : ''}
            <a href="/FlavorConnect/public/recipes/show.php?id=${recipe.recipe_id}" class="recipe-link">
                <div class="recipe-image">
                    <img src="${recipe.img_file_path ? '/FlavorConnect/public' + recipe.img_file_path : '/FlavorConnect/public/assets/images/recipe-placeholder.jpg'}" 
                         alt="${recipe.title}">
                </div>
                <div class="recipe-content">
                    <h2 class="recipe-title">${recipe.title}</h2>
                    
                    <div class="recipe-meta">
                        ${recipe.style ? `<span class="recipe-tag"><i class="fas fa-utensils"></i>${recipe.style}</span>` : ''}
                        ${recipe.diet ? `<span class="recipe-tag"><i class="fas fa-leaf"></i>${recipe.diet}</span>` : ''}
                        ${recipe.type ? `<span class="recipe-tag"><i class="fas fa-tag"></i>${recipe.type}</span>` : ''}
                    </div>

                    <div class="recipe-time">
                        <span><i class="fas fa-clock"></i>${totalTime}</span>
                    </div>

                    <div class="recipe-footer">
                        <div class="recipe-author">
                            <span class="author-name">By ${recipe.username}</span>
                        </div>
                        <div class="recipe-rating">
                            <i class="fas fa-star"></i>
                            <span>${recipe.rating ? recipe.rating.toFixed(1) : 'No ratings'}</span>
                            ${recipe.rating_count ? `<span class="rating-count">(${recipe.rating_count})</span>` : ''}
                        </div>
                    </div>
                </div>
            </a>
        </article>
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
    // Pagination implementation...
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
document.addEventListener('DOMContentLoaded', initializeGallery);
