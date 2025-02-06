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
    currentRating: 0
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

    // Save recipe
    const saveButton = document.querySelector('.save-recipe');
    if (saveButton) {
        addSafeEventListener(saveButton, 'click', handleSave);
    }
}

/**
 * Loads recipe data from the server
 */
async function loadRecipeData() {
    try {
        // Use the data embedded in the page
        state.recipe = window.recipeData;
        updateRecipeUI();
    } catch (error) {
        console.error('Error loading recipe:', error);
        showError('Failed to load recipe data');
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
            await loadComments();
            showSuccess('Comment added successfully');
        }
    } catch (error) {
        console.error('Error adding comment:', error);
        showError('Failed to add comment');
    }
}

/**
 * Loads comments for the recipe
 */
async function loadComments() {
    try {
        const response = await fetchData(`/api/recipes?action=get_comments&recipe_id=${state.recipe.id}`);
        
        if (response.success) {
            state.comments = response.comments;
            updateCommentsUI();
        }
    } catch (error) {
        console.error('Error loading comments:', error);
        showError('Failed to load comments');
    }
}

/**
 * Updates the comments UI
 */
function updateCommentsUI() {
    const commentsContainer = document.querySelector('.comments-list');
    if (!commentsContainer) return;

    commentsContainer.innerHTML = state.comments.map(comment => `
        <div class="comment">
            <div class="comment-header">
                <span class="comment-author">${comment.author}</span>
                <span class="comment-date">${formatDate(comment.created_at)}</span>
            </div>
            <div class="comment-content">${comment.content}</div>
        </div>
    `).join('');
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
        const response = await fetchData('/api/recipes', {
            method: 'POST',
            body: JSON.stringify({
                action: 'toggle_favorite',
                recipe_id: state.recipe.id
            })
        });

        if (response.success) {
            const saveButton = document.querySelector('.save-recipe');
            const isSaved = saveButton.classList.toggle('saved');
            showSuccess(isSaved ? 'Recipe saved to favorites' : 'Recipe removed from favorites');
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
