/**
 * @fileoverview Recipe Filter Component for FlavorConnect
 * @author FlavorConnect Team
 * @version 1.1.0
 * @license MIT
 * @description Provides client-side filtering for recipes with server-side fallback.
 * This component enhances the existing PHP-based filtering without disrupting current functionality.
 * 
 * Features include:
 * - Text search with debounce
 * - Filtering by style, diet, and type
 * - Sorting options
 * - Pagination
 * - URL state management
 * - Graceful fallback to server-side filtering
 */

// Global state for recipe filtering
window.recipeFilterState = {
    // Filter values
    filters: {
        search: '',
        style: '',
        diet: '',
        type: '',
        sort: 'newest'
    },
    
    // Pagination
    pagination: {
        currentPage: 1,
        totalPages: 1,
        itemsPerPage: 12
    },
    
    // UI elements (will be populated on init)
    elements: {},
    
    // Flags
    isInitialized: false,
    isClientSideFiltering: false, // Default to server-side filtering
    isLoading: false
};

/**
 * Initialize the recipe filter component
 * @param {Object} options Configuration options
 * @param {boolean} [options.useClientSide=false] Whether to use client-side filtering
 * @param {Array} [options.initialRecipes=[]] Initial recipes data for client-side filtering
 * @param {number|null} [options.userId=null] Current user ID
 * @param {boolean} [options.isLoggedIn=false] Whether user is logged in
 */
window.initRecipeFilter = function(options = {}) {
    // Only initialize once
    if (window.recipeFilterState.isInitialized) return;
    
    // Merge options with defaults
    const config = {
        useClientSide: false,
        initialRecipes: [],
        userId: null,
        isLoggedIn: false,
        ...options
    };
    
    // Store initial recipes if provided
    if (Array.isArray(config.initialRecipes) && config.initialRecipes.length > 0) {
        window.recipeFilterState.recipes = config.initialRecipes;
        window.recipeFilterState.allRecipes = [...config.initialRecipes];
        window.recipeFilterState.isClientSideFiltering = config.useClientSide;
    }
    
    // Store user info
    window.recipeFilterState.userId = config.userId;
    window.recipeFilterState.isLoggedIn = config.isLoggedIn;
    
    // Cache DOM elements
    cacheFilterElements();
    
    // Set up event listeners
    setupFilterEventListeners();
    
    // Initialize filter values from URL
    initializeFromUrl();
    
    // Mark as initialized
    window.recipeFilterState.isInitialized = true;
    
    console.log('Recipe filter initialized', 
        window.recipeFilterState.isClientSideFiltering ? 'using client-side filtering' : 'using server-side filtering');
};

/**
 * Cache DOM elements for better performance
 * Stores references to all filter-related DOM elements
 */
function cacheFilterElements() {
    const elements = window.recipeFilterState.elements;
    
    elements.recipeGrid = document.querySelector('.recipe-grid');
    elements.searchInput = document.querySelector('input[name="search"]');
    elements.styleSelect = document.getElementById('style-filter');
    elements.dietSelect = document.getElementById('diet-filter');
    elements.typeSelect = document.getElementById('type-filter');
    elements.sortSelect = document.getElementById('sort-filter');
    elements.paginationContainer = document.querySelector('.pagination');
    elements.noResultsMessage = document.querySelector('.no-results');
    
    // Create no results message if it doesn't exist
    if (!elements.noResultsMessage && elements.recipeGrid) {
        const noResults = document.createElement('div');
        noResults.className = 'no-results';
        noResults.innerHTML = '<p>No recipes found matching your criteria.</p>';
        noResults.style.display = 'none';
        
        elements.recipeGrid.parentNode.insertBefore(noResults, elements.recipeGrid.nextSibling);
        elements.noResultsMessage = noResults;
    }
}

/**
 * Set up event listeners for filter controls
 * Attaches event handlers to search input, dropdowns, and form submission
 */
