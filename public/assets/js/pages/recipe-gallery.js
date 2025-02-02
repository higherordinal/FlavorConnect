import { debounce, formatTime, fetchData } from '../utils/common.js';

document.addEventListener('DOMContentLoaded', function() {
    let recipes = [];
    let currentFilters = {
        search: '',
        style: '',
        diet: '',
        type: '',
        sort: 'newest',
        page: 1
    };
    const recipesPerPage = 12;

    // Initialize filters from URL parameters
    function initializeFilters() {
        const params = new URLSearchParams(window.location.search);
        currentFilters.search = params.get('search') || '';
        currentFilters.style = params.get('style') || '';
        currentFilters.diet = params.get('diet') || '';
        currentFilters.type = params.get('type') || '';
        currentFilters.sort = params.get('sort') || 'newest';
        currentFilters.page = parseInt(params.get('page')) || 1;

        // Set initial form values
        document.querySelector('.search-input').value = currentFilters.search;
        document.querySelector('#style-filter').value = currentFilters.style;
        document.querySelector('#diet-filter').value = currentFilters.diet;
        document.querySelector('#type-filter').value = currentFilters.type;
        document.querySelector('#sort-filter').value = currentFilters.sort;
    }

    // Fetch all recipes on page load
    async function fetchRecipes() {
        console.log('Fetching recipes...');
        try {
            const url = '/FlavorConnect/public/recipes/api.php?action=list';
            console.log('API URL:', url);
            
            const response = await fetchData(url);
            console.log('Response status:', response.status);
            console.log('Response headers:', Object.fromEntries(response.headers));
            
            if (!response.ok) {
                throw new Error(`Network response was not ok: ${response.status} ${response.statusText}`);
            }
            
            const text = await response.text();
            console.log('Raw response:', text);
            
            try {
                recipes = JSON.parse(text);
            } catch (e) {
                console.error('Failed to parse JSON:', e);
                throw new Error('Invalid JSON response from server');
            }
            
            if (recipes.error) {
                throw new Error(recipes.message || 'Failed to load recipes');
            }
            
            console.log('Parsed recipes:', recipes);
            applyFilters();
        } catch (error) {
            console.error('Error fetching recipes:', error);
            document.querySelector('.recipe-grid').innerHTML = `
                <div class="error-message">
                    <p>Sorry, we couldn't load the recipes. Please try again later.</p>
                    <p class="error-details">${error.message}</p>
                </div>
            `;
        }
    }

    // Filter recipes based on current filters
    function filterRecipes(recipes) {
        return recipes.filter(recipe => {
            const searchMatch = !currentFilters.search || 
                recipe.title.toLowerCase().includes(currentFilters.search.toLowerCase()) ||
                recipe.description.toLowerCase().includes(currentFilters.search.toLowerCase());
            
            const styleMatch = !currentFilters.style || 
                recipe.style_id === parseInt(currentFilters.style);
            
            const dietMatch = !currentFilters.diet || 
                recipe.diet_id === parseInt(currentFilters.diet);
            
            const typeMatch = !currentFilters.type || 
                recipe.type_id === parseInt(currentFilters.type);

            return searchMatch && styleMatch && dietMatch && typeMatch;
        });
    }

    // Sort recipes
    function sortRecipes(recipes) {
        return [...recipes].sort((a, b) => {
            switch(currentFilters.sort) {
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
    }

    // Update recipe grid
    function updateRecipeGrid(filteredRecipes) {
        const grid = document.querySelector('.recipe-grid');
        const start = (currentFilters.page - 1) * recipesPerPage;
        const paginatedRecipes = filteredRecipes.slice(start, start + recipesPerPage);
        
        if (paginatedRecipes.length === 0) {
            grid.innerHTML = `
                <div class="no-results">
                    <p>No recipes found matching your criteria.</p>
                    <button onclick="window.location.href='/FlavorConnect/public/recipes/index.php'" class="btn btn-primary">Clear Filters</button>
                </div>
            `;
            return;
        }

        grid.innerHTML = paginatedRecipes.map(recipe => `
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
                            <span><i class="fas fa-clock"></i>${formatTime(recipe.prep_time + recipe.cook_time)}</span>
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
        `).join('');

        // Reinitialize favorite buttons if user is logged in
        if (window.isLoggedIn) {
            initializeFavoriteButtons();
        }

        updatePagination(filteredRecipes.length);
    }

    // Update pagination
    function updatePagination(totalRecipes) {
        const totalPages = Math.ceil(totalRecipes / recipesPerPage);
        const pagination = document.querySelector('.pagination');
        
        if (totalPages <= 1) {
            pagination.style.display = 'none';
            return;
        }

        pagination.style.display = 'flex';
        pagination.innerHTML = `
            ${currentFilters.page > 1 ? `
                <a href="#" class="page-link" data-page="${currentFilters.page - 1}">
                    <i class="fas fa-chevron-left"></i>
                </a>
            ` : ''}
            ${Array.from({length: totalPages}, (_, i) => i + 1).map(page => `
                <a href="#" class="page-link ${page === currentFilters.page ? 'active' : ''}" 
                   data-page="${page}">${page}</a>
            `).join('')}
            ${currentFilters.page < totalPages ? `
                <a href="#" class="page-link" data-page="${currentFilters.page + 1}">
                    <i class="fas fa-chevron-right"></i>
                </a>
            ` : ''}
        `;

        // Add click handlers for pagination
        pagination.querySelectorAll('.page-link').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const newPage = parseInt(e.target.closest('.page-link').dataset.page);
                if (newPage !== currentFilters.page) {
                    currentFilters.page = newPage;
                    applyFilters();
                    // Scroll to top of recipe grid
                    document.querySelector('.recipe-grid').scrollIntoView({ behavior: 'smooth' });
                }
            });
        });
    }

    // Apply all filters and update display
    function applyFilters() {
        const filteredRecipes = filterRecipes(recipes);
        const sortedRecipes = sortRecipes(filteredRecipes);
        updateRecipeGrid(sortedRecipes);
        updateURL();
    }

    // Update URL with current filters
    function updateURL() {
        const params = new URLSearchParams();
        Object.entries(currentFilters).forEach(([key, value]) => {
            if (value) params.set(key, value);
        });
        window.history.pushState({}, '', `${window.location.pathname}?${params.toString()}`);
    }

    // Set up event listeners
    document.querySelector('.search-input').addEventListener('input', debounce(function(e) {
        currentFilters.search = e.target.value;
        currentFilters.page = 1;
        applyFilters();
    }, 300));

    document.querySelectorAll('.filter-select').forEach(select => {
        select.addEventListener('change', function() {
            const filterType = this.id.split('-')[0]; // style-filter -> style
            currentFilters[filterType] = this.value;
            currentFilters.page = 1;
            applyFilters();
        });
    });

    // Initialize and fetch recipes
    initializeFilters();
    fetchRecipes();
});
