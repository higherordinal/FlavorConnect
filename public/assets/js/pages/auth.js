/**
 * @fileoverview Authentication page functionality for FlavorConnect
 * @author Henry Vaughn
 * @license MIT
 * @description Handles the interactive functionality on the authentication pages.
 * This script manages user interactions including:
 * - Password validation
 * - Password matching
 * - Form validation
 * @version 1.1.0
 */

// Add to FlavorConnect namespace
window.FlavorConnect = window.FlavorConnect || {};
window.FlavorConnect.pages = window.FlavorConnect.pages || {};

// Auth page module
window.FlavorConnect.pages.auth = (function() {
    'use strict';
    
    // Configuration
    const config = {
        minPasswordLength: 8,
        validClass: 'valid',
        invalidClass: 'invalid',
        matchValidClass: 'match-valid',
        matchInvalidClass: 'match-invalid'
    };
    
    /**
     * Initializes password validation functionality
     * @private
     */
    function initPasswordValidation() {
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        
        // If not on register page, exit early
        if (!passwordInput || !confirmPasswordInput) return;
        
        const passwordRequirements = document.getElementById('password-requirements');
        const lengthCheck = document.getElementById('length-check');
        const uppercaseCheck = document.getElementById('uppercase-check');
        const lowercaseCheck = document.getElementById('lowercase-check');
        const numberCheck = document.getElementById('number-check');
        const passwordMatch = document.getElementById('password-match');
        
        // Initial state - hide password match indicator
        if (passwordMatch) {
            passwordMatch.style.display = 'none';
        }
        
        // Add event listeners
        if (passwordInput) {
            passwordInput.addEventListener('input', validatePassword);
        }
        
        if (confirmPasswordInput) {
            confirmPasswordInput.addEventListener('input', checkPasswordMatch);
        }
        
        // Initial validation
        validatePassword();
        
        /**
         * Updates element classes based on validation state
         * @param {HTMLElement} element - The element to update
         * @param {boolean} isValid - Whether the validation passed
         * @private
         */
        function updateValidationClass(element, isValid) {
            if (!element) return;
            
            if (isValid) {
                element.classList.add(config.validClass);
                element.classList.remove(config.invalidClass);
            } else {
                element.classList.add(config.invalidClass);
                element.classList.remove(config.validClass);
            }
        }
        
        /**
         * Validates password against requirements
         * @private
         */
        function validatePassword() {
            if (!passwordInput) return;
            
            const password = passwordInput.value;
            
            // Check length
            updateValidationClass(lengthCheck, password.length >= config.minPasswordLength);
            
            // Check uppercase
            updateValidationClass(uppercaseCheck, /[A-Z]/.test(password));
            
            // Check lowercase
            updateValidationClass(lowercaseCheck, /[a-z]/.test(password));
            
            // Check number
            updateValidationClass(numberCheck, /[0-9]/.test(password));
            
            // Check if passwords match
            checkPasswordMatch();
        }
        
        /**
         * Checks if passwords match
         * @private
         */
        function checkPasswordMatch() {
            if (!passwordInput || !confirmPasswordInput || !passwordMatch) return;
            
            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            
            if (confirmPassword.length > 0) {
                passwordMatch.style.display = 'block';
                
                const passwordsMatch = password === confirmPassword;
                passwordMatch.textContent = passwordsMatch ? 'Passwords match' : 'Passwords do not match';
                
                if (passwordsMatch) {
                    passwordMatch.classList.add(config.matchValidClass);
                    passwordMatch.classList.remove(config.matchInvalidClass);
                } else {
                    passwordMatch.classList.add(config.matchInvalidClass);
                    passwordMatch.classList.remove(config.matchValidClass);
                }
            } else {
                passwordMatch.style.display = 'none';
            }
        }
    }
    
    /**
     * Initialize all authentication page functionality
     * @public
     */
    function initialize() {
        initPasswordValidation();
    }
    
    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', initialize);
    
    // Return public API
    return {
        init: initialize
    };
})();