function setupFilterEventListeners() {
    const elements = window.recipeFilterState.elements;
    
    // Only set up if we have the elements
    if (!elements.searchInput) return;
    
    // Prevent default form submission and use our filtering instead
    const searchForm = elements.searchInput.closest('form');
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            if (window.recipeFilterState.isClientSideFiltering) {
                e.preventDefault();
                updateFilters({
                    search: elements.searchInput.value
                });
                applyFilters();
            }
        });
    }
    
    // Add input event with debounce for search
    if (elements.searchInput) {
        elements.searchInput.addEventListener('input', debounce(function() {
            if (window.recipeFilterState.isClientSideFiltering) {
                updateFilters({
                    search: this.value
                });
                applyFilters();
            }
        }, 300));
    }
    
    // Add change events for dropdowns
    if (elements.styleSelect) {
        elements.styleSelect.addEventListener('change', function() {
            if (window.recipeFilterState.isClientSideFiltering) {
                updateFilters({
                    style: this.value
                });
                applyFilters();
            }
        });
    }
    
    if (elements.dietSelect) {
        elements.dietSelect.addEventListener('change', function() {
            if (window.recipeFilterState.isClientSideFiltering) {
                updateFilters({
                    diet: this.value
                });
                applyFilters();
            }
        });
    }
    
    if (elements.typeSelect) {
        elements.typeSelect.addEventListener('change', function() {
            if (window.recipeFilterState.isClientSideFiltering) {
                updateFilters({
                    type: this.value
                });
                applyFilters();
            }
        });
    }
    
    if (elements.sortSelect) {
        elements.sortSelect.addEventListener('change', function() {
            if (window.recipeFilterState.isClientSideFiltering) {
                updateFilters({
                    sort: this.value
                });
                applyFilters();
            }
        });
    }
}

/**
 * Initialize filter values from URL parameters
 */
function initializeFromUrl() {
    const urlParams = new URLSearchParams(window.location.search);
    const state = window.recipeFilterState;
    
    // Update filter state from URL
    state.filters.search = urlParams.get('search') || '';
    state.filters.style = urlParams.get('style') || '';
    state.filters.diet = urlParams.get('diet') || '';
    state.filters.type = urlParams.get('type') || '';
    state.filters.sort = urlParams.get('sort') || 'newest';
    state.pagination.currentPage = parseInt(urlParams.get('page') || '1', 10);
    
    // Update UI to match filter state
    const elements = state.elements;
    
    if (elements.searchInput) {
        elements.searchInput.value = state.filters.search;
    }
    
    if (elements.styleSelect) {
        elements.styleSelect.value = state.filters.style;
    }
    
    if (elements.dietSelect) {
        elements.dietSelect.value = state.filters.diet;
    }
    
    if (elements.typeSelect) {
        elements.typeSelect.value = state.filters.type;
    }
    
    if (elements.sortSelect) {
        elements.sortSelect.value = state.filters.sort;
    }
}

/**
 * Update filter values
 * @param {Object} newFilters - New filter values
 */
function updateFilters(newFilters) {
    // Update filter state
    window.recipeFilterState.filters = {
        ...window.recipeFilterState.filters,
        ...newFilters
    };
    
    // Reset to first page when filters change
    if (newFilters.search !== undefined || 
        newFilters.style !== undefined || 
        newFilters.diet !== undefined || 
        newFilters.type !== undefined) {
        window.recipeFilterState.pagination.currentPage = 1;
    }
    
    // Update URL to reflect new filters
    updateUrl();
}

/**
 * Update URL with current filter state
 */
function updateUrl() {
    if (!window.recipeFilterState.isClientSideFiltering) return;
    
    const urlParams = new URLSearchParams();
    const filters = window.recipeFilterState.filters;
    const pagination = window.recipeFilterState.pagination;
    
    // Only add parameters that have values
    if (filters.search) {
        urlParams.set('search', filters.search);
    }
    
    if (filters.style) {
        urlParams.set('style', filters.style);
    }
    
    if (filters.diet) {
        urlParams.set('diet', filters.diet);
    }
    
    if (filters.type) {
        urlParams.set('type', filters.type);
    }
    
    if (filters.sort && filters.sort !== 'newest') {
        urlParams.set('sort', filters.sort);
    }
    
    if (pagination.currentPage > 1) {
        urlParams.set('page', pagination.currentPage.toString());
    }
    
    // Update URL without reloading page
    const newUrl = window.location.pathname + (urlParams.toString() ? '?' + urlParams.toString() : '');
    window.history.pushState({ path: newUrl }, '', newUrl);
}

