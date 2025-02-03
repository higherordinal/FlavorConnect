/**
 * @fileoverview Recipe scaling functionality for FlavorConnect
 * @author FlavorConnect Team
 * @version 1.0.0
 * @license MIT
 */

import { formatFraction } from '../utils/common.js';

// State management
const state = {
    originalServings: 1,
    currentServings: 1,
    ingredients: []
};

/**
 * Initializes recipe scaling functionality
 */
function initializeScaling() {
    captureInitialState();
    setupEventListeners();
}

/**
 * Captures initial recipe state
 */
function captureInitialState() {
    const servingsInput = document.querySelector('#servings');
    if (servingsInput) {
        state.originalServings = parseInt(servingsInput.value) || 1;
        state.currentServings = state.originalServings;
    }

    // Store original ingredient amounts
    state.ingredients = Array.from(document.querySelectorAll('.ingredient-amount')).map(el => ({
        element: el,
        originalAmount: parseFloat(el.dataset.amount) || 0,
        unit: el.dataset.unit || ''
    }));
}

/**
 * Sets up event listeners for scaling controls
 */
function setupEventListeners() {
    // Servings input
    const servingsInput = document.querySelector('#servings');
    if (servingsInput) {
        servingsInput.addEventListener('change', handleServingsChange);
        servingsInput.addEventListener('input', validateInput);
    }

    // Scale buttons
    const scaleButtons = document.querySelectorAll('.scale-btn');
    scaleButtons.forEach(button => {
        button.addEventListener('click', handleScaleButtonClick);
    });

    // Reset button
    const resetButton = document.querySelector('.reset-scale');
    if (resetButton) {
        resetButton.addEventListener('click', resetScale);
    }
}

/**
 * Handles changes to servings input
 * @param {Event} e - Change event from servings input
 */
function handleServingsChange(e) {
    const newServings = parseInt(e.target.value) || 1;
    if (newServings < 1) {
        e.target.value = 1;
        return;
    }

    updateIngredientAmounts(newServings);
    state.currentServings = newServings;
}

/**
 * Validates servings input to ensure only positive numbers
 * @param {Event} e - Input event from servings input
 */
function validateInput(e) {
    const value = e.target.value;
    if (value < 0) {
        e.target.value = 1;
    }
}

/**
 * Handles clicks on scale buttons (half, double)
 * @param {Event} e - Click event from scale button
 */
function handleScaleButtonClick(e) {
    const action = e.currentTarget.dataset.action;
    const servingsInput = document.querySelector('#servings');
    let newServings = state.currentServings;

    switch(action) {
        case 'half':
            newServings = Math.max(1, Math.floor(state.currentServings / 2));
            break;
        case 'double':
            newServings = state.currentServings * 2;
            break;
    }

    servingsInput.value = newServings;
    updateIngredientAmounts(newServings);
    state.currentServings = newServings;
}

/**
 * Updates ingredient amounts based on new servings
 * @param {number} newServings - New number of servings
 */
function updateIngredientAmounts(newServings) {
    const scaleFactor = newServings / state.originalServings;

    state.ingredients.forEach(ingredient => {
        const newAmount = ingredient.originalAmount * scaleFactor;
        const formattedAmount = formatFraction(newAmount);
        ingredient.element.textContent = `${formattedAmount}${ingredient.unit ? ' ' + ingredient.unit : ''}`;
    });
}

/**
 * Resets recipe to original scale
 */
function resetScale() {
    const servingsInput = document.querySelector('#servings');
    if (servingsInput) {
        servingsInput.value = state.originalServings;
    }
    
    updateIngredientAmounts(state.originalServings);
    state.currentServings = state.originalServings;
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', initializeScaling);
