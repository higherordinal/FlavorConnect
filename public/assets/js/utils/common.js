/**
 * Common utility functions for FlavorConnect
 */

// Debounce function to limit rate of function calls
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

// Format time from seconds to human readable string
function formatTime(seconds) {
    if (!seconds) return '0 min';
    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    
    if (hours > 0) {
        return `${hours} hr ${minutes > 0 ? minutes + ' min' : ''}`;
    }
    return `${minutes} min`;
}

// Format date to locale string
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

// Handle AJAX requests
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

// Format number to fraction
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

// Toggle element visibility
function toggleVisibility(element, show = true) {
    if (typeof element === 'string') {
        element = document.querySelector(element);
    }
    if (element) {
        element.style.display = show ? '' : 'none';
    }
}

// Add event listener with error handling
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