/**
 * Apply filters to recipes
 */
function applyFilters() {
    const state = window.recipeFilterState;
    
    if (!state.isClientSideFiltering) {
        // Use server-side filtering
        window.location.href = window.location.pathname + '?' + new URLSearchParams(state.filters).toString();
        return;
    }
    
    // Show loading state
    showLoading(true);
    
    // Use setTimeout to allow UI to update before filtering
    setTimeout(() => {
        try {
            // Filter recipes
            const filteredRecipes = filterRecipes();
            
            // Update pagination
            updatePagination(filteredRecipes.length);
            
            // Get current page of recipes
            const paginatedRecipes = paginateRecipes(filteredRecipes);
            
            // Update UI
            updateRecipeGrid(paginatedRecipes);
        } catch (error) {
            console.error('Error applying filters:', error);
            // Fall back to server-side filtering
            window.location.href = window.location.pathname + '?' + new URLSearchParams(state.filters).toString();
        } finally {
            // Hide loading state
            showLoading(false);
        }
    }, 10);
}

/**
 * Filter recipes based on current filter state
 * @returns {Array} Filtered recipes
 */
function filterRecipes() {
    const filters = window.recipeFilterState.filters;
    
    return window.recipeFilterState.allRecipes.filter(recipe => {
        // Search filter
        if (filters.search) {
            const searchLower = filters.search.toLowerCase();
            const titleMatches = recipe.title.toLowerCase().includes(searchLower);
            const descMatches = recipe.description && recipe.description.toLowerCase().includes(searchLower);
            
            if (!titleMatches && !descMatches) {
                return false;
            }
        }
        
        // Style filter
        if (filters.style && recipe.style_id.toString() !== filters.style) {
            return false;
        }
        
        // Diet filter
        if (filters.diet && recipe.diet_id.toString() !== filters.diet) {
            return false;
        }
        
        // Type filter
        if (filters.type && recipe.type_id.toString() !== filters.type) {
            return false;
        }
        
        return true;
    }).sort((a, b) => {
        // Sort recipes
        switch (filters.sort) {
            case 'oldest':
                return new Date(a.created_at) - new Date(b.created_at);
            case 'rating':
                const aRating = a.rating || 0;
                const bRating = b.rating || 0;
                return bRating - aRating || new Date(b.created_at) - new Date(a.created_at);
            case 'name_asc':
                return a.title.localeCompare(b.title);
            case 'name_desc':
                return b.title.localeCompare(a.title);
            case 'newest':
            default:
                return new Date(b.created_at) - new Date(a.created_at);
        }
    });
}

/**
 * Update pagination based on total recipes
 * @param {number} totalRecipes - Total number of recipes
 */
function updatePagination(totalRecipes) {
    const pagination = window.recipeFilterState.pagination;
    
    pagination.totalPages = Math.max(1, Math.ceil(totalRecipes / pagination.itemsPerPage));
    
    // Ensure current page is valid
    if (pagination.currentPage > pagination.totalPages) {
        pagination.currentPage = pagination.totalPages;
        updateUrl();
    }
    
    // Update pagination UI
    renderPagination();
}

/**
 * Get current page of recipes
 * @param {Array} recipes - Filtered recipes
 * @returns {Array} Paginated recipes
 */
function paginateRecipes(recipes) {
    const pagination = window.recipeFilterState.pagination;
    const startIndex = (pagination.currentPage - 1) * pagination.itemsPerPage;
    const endIndex = startIndex + pagination.itemsPerPage;
    return recipes.slice(startIndex, endIndex);
}

