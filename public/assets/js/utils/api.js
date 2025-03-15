/**
 * FlavorConnect API Utility
 * Provides functions for interacting with the FlavorConnect API
 */

// Initialize FlavorConnect namespace
window.FlavorConnect = window.FlavorConnect || {};

// API utility functions
window.FlavorConnect.api = {
    /**
     * Make a GET request to the API
     * @param {string} endpoint - The API endpoint to call
     * @param {Object} params - Query parameters
     * @returns {Promise<Object>} - The API response
     */
    get: async function(endpoint, params = {}) {
        try {
            // Ensure we have the correct API URL format
            const apiUrl = window.FlavorConnect.config.apiBaseUrl;
            // Remove trailing slash if present
            const baseUrl = apiUrl.endsWith('/') ? apiUrl.slice(0, -1) : apiUrl;
            
            // Add leading slash to endpoint if not present
            const formattedEndpoint = endpoint.startsWith('/') ? endpoint : '/' + endpoint;
            
            // Build URL with query parameters
            let url = `${baseUrl}${formattedEndpoint}`;
            
            // Add query parameters if any
            if (Object.keys(params).length > 0) {
                const queryString = new URLSearchParams(params).toString();
                url += `?${queryString}`;
            }
            
            console.log('API GET:', url);
            
            const response = await fetch(url);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            return await response.json();
        } catch (error) {
            console.error('API GET error:', error);
            throw error;
        }
    },
    
    /**
     * Make a POST request to the API
     * @param {string} endpoint - The API endpoint to call
     * @param {Object} data - The data to send
     * @returns {Promise<Object>} - The API response
     */
    post: async function(endpoint, data = {}) {
        try {
            // Ensure we have the correct API URL format
            const apiUrl = window.FlavorConnect.config.apiBaseUrl;
            // Remove trailing slash if present
            const baseUrl = apiUrl.endsWith('/') ? apiUrl.slice(0, -1) : apiUrl;
            
            // Add leading slash to endpoint if not present
            const formattedEndpoint = endpoint.startsWith('/') ? endpoint : '/' + endpoint;
            
            const url = `${baseUrl}${formattedEndpoint}`;
            console.log('API POST:', url);
            
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            return await response.json();
        } catch (error) {
            console.error('API POST error:', error);
            throw error;
        }
    }
};
