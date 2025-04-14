/**
 * @fileoverview Admin page functionality for FlavorConnect
 * @author Cascade AI
 * @license MIT
 * @description Handles the interactive functionality on the admin pages.
 * @version 1.1.0
 */

// Add to FlavorConnect namespace
window.FlavorConnect = window.FlavorConnect || {};
window.FlavorConnect.pages = window.FlavorConnect.pages || {};

// Admin page module
window.FlavorConnect.pages.admin = (function() {
    'use strict';
    
    /**
     * Initializes the cancel buttons on admin forms to use JavaScript history.back()
     * instead of inline onclick handlers
     * @private
     */
    function initializeCancelButtons() {
        const cancelButtons = document.querySelectorAll('a.cancel');
        
        cancelButtons.forEach(button => {
            if (button) {
                // Remove any existing onclick handlers
                button.removeAttribute('onclick');
                
                // Add event listener
                button.addEventListener('click', function(event) {
                    event.preventDefault();
                    history.back();
                });
            }
        });
    }
    
    /**
     * Initializes all admin page functionality
     * @public
     */
    function initialize() {
        initializeCancelButtons();
    }
    
    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', initialize);
    
    // Return public API
    return {
        init: initialize
    };
})();
