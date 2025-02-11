/**
 * @fileoverview Recipe Show page functionality for FlavorConnect
 * @author FlavorConnect Team
 * @version 1.0.0
 * @license MIT
 */

import { 
    fetchData, 
    formatTime, 
    formatDate, 
    debounce, 
    toggleVisibility,
    addSafeEventListener 
} from '../utils/common.js';

// State management
const state = {
    recipe: null,
    comments: [],
    currentRating: 0,
    isFavorited: false
};

/**
 * Initializes recipe show page functionality
 */
function initializeRecipeShow() {
    loadRecipeData();
    setupEventListeners();
}

/**
 * Sets up event listeners for the page
 */
function setupEventListeners() {
    // Rating functionality
    const ratingStars = document.querySelectorAll('.rating-star');
    ratingStars.forEach(star => {
        addSafeEventListener(star, 'click', handleRatingClick);
        addSafeEventListener(star, 'mouseover', handleRatingHover);
        addSafeEventListener(star, 'mouseout', handleRatingReset);
    });

    // Comment functionality
    const commentForm = document.querySelector('#comment-form');
    if (commentForm) {
        addSafeEventListener(commentForm, 'submit', handleCommentSubmit);
    }

    // Favorite functionality
    const favoriteBtn = document.querySelector('.favorite-btn');
    if (favoriteBtn) {
        addSafeEventListener(favoriteBtn, 'click', handleFavoriteToggle);
    }

    // Print recipe
    const printButton = document.querySelector('.print-recipe');
    if (printButton) {
        addSafeEventListener(printButton, 'click', handlePrint);
    }

    // Share recipe
    const shareButton = document.querySelector('.share-recipe');
    if (shareButton) {
        addSafeEventListener(shareButton, 'click', handleShare);
    }
}

/**
 * Loads recipe data and initializes state
 */
async function loadRecipeData() {
    try {
        // Load recipe data from the page
        const recipeDataScript = document.getElementById('recipe-data');
        if (recipeDataScript) {
            state.recipe = JSON.parse(recipeDataScript.textContent);
            state.isFavorited = state.recipe.is_favorited;
            state.comments = state.recipe.comments || [];
            updateFavoriteButton();
            updateCommentsUI();
        }

        // Update recipe UI
        updateRecipeUI();
    } catch (error) {
        console.error('Error loading recipe data:', error);
        showError('Failed to load recipe data');
    }
}

/**
 * Handles toggling recipe favorite status
 */
async function handleFavoriteToggle() {
    try {
        const recipeId = state.recipe.recipe_id;
        const userId = state.recipe.user_id;
        
        if (!recipeId || !userId) {
            console.error('Missing recipe ID or user ID');
            return;
        }

        const url = `${window.API_CONFIG.baseUrl}`;
        const body = {
            action: 'toggle_favorite',
            recipe_id: parseInt(recipeId)
        };

        console.log('Making API request:', {
            url,
            method: 'POST',
            body
        });

        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(body)
        });

        console.log('API response status:', response.status);
        
        const data = await response.json();
        console.log('API response data:', data);

        if (!response.ok) {
            console.error('API error response:', data);
            throw new Error(data.error || 'Failed to update favorite status');
        }
        
        if (data.success) {
            // Update recipe state
            state.isFavorited = !state.isFavorited;

            // Update button UI
            updateFavoriteButton();
            
            showSuccess(data.message || 'Recipe favorite status updated');
        } else {
            throw new Error(data.error || 'Failed to update favorite status');
        }
    } catch (error) {
        console.error('Error toggling favorite:', error);
        showError(error.message || 'Failed to update favorite status');
    }
}

/**
 * Updates favorite button UI based on current state
 */
function updateFavoriteButton() {
    const favoriteBtn = document.querySelector('.favorite-btn');
    if (favoriteBtn) {
        console.log('Updating favorite button state:', state.isFavorited);
        favoriteBtn.classList.toggle('favorited', state.isFavorited);
        favoriteBtn.setAttribute('aria-label', state.isFavorited ? 'Remove from favorites' : 'Add to favorites');
        
        // Update icon if it exists
        const icon = favoriteBtn.querySelector('i');
        if (icon) {
            icon.classList.toggle('fas', state.isFavorited);
            icon.classList.toggle('far', !state.isFavorited);
        }
    } else {
        console.warn('Favorite button not found in DOM');
    }
}

/**
 * Updates the recipe UI with current state
 */
