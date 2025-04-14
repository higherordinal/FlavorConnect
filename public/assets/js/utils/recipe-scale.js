/**
 * @fileoverview Recipe scaling functionality for FlavorConnect
 * @author Henry Vaughn
 * @license MIT
 * @description Handles the recipe scaling functionality that allows users to adjust
 * ingredient quantities based on serving size. This script provides interactive
 * scaling buttons that recalculate ingredient amounts in real-time.
 */

'use strict';

// Add to FlavorConnect namespace
window.FlavorConnect = window.FlavorConnect || {};
window.FlavorConnect.utils = window.FlavorConnect.utils || {};

// Recipe Scale utility
window.FlavorConnect.utils.recipeScale = (function() {
    'use strict';
    
    /**
     * Initialize recipe scaling functionality
     */
    function initialize() {
        // Scale buttons
        const scaleButtons = document.querySelectorAll('.scale-btn');
        if (!scaleButtons.length) return;
        
        scaleButtons.forEach(button => {
            button.addEventListener('click', handleScaleClick);
        });
    }
    
    /**
     * Handle click on scale button
     * @param {Event} e - Click event
     */
    function handleScaleClick(e) {
        const newScale = parseFloat(this.dataset.scale);
        
        // Update active button state
        document.querySelectorAll('.scale-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        this.classList.add('active');
        
        // Update ingredient amounts and measurements
        updateIngredients(newScale);
    }
    
    /**
     * Update all ingredients with new scale
     * @param {number} newScale - The scaling factor
     */
    function updateIngredients(newScale) {
        const ingredients = document.querySelectorAll('.ingredients-list li');
        
        ingredients.forEach(ingredient => {
            // Update amount
            const amount = ingredient.querySelector('.amount');
            if (amount && amount.dataset.base) {
                const baseAmount = parseFloat(amount.dataset.base);
                const newAmount = baseAmount * newScale;
                amount.textContent = formatQuantity(newAmount);
                
                // Update measurement text if it exists
                updateMeasurement(ingredient, newAmount);
            }
        });
    }
    
    /**
     * Update measurement text based on quantity
     * @param {HTMLElement} ingredient - The ingredient element
     * @param {number} newAmount - The new quantity amount
     */
    function updateMeasurement(ingredient, newAmount) {
        const measurement = ingredient.querySelector('.measurement');
        if (measurement) {
            const singular = measurement.dataset.singular;
            const plural = measurement.dataset.plural;
            
            if (singular && plural) {
                // Use singular for quantities of 1 or less, plural for greater
                measurement.textContent = (newAmount <= 1) ? singular : plural;
            }
        }
    }
    
    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', initialize);
    
    // Return public API
    return {
        init: initialize
    };
})();

/**
 * Formats a number as a fraction when appropriate
 * @param {number} value - Number to format
 * @param {string} precision - Precision level ('basic' or 'extended')
 * @returns {string} Formatted number
 */
function formatQuantity(value, precision = 'basic') {
    if (value === 0) return '0';
    
    let wholePart = Math.floor(value);
    const decimal = value - wholePart;
    
    // Convert decimal to fraction
    let fraction = '';
    
    if (precision === 'extended') {
        // Extended precision with more fraction options
        if (decimal >= 0.9375) {
            fraction = '';
            wholePart += 1;
        } else if (decimal >= 0.875) {
            fraction = '⅞';
        } else if (decimal >= 0.8125) {
            fraction = '⅚';
        } else if (decimal >= 0.75) {
            fraction = '¾';
        } else if (decimal >= 0.6875) {
            fraction = '⅔';
        } else if (decimal >= 0.625) {
            fraction = '⅝';
        } else if (decimal >= 0.5625) {
            fraction = '⅗';
        } else if (decimal >= 0.5) {
            fraction = '½';
        } else if (decimal >= 0.4375) {
            fraction = '⅖';
        } else if (decimal >= 0.375) {
            fraction = '⅜';
        } else if (decimal >= 0.3125) {
            fraction = '⅓';
        } else if (decimal >= 0.25) {
            fraction = '¼';
        } else if (decimal >= 0.1875) {
            fraction = '⅕';
        } else if (decimal >= 0.125) {
            fraction = '⅛';
        } else if (decimal >= 0.0625) {
            fraction = '⅟16';
        }
    } else {
        // Basic precision with expanded fractions
        if (decimal >= 0.9375) {
            fraction = '';
            wholePart += 1;
        } else if (decimal >= 0.8125) {
            fraction = '⅞'; // ⅞
        } else if (decimal >= 0.7) {
            fraction = '¾'; // ¾
        } else if (decimal >= 0.58) {
            fraction = '⅔'; // ⅔
        } else if (decimal >= 0.45) {
            fraction = '½'; // ½
        } else if (decimal >= 0.375) {
            fraction = '⅖'; // ⅖
        } else if (decimal >= 0.29) {
            fraction = '⅓'; // ⅓
        } else if (decimal >= 0.225) {
            fraction = '¼'; // ¼
        } else if (decimal >= 0.175) {
            fraction = '⅕'; // ⅕
        } else if (decimal >= 0.0625) {
            fraction = '⅛'; // ⅛
        }
    }
    
    // Format the final string
    if (wholePart === 0) {
        return fraction || '0';
    } else if (fraction) {
        return `${wholePart} ${fraction}`;
    } else {
        return wholePart.toString();
    }
}
