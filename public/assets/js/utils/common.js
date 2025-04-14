/**
 * @fileoverview Common utility functions for FlavorConnect application
 * @author Henry Vaughn
 * @license MIT
 */

'use strict';

// Add to FlavorConnect namespace
window.FlavorConnect = window.FlavorConnect || {};
window.FlavorConnect.utils = window.FlavorConnect.utils || {};

// Common utilities
window.FlavorConnect.utils.common = (function() {
    'use strict';
    
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
     * Fetches data from an API endpoint
     * @param {string} url - The URL to fetch data from
     * @param {Object} options - Fetch options
     * @returns {Promise<Object>} The fetched data
     */
    async function fetchData(url, options = {}) {
        try {
            const response = await fetch(url, options);
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
     * Formats a decimal to a fraction string
     * @param {number} decimal - The decimal to format
     * @returns {string} The formatted fraction string
     */
    function formatFraction(decimal) {
        if (decimal === 0) return '0';
        if (decimal === 1) return '1';
        if (decimal === 0.25) return '¼';
        if (decimal === 0.5) return '½';
        if (decimal === 0.75) return '¾';
        if (decimal === 0.33 || decimal === 0.333) return '⅓';
        if (decimal === 0.66 || decimal === 0.667) return '⅔';
        return decimal.toString();
    }

    /**
     * Toggles the visibility of an element
     * @param {HTMLElement} element - The element to toggle
     * @param {boolean} show - Whether to show or hide the element
     */
    function toggleVisibility(element, show) {
        if (!element) return;
        element.style.display = show ? 'block' : 'none';
    }

    /**
     * Adds an event listener with error handling
     * @param {HTMLElement} element - The element to add the listener to
     * @param {string} event - The event type
     * @param {Function} handler - The event handler
     */
    function addSafeEventListener(element, event, handler) {
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
    
    // For backward compatibility
    window.debounce = debounce;
    window.formatTime = formatTime;
    window.formatDate = formatDate;
    window.fetchData = fetchData;
    window.formatFraction = formatFraction;
    window.toggleVisibility = toggleVisibility;
    window.addSafeEventListener = addSafeEventListener;
    
    // Return public API
    return {
        debounce,
        formatTime,
        formatDate,
        fetchData,
        formatFraction,
        toggleVisibility,
        addSafeEventListener
    };
})();
