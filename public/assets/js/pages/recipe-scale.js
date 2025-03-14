/**
 * @fileoverview Recipe scaling functionality for FlavorConnect
 * @author FlavorConnect Team
 * @version 1.0.0
 * @license MIT
 */

document.addEventListener('DOMContentLoaded', function() {
    // Scale buttons
    const scaleButtons = document.querySelectorAll('.scale-btn');
    scaleButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const newScale = parseFloat(this.dataset.scale);
            
            // Update active button state
            document.querySelectorAll('.scale-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            this.classList.add('active');
            
            // Update ingredient amounts
            const amounts = document.querySelectorAll('.amount');
            amounts.forEach(amount => {
                const baseAmount = parseFloat(amount.dataset.base);
                const newAmount = baseAmount * newScale;
                amount.textContent = formatQuantity(newAmount);
            });
        });
    });
});

/**
 * Formats a number as a fraction when appropriate
 * @param {number} value - Number to format
 * @returns {string} Formatted number
 */
function formatQuantity(value) {
    if (value === 0) return '0';
    
    const wholePart = Math.floor(value);
    const decimal = value - wholePart;
    
    // Convert decimal to fraction
    let fraction = '';
    if (decimal >= 0.875) {
        fraction = '';
        wholePart += 1;
    } else if (decimal >= 0.625) {
        fraction = '¾';
    } else if (decimal >= 0.375) {
        fraction = '½';
    } else if (decimal >= 0.125) {
        fraction = '¼';
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