/**
 * Update recipe grid with filtered recipes
 * @param {Array} recipes - Recipes to display
 */
function updateRecipeGrid(recipes) {
    const elements = window.recipeFilterState.elements;
    
    if (!elements.recipeGrid) return;
    
    if (recipes.length === 0) {
        // Show no results message
        elements.recipeGrid.style.display = 'none';
        if (elements.noResultsMessage) {
            elements.noResultsMessage.style.display = 'block';
        }
        return;
    }
    
    // Hide no results message
    if (elements.noResultsMessage) {
        elements.noResultsMessage.style.display = 'none';
    }
    
    // Show recipe grid
    elements.recipeGrid.style.display = 'grid';
    
    // Generate HTML for recipes
    const recipeHtml = recipes.map(recipe => {
        const imagePath = recipe.img_file_path 
            ? `/assets/images/recipes/${recipe.img_file_path}` 
            : '/assets/images/placeholder-recipe.jpg';
        
        const ratingStars = generateRatingStars(recipe.rating || 0);
        const favoriteClass = recipe.is_favorited ? 'favorited' : '';
        const favoriteIcon = recipe.is_favorited ? 'fas fa-heart' : 'far fa-heart';
        
        return `
            <div class="recipe-card">
                <a href="/recipes/show.php?id=${recipe.recipe_id}" class="recipe-link">
                    <div class="recipe-image">
                        <img src="${imagePath}" alt="${recipe.title}" loading="lazy">
                        ${window.recipeFilterState.isLoggedIn ? `
                            <button class="favorite-btn ${favoriteClass}" 
                                    data-recipe-id="${recipe.recipe_id}" 
                                    data-initialized="false"
                                    aria-label="${recipe.is_favorited ? 'Remove from favorites' : 'Add to favorites'}">
                                <i class="${favoriteIcon}"></i>
                            </button>
                        ` : ''}
                    </div>
                    <div class="recipe-info">
                        <h3 class="recipe-title">${recipe.title}</h3>
                        <div class="recipe-meta">
                            <span class="recipe-author">By ${recipe.username || 'Unknown'}</span>
                            <div class="recipe-rating">
                                ${ratingStars}
                                <span class="rating-count">(${recipe.rating_count || 0})</span>
                            </div>
                        </div>
                        <div class="recipe-tags">
                            ${recipe.style ? `<span class="recipe-tag style-tag">${recipe.style}</span>` : ''}
                            ${recipe.diet ? `<span class="recipe-tag diet-tag">${recipe.diet}</span>` : ''}
                            ${recipe.type ? `<span class="recipe-tag type-tag">${recipe.type}</span>` : ''}
                        </div>
                    </div>
                </a>
            </div>
        `;
    }).join('');
    
    // Update recipe grid
    elements.recipeGrid.innerHTML = recipeHtml;
    
    // Initialize favorite buttons if available
    if (window.recipeFilterState.isLoggedIn && typeof window.initializeFavoriteButtonsInGallery === 'function') {
        window.initializeFavoriteButtonsInGallery();
    }
}

/**
 * Generate HTML for rating stars
 * @param {number} rating - Rating value (0-5)
 * @returns {string} HTML for rating stars
 */
function generateRatingStars(rating) {
    const fullStars = Math.floor(rating);
    const halfStar = rating % 1 >= 0.5;
    const emptyStars = 5 - fullStars - (halfStar ? 1 : 0);
    
    let starsHtml = '';
    
    // Full stars
    for (let i = 0; i < fullStars; i++) {
        starsHtml += '<i class="fas fa-star"></i>';
    }
    
    // Half star
    if (halfStar) {
        starsHtml += '<i class="fas fa-star-half-alt"></i>';
    }
    
    // Empty stars
    for (let i = 0; i < emptyStars; i++) {
        starsHtml += '<i class="far fa-star"></i>';
    }
    
    return starsHtml;
}

/**
 * Render pagination controls
 */