function updateRecipeUI() {
    if (!state.recipe) return;

    // Update times
    const prepTime = document.querySelector('.prep-time');
    const cookTime = document.querySelector('.cook-time');
    const totalTime = document.querySelector('.total-time');

    if (prepTime) prepTime.textContent = formatTime(state.recipe.prep_time);
    if (cookTime) cookTime.textContent = formatTime(state.recipe.cook_time);
    if (totalTime) {
        const total = (parseInt(state.recipe.prep_time) || 0) + (parseInt(state.recipe.cook_time) || 0);
        totalTime.textContent = formatTime(total);
    }

    // Update dates
    const publishDate = document.querySelector('.publish-date');
    if (publishDate) {
        publishDate.textContent = formatDate(state.recipe.created_at);
    }

    // Update rating
    updateRatingDisplay(state.recipe.average_rating);

    // Update ingredients
    const ingredientsList = document.querySelector('.recipe-ingredients-list');
    if (ingredientsList && state.recipe.ingredients) {
        ingredientsList.innerHTML = state.recipe.ingredients.map(ing => `
            <li class="ingredient-item">
                <span class="amount">${ing.amount}</span>
                <span class="unit">${ing.unit}</span>
                <span class="name">${ing.name}</span>
            </li>
        `).join('');
    }

    // Update steps
    const stepsList = document.querySelector('.recipe-steps-list');
    if (stepsList && state.recipe.steps) {
        // Sort steps by step number
        const sortedSteps = [...state.recipe.steps].sort((a, b) => a.step_number - b.step_number);
        stepsList.innerHTML = sortedSteps.map(step => `
            <li class="step-item">
                <span class="step-number">${step.step_number}.</span>
                <span class="instruction">${step.instruction}</span>
            </li>
        `).join('');
    }
}

/**
 * Handles rating star click
 * @param {Event} e - Click event from rating star
 */
async function handleRatingClick(e) {
    const rating = parseInt(e.target.dataset.rating);
    const recipeId = state.recipe.id;

    try {
        const response = await fetchData('/api/recipes', {
            method: 'POST',
            body: JSON.stringify({
                action: 'rate_recipe',
                recipe_id: recipeId,
                rating: rating
            })
        });

        if (response.success) {
            state.currentRating = rating;
            updateRatingDisplay(rating);
            showSuccess('Rating saved successfully');
        }
    } catch (error) {
        console.error('Error saving rating:', error);
        showError('Failed to save rating');
    }
}

/**
 * Handles rating star hover
 * @param {Event} e - Mouseover event from rating star
 */
function handleRatingHover(e) {
    const rating = parseInt(e.target.dataset.rating);
    updateRatingDisplay(rating, true);
}

/**
 * Handles rating star mouse out
 */
function handleRatingReset() {
    updateRatingDisplay(state.currentRating || state.recipe.average_rating);
}

/**
 * Updates rating display
 * @param {number} rating - Rating value to display
 * @param {boolean} [isHover=false] - Whether this is a hover state
 */
function updateRatingDisplay(rating, isHover = false) {
    const stars = document.querySelectorAll('.rating-star');
    stars.forEach((star, index) => {
        const starRating = index + 1;
        star.classList.toggle('active', starRating <= rating);
        if (isHover) {
            star.classList.toggle('hover', starRating <= rating);
        } else {
            star.classList.remove('hover');
        }
    });
}

/**
 * Handles comment form submission
 * @param {Event} e - Submit event from comment form
 */
async function handleCommentSubmit(e) {
    e.preventDefault();
    const form = e.target;
    const comment = form.querySelector('textarea').value.trim();

    if (!comment) {
        showError('Please enter a comment');
        return;
    }

    try {
        const response = await fetchData('/api/recipes', {
            method: 'POST',
            body: JSON.stringify({
                action: 'add_comment',
                recipe_id: state.recipe.id,
                comment: comment
            })
        });

        if (response.success) {
            form.reset();
            state.comments.push({
                user_name: 'You',
                rating: state.currentRating,
                comment_text: comment,
                created_at: new Date().toISOString()
            });
            updateCommentsUI();
            showSuccess('Comment added successfully');
        }
    } catch (error) {
        console.error('Error adding comment:', error);
        showError('Failed to add comment');
    }
}

/**
 * Updates the comments UI with current state
 */
