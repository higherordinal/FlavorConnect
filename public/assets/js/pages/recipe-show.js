/**
 * @fileoverview Recipe Show page functionality for FlavorConnect
 * @author Henry Vaughn
 * @license MIT
 * @description Handles the interactive functionality on the recipe detail page.
 * This script manages user interactions including:
 * - Recipe rating system
 * - Comment submission
 * - Print functionality (handled in show.php)
 */

// Functions from common.js that we need
/**
 * Safely adds an event listener with error handling
 * @param {HTMLElement} element - The DOM element to attach the listener to
 * @param {string} event - The event type to listen for
 * @param {Function} handler - The event handler function
 */
function addSafeEventListener(element, event, handler) {
    if (element) {
        element.addEventListener(event, handler);
    }
}

// State management
const state = {
    currentRating: 0,
    isSubmitting: false
};

/**
 * Updates the visual state of the star rating
 * This ensures the stars stay highlighted when selected
 */
function updateStarRating() {
    const ratingInputs = document.querySelectorAll('.star-rating input[type="radio"]');
    const labels = document.querySelectorAll('.star-rating label');
    
    // First, clear all active states
    labels.forEach(label => {
        label.classList.remove('active');
    });
    
    // Then, set the active state based on the current rating
    ratingInputs.forEach(input => {
        const value = parseInt(input.value, 10);
        const label = document.querySelector(`label[for="${input.id}"]`);
        
        if (value <= state.currentRating) {
            label.classList.add('active');
        }
        
        // If this input is the one that's checked, make sure it stays checked
        if (value === state.currentRating) {
            input.checked = true;
        }
    });
}

/**
 * Initializes recipe show page functionality
 * Sets up all event listeners and initializes the page state
 */
function initializeRecipeShow() {
    setupEventListeners();
}

/**
 * Sets up event listeners for the page
 * Attaches handlers to rating stars and comment form
 */
function setupEventListeners() {
    // Rating functionality
    const ratingInputs = document.querySelectorAll('.star-rating input[type="radio"]');
    ratingInputs.forEach(input => {
        addSafeEventListener(input, 'change', handleRatingClick);
    });

    // Comment functionality
    const commentForm = document.querySelector('form');
    if (commentForm) {
        addSafeEventListener(commentForm, 'submit', handleCommentSubmit);
    }

    // Note: Print functionality is now handled directly in show.php
}

/**
 * Handles rating click
 * @param {Event} event - The click event object
 */
function handleRatingClick(event) {
    const starInput = event.target.closest('input[type="radio"]');
    if (!starInput) return;
    
    // Update the current rating in our state
    state.currentRating = parseInt(starInput.value, 10);
    
    // Update the visual state of all stars
    updateStarRating();
}

/**
 * Handles comment form submission
 * @param {Event} event - The submit event object
 */
function handleCommentSubmit(event) {
    // Comment submission functionality would go here
    console.log('Comment submitted');
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    initializeRecipeShow();
});