function renderPagination() {
    const elements = window.recipeFilterState.elements;
    const pagination = window.recipeFilterState.pagination;
    
    if (!elements.paginationContainer) return;
    
    // Hide pagination if only one page
    if (pagination.totalPages <= 1) {
        elements.paginationContainer.style.display = 'none';
        return;
    }
    
    // Show pagination
    elements.paginationContainer.style.display = 'flex';
    
    const currentPage = pagination.currentPage;
    const totalPages = pagination.totalPages;
    
    let paginationHtml = '';
    
    // Previous button
    if (currentPage > 1) {
        paginationHtml += `
            <a href="#" class="page-link prev" data-page="${currentPage - 1}" aria-label="Previous page">
                <i class="fas fa-chevron-left"></i> Previous
            </a>
        `;
    }
    
    // Page numbers
    const pagesToShow = 5;
    let startPage = Math.max(1, currentPage - Math.floor(pagesToShow / 2));
    let endPage = Math.min(totalPages, startPage + pagesToShow - 1);
    
    // Adjust start page if end page is at max
    if (endPage === totalPages) {
        startPage = Math.max(1, endPage - pagesToShow + 1);
    }
    
    // First page link if not starting at page 1
    if (startPage > 1) {
        paginationHtml += `<a href="#" class="page-link" data-page="1">1</a>`;
        
        if (startPage > 2) {
            paginationHtml += `<span class="page-ellipsis">...</span>`;
        }
    }
    
    // Page links
    for (let i = startPage; i <= endPage; i++) {
        const activeClass = i === currentPage ? 'active' : '';
        paginationHtml += `<a href="#" class="page-link ${activeClass}" data-page="${i}">${i}</a>`;
    }
    
    // Last page link if not ending at last page
    if (endPage < totalPages) {
        if (endPage < totalPages - 1) {
            paginationHtml += `<span class="page-ellipsis">...</span>`;
        }
        
        paginationHtml += `<a href="#" class="page-link" data-page="${totalPages}">${totalPages}</a>`;
    }
    
    // Next button
    if (currentPage < totalPages) {
        paginationHtml += `
            <a href="#" class="page-link next" data-page="${currentPage + 1}" aria-label="Next page">
                Next <i class="fas fa-chevron-right"></i>
            </a>
        `;
    }
    
    // Update pagination container
    elements.paginationContainer.innerHTML = paginationHtml;
    
    // Add event listeners to pagination links
    const pageLinks = elements.paginationContainer.querySelectorAll('.page-link');
    pageLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const page = parseInt(this.dataset.page, 10);
            if (page !== pagination.currentPage) {
                pagination.currentPage = page;
                updateUrl();
                applyFilters();
                
                // Scroll to top of recipe grid
                if (elements.recipeGrid) {
                    elements.recipeGrid.scrollIntoView({ behavior: 'smooth' });
                }
            }
        });
    });
}

/**
 * Show or hide loading state
 * @param {boolean} isLoading - Whether to show loading state
 */
function showLoading(isLoading) {
    window.recipeFilterState.isLoading = isLoading;
    
    // Add loading class to recipe grid
    if (window.recipeFilterState.elements.recipeGrid) {
        if (isLoading) {
            window.recipeFilterState.elements.recipeGrid.classList.add('loading');
        } else {
            window.recipeFilterState.elements.recipeGrid.classList.remove('loading');
        }
    }
}

/**
 * Debounce function to limit how often a function is called
 * @param {Function} func - Function to debounce
 * @param {number} wait - Milliseconds to wait
 * @returns {Function} Debounced function
 */
function debounce(func, wait) {
    let timeout;
    return function() {
        const context = this;
        const args = arguments;
        clearTimeout(timeout);
        timeout = setTimeout(() => {
            func.apply(context, args);
        }, wait);
    };
}

// Expose public methods
window.applyRecipeFilters = applyFilters;
window.toggleRecipeFilteringMode = function(useClientSide) {
    window.recipeFilterState.isClientSideFiltering = useClientSide;
};

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Check if we should auto-initialize
    if (window.autoInitRecipeFilter) {
        window.initRecipeFilter();
    }
});
