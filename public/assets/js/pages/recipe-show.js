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

// Get recipe data from JSON script tag
const recipeData = JSON.parse(document.getElementById('recipe-data-json').textContent);

// State management
const state = {
    recipe: recipeData,
    comments: recipeData.comments,
    currentRating: 0,
    isSubmitting: false
};

/**
 * Initializes recipe show page functionality
 */
function initializeRecipeShow() {
    setupEventListeners();
    updateRecipeUI();
    updateCommentsUI();
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
            state.comments = state.recipe.comments || [];
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
}

/**
 * Handles rating star click
 * @param {Event} e - Click event from rating star
 */
function handleRatingClick(e) {
    const rating = parseInt(e.target.dataset.rating);
    if (!rating) return;

    state.currentRating = rating;
    updateRatingDisplay(rating);
}

/**
 * Handles rating star hover
 * @param {Event} e - Mouseover event from rating star
 */
function handleRatingHover(e) {
    const rating = parseInt(e.target.dataset.rating);
    if (!rating) return;

    updateRatingDisplay(rating, true);
}

/**
 * Handles rating star mouse out
 */
function handleRatingReset() {
    updateRatingDisplay(state.currentRating);
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
        if (starRating <= rating) {
            star.classList.add('active');
        } else {
            star.classList.remove('active');
        }
    });
}

/**
 * Handles comment form submission
 * @param {Event} e - Submit event from comment form
 */
async function handleCommentSubmit(e) {
    e.preventDefault();
    if (state.isSubmitting) return;

    try {
        state.isSubmitting = true;
        const form = e.target;
        const formData = new FormData(form);

        const response = await fetchData('/api/comments', {
            method: 'POST',
            body: formData
        });

        if (response.success) {
            // Add new comment to state
            state.comments.push(response.comment);
            updateCommentsUI();
            form.reset();
            showSuccess('Comment added successfully');
        } else {
            throw new Error(response.error || 'Failed to add comment');
        }
    } catch (error) {
        console.error('Error submitting comment:', error);
        showError(error.message || 'Failed to add comment');
    } finally {
        state.isSubmitting = false;
    }
}

/**
 * Updates the comments UI with current state
 */
function updateCommentsUI() {
    const commentsContainer = document.querySelector('.comments-list');
    if (!commentsContainer) return;

    if (!state.comments || state.comments.length === 0) {
        commentsContainer.innerHTML = '<p class="no-comments">No comments yet. Be the first to comment!</p>';
        return;
    }

    const commentHTML = state.comments.map(comment => `
        <div class="comment">
            <div class="comment-header">
                <span class="comment-author">${comment.user_name}</span>
                <span class="comment-date">${formatDate(comment.created_at)}</span>
                ${comment.rating ? generateStarRating(comment.rating) : ''}
            </div>
            <div class="comment-content">${comment.content}</div>
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
    return `
        <div class="star-rating">
            ${Array.from({ length: 5 }, (_, i) => `
                <i class="fas fa-star${i < rating ? ' active' : ''}"></i>
            `).join('')}
        </div>
    `;
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
function handleShare() {
    const shareUrl = window.location.href;
    const shareTitle = state.recipe.title;
    const shareText = `Check out this recipe for ${shareTitle} on FlavorConnect!`;

    if (navigator.share) {
        navigator.share({
            title: shareTitle,
            text: shareText,
            url: shareUrl
        }).catch(error => {
            console.error('Error sharing:', error);
            showError('Failed to share recipe');
        });
    } else {
        // Fallback to copying URL
        navigator.clipboard.writeText(shareUrl).then(() => {
            showSuccess('Recipe URL copied to clipboard');
        }).catch(error => {
            console.error('Error copying URL:', error);
            showError('Failed to copy recipe URL');
        });
    }
}

/**
 * Shows success message
 * @param {string} message - Success message to display
 */
function showSuccess(message) {
    const toast = document.querySelector('.toast.success');
    if (toast) {
        toast.textContent = message;
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 3000);
    }
}

/**
 * Shows error message
 * @param {string} message - Error message to display
 */
function showError(message) {
    const toast = document.querySelector('.toast.error');
    if (toast) {
        toast.textContent = message;
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 3000);
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    initializeRecipeShow();
});