function updateCommentsUI() {
    const commentsContainer = document.querySelector('.comments-list');
    if (!commentsContainer) return;

    if (!state.comments || state.comments.length === 0) {
        commentsContainer.innerHTML = '<p class="no-comments">No comments yet. Be the first to review this recipe!</p>';
        return;
    }

    const commentHTML = state.comments.map(comment => `
        <div class="comment">
            <div class="comment-header">
                <span class="user-name">${comment.user_name}</span>
                <div class="rating">
                    ${generateStarRating(comment.rating)}
                </div>
                <span class="date">${formatDate(comment.created_at)}</span>
            </div>
            <div class="comment-text">
                ${comment.comment_text}
            </div>
        </div>
    `).join('');

    commentsContainer.innerHTML = commentHTML;
}

/**
 * Generates HTML for star rating display
 * @param {number} rating - Rating value (1-5)
 * @returns {string} HTML string for star rating
 */
function generateStarRating(rating) {
    let stars = '';
    for (let i = 1; i <= 5; i++) {
        stars += `<i class="fas fa-star${i <= rating ? '' : ' empty'}"></i>`;
    }
    return stars;
}

/**
 * Handles print button click
 */
function handlePrint() {
    window.print();
}

/**
 * Handles share button click
 */
async function handleShare() {
    const shareUrl = window.location.href;
    
    if (navigator.share) {
        try {
            await navigator.share({
                title: state.recipe.title,
                text: state.recipe.description,
                url: shareUrl
            });
            showSuccess('Recipe shared successfully');
        } catch (error) {
            if (error.name !== 'AbortError') {
                console.error('Error sharing recipe:', error);
                showError('Failed to share recipe');
            }
        }
    } else {
        // Fallback to copying to clipboard
        navigator.clipboard.writeText(shareUrl)
            .then(() => showSuccess('Recipe URL copied to clipboard'))
            .catch(error => {
                console.error('Error copying URL:', error);
                showError('Failed to copy recipe URL');
            });
    }
}

/**
 * Handles save button click
 */
async function handleSave() {
    try {
        const { recipe } = state;
        if (!recipe || !recipe.user_id) {
            console.error('Missing recipe data or user_id');
            return;
        }

        const url = `${window.API_CONFIG.baseUrl}`;
        const body = {
            action: 'toggle_favorite',
            recipe_id: recipe.recipe_id
        };

        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(body)
        });

        if (!response.ok) {
            const errorData = await response.json().catch(() => ({}));
            throw new Error(errorData.message || 'Failed to save recipe');
        }

        const data = await response.json();
        if (data.success) {
            state.isFavorited = data.is_favorited;
            updateFavoriteButton();
            showSuccess(data.message);
        } else {
            throw new Error(data.message || 'Failed to save recipe');
        }
    } catch (error) {
        console.error('Error saving recipe:', error);
        showError('Failed to save recipe');
    }
}

/**
 * Shows success message
 * @param {string} message - Success message to display
 */
function showSuccess(message) {
    const alert = document.querySelector('.alert-success');
    if (alert) {
        alert.textContent = message;
        toggleVisibility(alert);
        setTimeout(() => toggleVisibility(alert, false), 3000);
    }
}

/**
 * Shows error message
 * @param {string} message - Error message to display
 */
function showError(message) {
    const alert = document.querySelector('.alert-error');
    if (alert) {
        alert.textContent = message;
        toggleVisibility(alert);
        setTimeout(() => toggleVisibility(alert, false), 3000);
    }
}

// Initialize star rating behavior
function initializeStarRating() {
    const starRating = document.querySelector('.star-rating');
    if (!starRating) return;

    const stars = starRating.querySelectorAll('label');
    const inputs = starRating.querySelectorAll('input');
    let selectedRating = 0;
    
    // Light up stars based on rating
    function updateStars(rating) {
        stars.forEach((star, index) => {
            star.style.color = index < rating ? '#ffd700' : '#ddd';
        });
    }

    stars.forEach((star, index) => {
        // Handle hover
        star.addEventListener('mouseenter', () => {
            if (!selectedRating) {
                updateStars(index + 1);
            }
        });

        // Handle click
        star.addEventListener('click', (e) => {
            e.preventDefault();
            selectedRating = index + 1;
            updateStars(selectedRating);
            inputs[index].checked = true;
        });
    });

    starRating.addEventListener('mouseleave', () => {
        updateStars(selectedRating);
    });
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    initializeRecipeShow();
    initializeStarRating();
});
