/**
 * @fileoverview Form Validation functionality for FlavorConnect
 * @description Provides client-side validation for all forms in the application
 * This script includes validation for:
 * - Password strength and matching
 * - Email format
 * - Required fields
 * - URL format
 * - Numeric values
 * - File uploads
 * 
 * @author Henry Vaughn
 * @version 1.1.0
 */

// Add to FlavorConnect namespace
window.FlavorConnect = window.FlavorConnect || {};
window.FlavorConnect.utils = window.FlavorConnect.utils || {};

// Form validation utility
window.FlavorConnect.utils.formValidation = (function() {
    'use strict';
    
    // Configuration options
    const config = {
        passwordMinLength: 8,
        showValidationStyles: true,
        validationClasses: {
            error: 'error',
            valid: 'valid',
            invalid: 'invalid'
        }
    };
    
    /**
     * Initialize validation when DOM is loaded
     */
    function initialize() {
        initializeFormValidation();
    }
    
    // Initialize validation when DOM is loaded
    document.addEventListener('DOMContentLoaded', initialize);
    
    /**
     * Main initialization function for form validation
     */
    function initializeFormValidation() {
        // Add CSS styles for validation feedback
        addValidationStyles();
        
        // Initialize specific validation types
        initializePasswordValidation();
        initializeRequiredFieldValidation();
        initializeEmailValidation();
        initializeUrlValidation();
        initializeNumericValidation();
        initializeFileValidation();
        
        // Add form submission validation
        validateFormsOnSubmit();
    }
    
    /**
     * Adds CSS styles for validation feedback
     */
    function addValidationStyles() {
        const styleElement = document.createElement('style');
        styleElement.textContent = `
            .validation-requirements {
                margin-top: 10px;
                padding: 10px;
                border-radius: 4px;
                background-color: #f8f9fa;
                font-size: 0.85rem;
            }
            .validation-requirements ul {
                list-style: none;
                padding-left: 10px;
                margin: 5px 0;
            }
            .requirement-heading {
                margin: 0 0 5px 0;
                font-weight: bold;
            }
            .check-icon {
                display: inline-block;
                width: 16px;
                color: #ccc;
            }
            .valid .check-icon {
                color: #28a745;
            }
            .invalid .check-icon {
                color: #dc3545;
            }
            .validation-message {
                margin-top: 5px;
                font-size: 0.85rem;
            }
            .validation-valid {
                color: #28a745;
            }
            .validation-invalid {
                color: #dc3545;
            }
            .form-input.error {
                border-color: #dc3545;
            }
            .form-error {
                color: #dc3545;
                font-size: 0.85rem;
                margin-top: 5px;
            }
        `;
        document.head.appendChild(styleElement);
    }
    
    /**
     * Initializes password validation for password fields
     */
    function initializePasswordValidation() {
        // Find all password fields
        const passwordFields = document.querySelectorAll('input[type="password"][id="password"]');
        
        passwordFields.forEach(passwordField => {
            // Find confirm password field
            const form = passwordField.closest('form');
            if (!form) return;
            
            const confirmPasswordField = form.querySelector('input[type="password"][id="confirm_password"]');
            if (!confirmPasswordField) return;
            
            // Check if password requirements container already exists
            let requirementsContainer = form.querySelector('.validation-requirements');
            let matchMessage = form.querySelector('#password-match');
            
            // Create password requirements container if it doesn't exist
            if (!requirementsContainer) {
                requirementsContainer = document.createElement('div');
                requirementsContainer.className = 'validation-requirements';
                requirementsContainer.style.display = 'none'; // Initially hidden
                requirementsContainer.innerHTML = `
                    <p class="requirement-heading">Password must contain:</p>
                    <ul>
                        <li id="length-check"><span class="check-icon">✓</span> At least 8 characters</li>
                        <li id="uppercase-check"><span class="check-icon">✓</span> At least one uppercase letter</li>
                        <li id="lowercase-check"><span class="check-icon">✓</span> At least one lowercase letter</li>
                        <li id="number-check"><span class="check-icon">✓</span> At least one number</li>
                    </ul>
                `;
                
                // Insert element after the field
                const passwordErrorContainer = passwordField.nextElementSibling;
                if (passwordErrorContainer && passwordErrorContainer.classList.contains('form-error')) {
                    passwordErrorContainer.parentNode.insertBefore(requirementsContainer, passwordErrorContainer.nextSibling);
                } else {
                    passwordField.parentNode.insertBefore(requirementsContainer, passwordField.nextSibling);
                }
            }
            
            // Create password match message if it doesn't exist
            if (!matchMessage && confirmPasswordField) {
                matchMessage = document.createElement('div');
                matchMessage.id = 'password-match';
                matchMessage.className = 'validation-message';
                matchMessage.textContent = 'Passwords match';
                matchMessage.style.display = 'none';
                
                // Insert element after the field
                const confirmErrorContainer = confirmPasswordField.nextElementSibling;
                if (confirmErrorContainer && confirmErrorContainer.classList.contains('form-error')) {
                    confirmErrorContainer.parentNode.insertBefore(matchMessage, confirmErrorContainer.nextSibling);
                } else {
                    confirmPasswordField.parentNode.insertBefore(matchMessage, confirmPasswordField.nextSibling);
                }
            }
            
            // Show password requirements on focus
            passwordField.addEventListener('focus', function() {
                requirementsContainer.style.display = 'block';
            });
            
            // Add event listeners for validation
            passwordField.addEventListener('input', function() {
                validatePassword(passwordField, confirmPasswordField);
            });
            
            confirmPasswordField.addEventListener('input', function() {
                checkPasswordMatch(passwordField, confirmPasswordField);
            });
            
            // Show password match message on focus if there's already content
            confirmPasswordField.addEventListener('focus', function() {
                if (confirmPasswordField.value) {
                    const matchMessage = form.querySelector('#password-match');
                    if (matchMessage) {
                        matchMessage.style.display = 'block';
                    }
                }
            });
            
            // Initial validation (but don't show requirements yet)
            if (passwordField.value) {
                validatePassword(passwordField, confirmPasswordField);
            }
        });
    }
    
    /**
     * Validates password strength
     * @param {HTMLInputElement} passwordField - The password input field
     * @param {HTMLInputElement} confirmPasswordField - The confirm password input field
     */
    function validatePassword(passwordField, confirmPasswordField) {
        const password = passwordField.value;
        const form = passwordField.closest('form');
        
        const lengthCheck = form.querySelector('#length-check');
        const uppercaseCheck = form.querySelector('#uppercase-check');
        const lowercaseCheck = form.querySelector('#lowercase-check');
        const numberCheck = form.querySelector('#number-check');
        
        // Check length
        if (password.length >= 8) {
            lengthCheck.classList.add('valid');
            lengthCheck.classList.remove('invalid');
        } else {
            lengthCheck.classList.add('invalid');
            lengthCheck.classList.remove('valid');
        }
        
        // Check uppercase
        if (/[A-Z]/.test(password)) {
            uppercaseCheck.classList.add('valid');
            uppercaseCheck.classList.remove('invalid');
        } else {
            uppercaseCheck.classList.add('invalid');
            uppercaseCheck.classList.remove('valid');
        }
        
        // Check lowercase
        if (/[a-z]/.test(password)) {
            lowercaseCheck.classList.add('valid');
            lowercaseCheck.classList.remove('invalid');
        } else {
            lowercaseCheck.classList.add('invalid');
            lowercaseCheck.classList.remove('valid');
        }
        
        // Check number
        if (/[0-9]/.test(password)) {
            numberCheck.classList.add('valid');
            numberCheck.classList.remove('invalid');
        } else {
            numberCheck.classList.add('invalid');
            numberCheck.classList.remove('valid');
        }
        
        // Check if passwords match
        checkPasswordMatch(passwordField, confirmPasswordField);
    }
    
    /**
     * Checks if passwords match
     * @param {HTMLInputElement} passwordField - The password input field
     * @param {HTMLInputElement} confirmPasswordField - The confirm password input field
     */
    function checkPasswordMatch(passwordField, confirmPasswordField) {
        const password = passwordField.value;
        const confirmPassword = confirmPasswordField.value;
        const form = confirmPasswordField.closest('form');
        
        const matchMessage = form.querySelector('#password-match');
        if (!matchMessage) return;
        
        // Only show match message if user has started typing in confirm field
        if (confirmPassword.length > 0) {
            matchMessage.style.display = 'block';
            
            if (password === confirmPassword) {
                matchMessage.textContent = 'Passwords match';
                matchMessage.classList.add('validation-valid');
                matchMessage.classList.remove('validation-invalid');
                confirmPasswordField.classList.remove('error');
            } else {
                matchMessage.textContent = 'Passwords do not match';
                matchMessage.classList.add('validation-invalid');
                matchMessage.classList.remove('validation-valid');
                confirmPasswordField.classList.add('error');
            }
        } else {
            // Hide the message if confirm field is empty
            matchMessage.style.display = 'none';
        }
    }
    
    /**
     * Initializes validation for required fields
     */
    function initializeRequiredFieldValidation() {
        const requiredFields = document.querySelectorAll('input[required], select[required], textarea[required]');
        
        requiredFields.forEach(field => {
            // Add blur event listener to validate when user leaves the field
            field.addEventListener('blur', function() {
                validateRequiredField(field);
            });
            
            // Add input event listener to clear error when user starts typing
            field.addEventListener('input', function() {
                if (field.value.trim() !== '') {
                    field.classList.remove('error');
                    const errorContainer = getErrorContainer(field);
                    if (errorContainer) {
                        errorContainer.style.display = 'none';
                    }
                }
            });
        });
    }
    
    /**
     * Validates a required field
     * @param {HTMLElement} field - The field to validate
     * @returns {boolean} - Whether the field is valid
     */
    function validateRequiredField(field) {
        const isValid = field.value.trim() !== '';
        
        if (!isValid) {
            field.classList.add('error');
            const errorContainer = getErrorContainer(field);
            if (errorContainer) {
                errorContainer.textContent = field.getAttribute('data-error-message') || 'This field is required';
                errorContainer.style.display = 'block';
            }
        } else {
            field.classList.remove('error');
            const errorContainer = getErrorContainer(field);
            if (errorContainer) {
                errorContainer.style.display = 'none';
            }
        }
        
        return isValid;
    }
    
    /**
     * Initializes email validation for email fields
     */
    function initializeEmailValidation() {
        const emailFields = document.querySelectorAll('input[type="email"]');
        
        emailFields.forEach(field => {
            field.addEventListener('blur', function() {
                validateEmail(field);
            });
            
            field.addEventListener('input', function() {
                if (isValidEmail(field.value) || field.value.trim() === '') {
                    field.classList.remove('error');
                    const errorContainer = getErrorContainer(field);
                    if (errorContainer) {
                        errorContainer.style.display = 'none';
                    }
                }
            });
        });
    }
    
    /**
     * Validates an email field
     * @param {HTMLInputElement} field - The email field to validate
     * @returns {boolean} - Whether the email is valid
     */
    function validateEmail(field) {
        const email = field.value.trim();
        const isValid = email === '' || isValidEmail(email);
        
        if (!isValid) {
            field.classList.add('error');
            const errorContainer = getErrorContainer(field);
            if (errorContainer) {
                errorContainer.textContent = 'Please enter a valid email address';
                errorContainer.style.display = 'block';
            }
        } else {
            field.classList.remove('error');
            const errorContainer = getErrorContainer(field);
            if (errorContainer) {
                errorContainer.style.display = 'none';
            }
        }
        
        return isValid;
    }
    
    /**
     * Checks if an email is valid
     * @param {string} email - The email to validate
     * @returns {boolean} - Whether the email is valid
     */
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    /**
     * Initializes URL validation for URL fields
     */
    function initializeUrlValidation() {
        const urlFields = document.querySelectorAll('input[type="url"], input[data-type="url"]');
        
        urlFields.forEach(field => {
            field.addEventListener('blur', function() {
                validateUrl(field);
            });
            
            field.addEventListener('input', function() {
                if (isValidUrl(field.value) || field.value.trim() === '') {
                    field.classList.remove('error');
                    const errorContainer = getErrorContainer(field);
                    if (errorContainer) {
                        errorContainer.style.display = 'none';
                    }
                }
            });
        });
    }
    
    /**
     * Validates a URL field
     * @param {HTMLInputElement} field - The URL field to validate
     * @returns {boolean} - Whether the URL is valid
     */
    function validateUrl(field) {
        const url = field.value.trim();
        const isValid = url === '' || isValidUrl(url);
        
        if (!isValid) {
            field.classList.add('error');
            const errorContainer = getErrorContainer(field);
            if (errorContainer) {
                errorContainer.textContent = 'Please enter a valid URL';
                errorContainer.style.display = 'block';
            }
        } else {
            field.classList.remove('error');
            const errorContainer = getErrorContainer(field);
            if (errorContainer) {
                errorContainer.style.display = 'none';
            }
        }
        
        return isValid;
    }
    
    /**
     * Checks if a URL is valid
     * @param {string} url - The URL to validate
     * @returns {boolean} - Whether the URL is valid
     */
    function isValidUrl(url) {
        try {
            new URL(url);
            return true;
        } catch (e) {
            return false;
        }
    }
    
    /**
     * Initializes numeric validation for numeric fields
     */
    function initializeNumericValidation() {
        const numericFields = document.querySelectorAll('input[type="number"], input[data-type="numeric"]');
        
        numericFields.forEach(field => {
            field.addEventListener('blur', function() {
                validateNumeric(field);
            });
            
            field.addEventListener('input', function() {
                if (isValidNumeric(field.value) || field.value.trim() === '') {
                    field.classList.remove('error');
                    const errorContainer = getErrorContainer(field);
                    if (errorContainer) {
                        errorContainer.style.display = 'none';
                    }
                }
            });
        });
    }
    
    /**
     * Validates a numeric field
     * @param {HTMLInputElement} field - The numeric field to validate
     * @returns {boolean} - Whether the value is valid
     */
    function validateNumeric(field) {
        const value = field.value.trim();
        const isValid = value === '' || isValidNumeric(value);
        
        if (!isValid) {
            field.classList.add('error');
            const errorContainer = getErrorContainer(field);
            if (errorContainer) {
                errorContainer.textContent = 'Please enter a valid number';
                errorContainer.style.display = 'block';
            }
        } else {
            field.classList.remove('error');
            const errorContainer = getErrorContainer(field);
            if (errorContainer) {
                errorContainer.style.display = 'none';
            }
        }
        
        return isValid;
    }
    
    /**
     * Checks if a value is a valid number
     * @param {string} value - The value to validate
     * @returns {boolean} - Whether the value is a valid number
     */
    function isValidNumeric(value) {
        return !isNaN(parseFloat(value)) && isFinite(value);
    }
    
    /**
     * Initializes file validation for file upload fields
     */
    function initializeFileValidation() {
        const fileFields = document.querySelectorAll('input[type="file"]');
        
        fileFields.forEach(field => {
            field.addEventListener('change', function() {
                validateFile(field);
            });
        });
    }
    
    /**
     * Validates a file upload field
     * @param {HTMLInputElement} field - The file field to validate
     * @returns {boolean} - Whether the file is valid
     */
    function validateFile(field) {
        const file = field.files[0];
        let isValid = true;
        
        // Check if file is required but not provided
        if (field.hasAttribute('required') && (!file || file.size === 0)) {
            isValid = false;
            field.classList.add('error');
            const errorContainer = getErrorContainer(field);
            if (errorContainer) {
                errorContainer.textContent = 'Please select a file';
                errorContainer.style.display = 'block';
            }
            return isValid;
        }
        
        // If no file selected, consider it valid
        if (!file) {
            field.classList.remove('error');
            const errorContainer = getErrorContainer(field);
            if (errorContainer) {
                errorContainer.style.display = 'none';
            }
            return true;
        }
        
        // Check file size
        const maxSize = parseInt(field.getAttribute('data-max-size') || '5242880', 10); // Default 5MB
        if (file.size > maxSize) {
            isValid = false;
            field.classList.add('error');
            const errorContainer = getErrorContainer(field);
            if (errorContainer) {
                const maxSizeMB = Math.round(maxSize / 1048576 * 10) / 10;
                errorContainer.textContent = `File size exceeds maximum allowed size of ${maxSizeMB}MB`;
                errorContainer.style.display = 'block';
            }
        }
        
        // Check file type if specified
        const allowedTypes = field.getAttribute('data-allowed-types');
        if (allowedTypes && file) {
            const types = allowedTypes.split(',').map(type => type.trim());
            const fileType = file.type;
            
            if (!types.includes(fileType)) {
                isValid = false;
                field.classList.add('error');
                const errorContainer = getErrorContainer(field);
                if (errorContainer) {
                    errorContainer.textContent = `File type not allowed. Allowed types: ${allowedTypes}`;
                    errorContainer.style.display = 'block';
                }
            }
        }
        
        if (isValid) {
            field.classList.remove('error');
            const errorContainer = getErrorContainer(field);
            if (errorContainer) {
                errorContainer.style.display = 'none';
            }
        }
        
        return isValid;
    }
    
    /**
     * Gets or creates an error container for a field
     * @param {HTMLElement} field - The field to get the error container for
     * @returns {HTMLElement} - The error container
     */
    function getErrorContainer(field) {
        let errorContainer = field.nextElementSibling;
        
        // Check if next element is already an error container
        if (errorContainer && errorContainer.classList.contains('form-error')) {
            return errorContainer;
        }
        
        // Create a new error container
        errorContainer = document.createElement('div');
        errorContainer.className = 'form-error';
        errorContainer.style.display = 'none';
        
        // Insert after the field
        field.parentNode.insertBefore(errorContainer, field.nextSibling);
        
        return errorContainer;
    }
    
    /**
     * Validates all forms on submit
     */
    function validateFormsOnSubmit() {
        const forms = document.querySelectorAll('form');
        
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                let isValid = true;
                
                // Validate required fields
                const requiredFields = form.querySelectorAll('[required]');
                requiredFields.forEach(field => {
                    if (!validateRequiredField(field)) {
                        isValid = false;
                    }
                });
                
                // Validate email fields
                const emailFields = form.querySelectorAll('input[type="email"]');
                emailFields.forEach(field => {
                    if (!validateEmail(field)) {
                        isValid = false;
                    }
                });
                
                // Validate URL fields
                const urlFields = form.querySelectorAll('input[type="url"], input[data-type="url"]');
                urlFields.forEach(field => {
                    if (!validateUrl(field)) {
                        isValid = false;
                    }
                });
                
                // Validate numeric fields
                const numericFields = form.querySelectorAll('input[type="number"], input[data-type="numeric"]');
                numericFields.forEach(field => {
                    if (!validateNumeric(field)) {
                        isValid = false;
                    }
                });
                
                // Validate file fields
                const fileFields = form.querySelectorAll('input[type="file"]');
                fileFields.forEach(field => {
                    if (!validateFile(field)) {
                        isValid = false;
                    }
                });
                
                // Validate password fields
                const passwordFields = form.querySelectorAll('input[type="password"][id="password"]');
                passwordFields.forEach(passwordField => {
                    const confirmPasswordField = form.querySelector('input[type="password"][id="confirm_password"]');
                    if (confirmPasswordField) {
                        // Check password requirements
                        const lengthCheck = form.querySelector('#length-check');
                        const uppercaseCheck = form.querySelector('#uppercase-check');
                        const lowercaseCheck = form.querySelector('#lowercase-check');
                        const numberCheck = form.querySelector('#number-check');
                        
                        if (passwordField.value !== '' && 
                            (!lengthCheck.classList.contains('valid') || 
                             !uppercaseCheck.classList.contains('valid') || 
                             !lowercaseCheck.classList.contains('valid') || 
                             !numberCheck.classList.contains('valid'))) {
                            isValid = false;
                            passwordField.classList.add('error');
                        }
                        
                        // Check if passwords match
                        if (passwordField.value !== confirmPasswordField.value) {
                            isValid = false;
                            confirmPasswordField.classList.add('error');
                            const errorContainer = getErrorContainer(confirmPasswordField);
                            if (errorContainer) {
                                errorContainer.textContent = 'Passwords do not match';
                                errorContainer.style.display = 'block';
                            }
                        }
                    }
                });
                
                if (!isValid) {
                    e.preventDefault();
                    
                    // Scroll to first error
                    const firstError = form.querySelector('.error');
                    if (firstError) {
                        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        firstError.focus();
                    }
                }
            });
        });
    }
    // Return public API
    return {
        init: initialize,
        validateForm: function(form) {
            // Validate all fields in the form
            let isValid = true;
            
            // Validate required fields
            const requiredFields = form.querySelectorAll('[required]');
            requiredFields.forEach(field => {
                if (!validateRequiredField(field)) {
                    isValid = false;
                }
            });
            
            // Validate email fields
            const emailFields = form.querySelectorAll('input[type="email"]');
            emailFields.forEach(field => {
                if (!validateEmail(field)) {
                    isValid = false;
                }
            });
            
            // Validate URL fields
            const urlFields = form.querySelectorAll('input[type="url"], input[data-type="url"]');
            urlFields.forEach(field => {
                if (!validateUrl(field)) {
                    isValid = false;
                }
            });
            
            // Validate numeric fields
            const numericFields = form.querySelectorAll('input[type="number"], input[data-type="numeric"]');
            numericFields.forEach(field => {
                if (!validateNumeric(field)) {
                    isValid = false;
                }
            });
            
            // Validate file fields
            const fileFields = form.querySelectorAll('input[type="file"]');
            fileFields.forEach(field => {
                if (!validateFile(field)) {
                    isValid = false;
                }
            });
            
            // Validate password fields
            const passwordFields = form.querySelectorAll('input[type="password"][id="password"]');
            passwordFields.forEach(passwordField => {
                const confirmPasswordField = form.querySelector('input[type="password"][id="confirm_password"]');
                if (confirmPasswordField) {
                    if (!validatePassword(passwordField, confirmPasswordField)) {
                        isValid = false;
                    }
                }
            });
            
            return isValid;
        },
        validateEmail: validateEmail,
        validateUrl: validateUrl,
        validateNumeric: validateNumeric,
        validateFile: validateFile,
        validatePassword: validatePassword,
        validateRequiredField: validateRequiredField
    };
})();
