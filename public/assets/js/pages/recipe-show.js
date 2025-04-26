/**
 * @fileoverview Recipe Show page functionality for FlavorConnect
 * @author Henry Vaughn
 * @license MIT
 * @description Handles the interactive functionality on the recipe detail page.
 * This script manages user interactions including:
 * - Recipe rating system
 * - Comment submission
 * - Print functionality
 * @version 1.1.0
 */

// Add to FlavorConnect namespace
window.FlavorConnect = window.FlavorConnect || {};
window.FlavorConnect.pages = window.FlavorConnect.pages || {};

// Recipe show page module
window.FlavorConnect.pages.recipeShow = (function() {
    'use strict';
    
    // Configuration
    const config = {
        selectors: {
            starRating: '.star-rating input.star-input',
            starLabels: '.star-rating label',
            commentForm: 'form',
            printButton: '#printRecipeBtn',
            hiddenRatingInput: '#rating-value'
        },
        classes: {
            active: 'active'
        }
    };
    
    // State management
    const state = {
        currentRating: 0,
        isSubmitting: false
    };
    
    /**
     * Safely adds an event listener with error handling
     * @param {HTMLElement} element - The DOM element to attach the listener to
     * @param {string} event - The event type to listen for
     * @param {Function} handler - The event handler function
     * @private
     */
    function addSafeEventListener(element, event, handler) {
        if (element) {
            element.addEventListener(event, handler);
        }
    }
    
    /**
     * Updates the visual state of the star rating
     * This ensures the stars stay highlighted when selected
     * @private
     */
    function updateStarRating() {
        const ratingInputs = document.querySelectorAll(config.selectors.starRating);
        const labels = document.querySelectorAll(config.selectors.starLabels);
        
        // First, clear all active states
        labels.forEach(label => {
            label.classList.remove(config.classes.active);
        });
        
        // Then, set the active state based on the current rating
        ratingInputs.forEach(input => {
            const value = parseInt(input.value, 10);
            const label = document.querySelector(`label[for="${input.id}"]`);
            
            if (value <= state.currentRating) {
                label.classList.add(config.classes.active);
            }
            
            // If this input is the one that's checked, make sure it stays checked
            if (value === state.currentRating) {
                input.checked = true;
            }
        });
    }
    
    /**
     * Handles rating click
     * @param {Event} event - The click event object
     * @private
     */
    function handleRatingClick(event) {
        const starInput = event.target.closest('input.star-input');
        if (!starInput) return;
        
        // Update the current rating in our state
        state.currentRating = parseInt(starInput.value, 10);
        
        // Update the hidden input value to make form validation work
        const hiddenInput = document.querySelector(config.selectors.hiddenRatingInput);
        if (hiddenInput) {
            hiddenInput.value = state.currentRating;
        }
        
        // Update the visual state of all stars
        updateStarRating();
    }
    
    /**
     * Handles comment form submission
     * @param {Event} event - The submit event object
     * @private
     */
    function handleCommentSubmit(event) {
        // Prevent double submissions
        if (state.isSubmitting) {
            event.preventDefault();
            return;
        }
        
        state.isSubmitting = true;
        
        // Comment submission functionality would go here
        // In a real implementation, we'd handle the AJAX submission
        // and reset state.isSubmitting when complete
        
        // For now, just log it
        console.log('Comment submitted');
        
        // Reset submission state after a delay (simulating AJAX)
        setTimeout(() => {
            state.isSubmitting = false;
        }, 1000);
    }
    
    /**
     * Sets up event listeners for the page
     * Attaches handlers to rating stars, comment form, and print button
     * @private
     */
    function setupEventListeners() {
        // Rating functionality
        const ratingInputs = document.querySelectorAll(config.selectors.starRating);
        ratingInputs.forEach(input => {
            addSafeEventListener(input, 'change', handleRatingClick);
        });
    
        // Comment functionality
        const commentForm = document.querySelector(config.selectors.commentForm);
        if (commentForm) {
            addSafeEventListener(commentForm, 'submit', handleCommentSubmit);
        }
    
        // Print functionality
        const printBtn = document.getElementById('printRecipeBtn');
        if (printBtn) {
            addSafeEventListener(printBtn, 'click', function() {
                window.print();
            });
        }
    }
    
    /**
     * Initializes recipe show page functionality
     * @public
     */
    function initialize() {
        setupEventListeners();
    }
    
    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', initialize);
    
    // Return public API
    return {
        init: initialize,
        getCurrentRating: function() {
            return state.currentRating;
        }
    };
})();
