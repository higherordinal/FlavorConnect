/**
 * @fileoverview Authentication page functionality for FlavorConnect
 * @author Henry Vaughn
 * @license MIT
 * @description Handles the interactive functionality on the authentication pages.
 * This script manages user interactions including:
 * - Password validation
 * - Password matching
 * - Form validation
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize password validation if on register page
    initPasswordValidation();
});

/**
 * Initializes password validation functionality
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
     * Validates password against requirements
     */
    function validatePassword() {
        if (!passwordInput) return;
        
        const password = passwordInput.value;
        
        // Check length
        if (lengthCheck) {
            if (password.length >= 8) {
                lengthCheck.classList.add('valid');
                lengthCheck.classList.remove('invalid');
            } else {
                lengthCheck.classList.add('invalid');
                lengthCheck.classList.remove('valid');
            }
        }
        
        // Check uppercase
        if (uppercaseCheck) {
            if (/[A-Z]/.test(password)) {
                uppercaseCheck.classList.add('valid');
                uppercaseCheck.classList.remove('invalid');
            } else {
                uppercaseCheck.classList.add('invalid');
                uppercaseCheck.classList.remove('valid');
            }
        }
        
        // Check lowercase
        if (lowercaseCheck) {
            if (/[a-z]/.test(password)) {
                lowercaseCheck.classList.add('valid');
                lowercaseCheck.classList.remove('invalid');
            } else {
                lowercaseCheck.classList.add('invalid');
                lowercaseCheck.classList.remove('valid');
            }
        }
        
        // Check number
        if (numberCheck) {
            if (/[0-9]/.test(password)) {
                numberCheck.classList.add('valid');
                numberCheck.classList.remove('invalid');
            } else {
                numberCheck.classList.add('invalid');
                numberCheck.classList.remove('valid');
            }
        }
        
        // Check if passwords match
        checkPasswordMatch();
    }
    
    /**
     * Checks if passwords match
     */
    function checkPasswordMatch() {
        if (!passwordInput || !confirmPasswordInput || !passwordMatch) return;
        
        const password = passwordInput.value;
        const confirmPassword = confirmPasswordInput.value;
        
        if (confirmPassword.length > 0) {
            passwordMatch.style.display = 'block';
            
            if (password === confirmPassword) {
                passwordMatch.textContent = 'Passwords match';
                passwordMatch.classList.add('match-valid');
                passwordMatch.classList.remove('match-invalid');
            } else {
                passwordMatch.textContent = 'Passwords do not match';
                passwordMatch.classList.add('match-invalid');
                passwordMatch.classList.remove('match-valid');
            }
        } else {
            passwordMatch.style.display = 'none';
        }
    }
}
