/**
 * @fileoverview Common utility functions for FlavorConnect application
 * @author FlavorConnect Team
 * @version 1.0.0
 * @license MIT
 */

/**
 * Debounces a function to limit the rate at which it is called
 * @param {Function} func - The function to debounce
 * @param {number} wait - The number of milliseconds to wait before calling the function
 * @returns {Function} A debounced version of the input function
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Formats time in seconds to a human-readable string
 * @param {number} seconds - Time in seconds
 * @returns {string} Formatted time string (e.g., "2 hr 30 min" or "45 min")
 */
function formatTime(seconds) {
    if (!seconds) return '0 min';
    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    
    if (hours > 0) {
        return `${hours} hr ${minutes > 0 ? minutes + ' min' : ''}`;
    }
    return `${minutes} min`;
}

/**
 * Formats a date string to a localized format
 * @param {string} dateString - ISO date string
 * @returns {string} Formatted date string
 */
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

/**
 * Makes an AJAX request with error handling
 * @param {string} url - The URL to fetch from
 * @param {Object} [options={}] - Fetch options
 * @returns {Promise<Object>} The parsed JSON response
 * @throws {Error} If the request fails
 */
async function fetchData(url, options = {}) {
    try {
        const response = await fetch(url, {
            headers: {
                'Content-Type': 'application/json'
            },
            ...options
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        return await response.json();
    } catch (error) {
        console.error('Error fetching data:', error);
        throw error;
    }
}

/**
 * Converts a decimal number to a fraction string
 * @param {number} value - The decimal number to convert
 * @returns {string} The fraction as a string (e.g., "1/2" or "2 1/4")
 */
function formatFraction(value) {
    if (!value) return '0';
    if (value % 1 === 0) return value.toString();
    
    const tolerance = 1.0E-6;
    let numerator = 1;
    let denominator = 1;
    let error = Math.abs(value - (numerator / denominator));
    
    while (error > tolerance && denominator < 16) {
        if ((numerator / denominator) < value) {
            numerator++;
        } else {
            denominator++;
            numerator = Math.round(value * denominator);
        }
        error = Math.abs(value - (numerator / denominator));
    }
    
    const whole = Math.floor(numerator / denominator);
    numerator = numerator % denominator;
    
    if (whole > 0) {
        return numerator ? `${whole} ${numerator}/${denominator}` : whole.toString();
    }
    return `${numerator}/${denominator}`;
}

/**
 * Toggles the visibility of an element
 * @param {HTMLElement|string} element - The element or selector to toggle
 * @param {boolean} [show=true] - Whether to show or hide the element
 */
function toggleVisibility(element, show = true) {
    if (typeof element === 'string') {
        element = document.querySelector(element);
    }
    if (element) {
        element.style.display = show ? '' : 'none';
    }
}

/**
 * Adds an event listener with error handling
 * @param {HTMLElement|string} element - The element or selector to attach the listener to
 * @param {string} event - The event name
 * @param {Function} handler - The event handler function
 */
function addSafeEventListener(element, event, handler) {
    if (typeof element === 'string') {
        element = document.querySelector(element);
    }
    if (element) {
        element.addEventListener(event, (...args) => {
            try {
                handler(...args);
            } catch (error) {
                console.error('Error in event handler:', error);
            }
        });
    }
}

// Export all utility functions
export {
    debounce,
    formatTime,
    formatDate,
    fetchData,
    formatFraction,
    toggleVisibility,
    addSafeEventListener
};
