/**
 * @fileoverview Admin page functionality for FlavorConnect
 * @author Cascade AI
 * @license MIT
 * @description Handles the interactive functionality on the admin pages.
 */

'use strict';

/**
 * Initializes the cancel buttons on admin forms to use JavaScript history.back()
 * instead of inline onclick handlers
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

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    initializeCancelButtons();
});
