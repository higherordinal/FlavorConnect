/**
 * @fileoverview Recipe Gallery page functionality for FlavorConnect
 * @author FlavorConnect Team
 * @version 1.0.0
 * @license MIT
 */

import { debounce, formatTime, fetchData } from '../utils/common.js';

// State management
const state = {
    recipes: [],
    currentFilters: {
        search: '',
        style: '',
        diet: '',
        type: '',
        sort: 'newest',
        page: 1
    }
};

/**
 * Initializes the recipe gallery page
 * Sets up event listeners and loads initial data
 */
function initializeGallery() {
    loadRecipes();
    setupEventListeners();
    loadFiltersFromURL();
}

/**
 * Loads recipes from the API
 * @returns {Promise<void>}
 */
async function loadRecipes() {
    try {
        showLoadingState(true);
        const url = '/api/recipes?action=list';
        const response = await fetchData(url);
        state.recipes = response.recipes || [];
        applyFilters();
    } catch (error) {
        showError('Failed to load recipes. Please try again later.');
        console.error('Error loading recipes:', error);
    } finally {
        showLoadingState(false);
    }
}

/**
 * Sets up event listeners for filters and search
 */
function setupEventListeners() {
    // Search input
    document.querySelector('.search-input').addEventListener('input', 
        debounce(e => {
            state.currentFilters.search = e.target.value;
            applyFilters();
            updateURL();
        }, 300)
    );

    // Filter selects
    const filterSelects = document.querySelectorAll('.filter-select');
    filterSelects.forEach(select => {
        select.addEventListener('change', e => {
            const filterType = e.target.dataset.filter;
            state.currentFilters[filterType] = e.target.value;
            applyFilters();
            updateURL();
        });
    });

    // Clear filters button
    document.querySelector('.clear-filters').addEventListener('click', clearFilters);
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

    const sortedRecipes = filteredRecipes.sort((a, b) => {
        switch(state.currentFilters.sort) {
            case 'newest':
                return new Date(b.created_at) - new Date(a.created_at);
            case 'oldest':
                return new Date(a.created_at) - new Date(b.created_at);
            case 'name_asc':
                return a.title.localeCompare(b.title);
            case 'name_desc':
                return b.title.localeCompare(a.title);
            default:
                return 0;
        }
    });

    const start = (state.currentFilters.page - 1) * 12;
    const paginatedRecipes = sortedRecipes.slice(start, start + 12);

    updateRecipeGrid(paginatedRecipes);
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
            ${window.isLoggedIn ? `
                <button type="button" class="favorite-btn ${recipe.is_favorited ? 'active' : ''}" 
                        data-recipe-id="${recipe.id}"
                        data-is-favorited="${recipe.is_favorited ? 'true' : 'false'}">
                    <i class="fa-heart ${recipe.is_favorited ? 'fas' : 'far'}"></i>
                </button>
            ` : ''}
            <a href="/FlavorConnect/public/recipes/show.php?id=${recipe.id}" class="recipe-link">
                <div class="recipe-image">
                    <img src="${recipe.image ? '/FlavorConnect/public' + recipe.image : '/FlavorConnect/public/assets/images/recipe-placeholder.jpg'}" 
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
        <div class="no-results">
            <p>No recipes found matching your criteria.</p>
            <button onclick="window.location.href='/FlavorConnect/public/recipes/index.php'" class="btn btn-primary">Clear Filters</button>
        </div>
    `;
}

/**
 * Updates pagination
 * @param {number} totalRecipes - Total number of recipes
 */
function updatePagination(totalRecipes) {
    const totalPages = Math.ceil(totalRecipes / 12);
    const pagination = document.querySelector('.pagination');
    
    if (totalPages <= 1) {
        pagination.style.display = 'none';
        return;
    }

    pagination.style.display = 'flex';
    pagination.innerHTML = `
        ${state.currentFilters.page > 1 ? `
            <a href="#" class="page-link" data-page="${state.currentFilters.page - 1}">
                <i class="fas fa-chevron-left"></i>
            </a>
        ` : ''}
        ${Array.from({length: totalPages}, (_, i) => i + 1).map(page => `
            <a href="#" class="page-link ${page === state.currentFilters.page ? 'active' : ''}" 
               data-page="${page}">${page}</a>
        `).join('')}
        ${state.currentFilters.page < totalPages ? `
            <a href="#" class="page-link" data-page="${state.currentFilters.page + 1}">
                <i class="fas fa-chevron-right"></i>
            </a>
        ` : ''}
    `;

    // Add click handlers for pagination
    pagination.querySelectorAll('.page-link').forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const newPage = parseInt(e.target.closest('.page-link').dataset.page);
            if (newPage !== state.currentFilters.page) {
                state.currentFilters.page = newPage;
                applyFilters();
                // Scroll to top of recipe grid
                document.querySelector('.recipe-grid').scrollIntoView({ behavior: 'smooth' });
            }
        });
    });
}

/**
 * Clears all filters and resets the display
 */
function clearFilters() {
    // Reset filter state
    Object.keys(state.currentFilters).forEach(key => {
        state.currentFilters[key] = '';
    });

    // Reset UI
    document.querySelector('.search-input').value = '';
    document.querySelectorAll('.filter-select').forEach(select => {
        select.value = '';
    });

    // Update display
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
    window.history.pushState({}, '', `${window.location.pathname}?${params.toString()}`);
}

/**
 * Loads filters from URL parameters
 */
function loadFiltersFromURL() {
    const params = new URLSearchParams(window.location.search);
    Object.keys(state.currentFilters).forEach(key => {
        const value = params.get(key);
        if (value) {
            state.currentFilters[key] = value;
            const element = document.querySelector(`[data-filter="${key}"]`);
            if (element) element.value = value;
        }
    });
    applyFilters();
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
document.addEventListener('DOMContentLoaded', initializeGallery);
