/**
 * @fileoverview Recipe Form functionality for FlavorConnect
 * @author Henry Vaughn
 * @license MIT
 * @description Handles the recipe creation and editing form functionality.
 * This script provides interactive features for the recipe form including:
 * - Image preview and alt text generation
 * - Dynamic ingredient rows with add/remove functionality
 * - Dynamic direction steps with add/remove functionality
 * - Form validation
 */

'use strict';

// Add to FlavorConnect namespace
window.FlavorConnect = window.FlavorConnect || {};
window.FlavorConnect.components = window.FlavorConnect.components || {};

// Global helper function for removing buttons visibility
function updateRemoveButtons(buttonSelector = '.remove-ingredient') {
    const removeButtons = document.querySelectorAll(buttonSelector);
    const showButtons = removeButtons.length > 1;
    removeButtons.forEach(button => button.style.display = showButtons ? 'flex' : 'none');
}

// Recipe Form component
window.FlavorConnect.components.recipeForm = (function() {
    'use strict';
    
    // Function to initialize all form components
    function initialize() {
        // Ensure we only initialize once
        if (window.recipeFormInitialized) return;
        window.recipeFormInitialized = true;

        // Image Preview Functionality
        initializeImagePreview();
        
        // Alt Text Generation
        initializeAltTextGeneration();
        
        // Ingredients Functionality
        initializeIngredients();
        
        // Directions Functionality
        initializeDirections();
        
        // Form Validation
        initializeFormValidation();
        
        // Recipe Header Background
        initializeRecipeHeaderBackground();
    }
    
    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', initialize);

    // updateRemoveButtons function is now defined globally

    /**
     * Initializes image preview functionality for the recipe image upload
     * Creates a preview of the selected image below the file input
     * Validates file size and displays error messages
     */
    /**
     * Helper function to clear all child elements from a container
     * @param {HTMLElement} container The container to clear
     */
    function clearContainer(container) {
        while (container.firstChild) {
            container.removeChild(container.firstChild);
        }
    }

    function initializeImagePreview() {
        const imageInput = document.getElementById('recipe_image');
        const previewContainer = document.createElement('div');
        previewContainer.className = 'mt-2';
        
        // Create error message container
        const errorContainer = document.createElement('div');
        errorContainer.className = 'form-error';
        errorContainer.style.display = 'none';
        
        if (imageInput) {
            // Add error container after the file input
            imageInput.parentNode.insertBefore(errorContainer, imageInput.nextSibling);
            
            imageInput.addEventListener('change', function() {
                const file = this.files[0];
                
                // Clear any existing error
                errorContainer.textContent = '';
                errorContainer.style.display = 'none';
                imageInput.classList.remove('error');
                
                if (file) {
                    // Check file size (max 10MB)
                    const maxSize = 10 * 1024 * 1024; // 10MB in bytes
                    if (file.size > maxSize) {
                        // Show error message
                        errorContainer.textContent = 'File is too large. Maximum size is 10MB';
                        errorContainer.style.display = 'block';
                        imageInput.classList.add('error');
                        
                        // Clear the file input
                        this.value = '';
                        
                        // Remove any existing preview
                        clearContainer(previewContainer);
                        
                        return;
                    }
                    
                    // Remove any existing preview
                    clearContainer(previewContainer);

                    // Create preview image
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const img = document.createElement('img');
                            img.src = e.target.result;
                            img.alt = 'Recipe image preview';
                            img.className = 'img-thumbnail';
                            img.style.maxWidth = '300px';
                            img.style.height = 'auto';
                            previewContainer.appendChild(img);
                        };
                        reader.readAsDataURL(file);
                    }
                }
            });

            // Insert preview container after the file input and error container
            imageInput.parentNode.insertBefore(previewContainer, errorContainer.nextSibling);
        }
    }

    /**
     * Initializes automatic alt text generation based on recipe title
     * Updates the alt text field when the title changes
     */
    function initializeAltTextGeneration() {
    const titleInput = document.getElementById('title');
    const altTextInput = document.getElementById('alt_text');
    
    if (titleInput && altTextInput) {
        // Set initial alt text if title has value but alt text is empty
        if (titleInput.value && !altTextInput.value) {
            altTextInput.value = titleInput.value;
        }
        
        // Update alt text whenever title changes
        titleInput.addEventListener('input', function() {
            const titleValue = this.value.trim();
            
            // Always update the alt text to match the title exactly
            if (titleValue) {
                altTextInput.value = titleValue;
            } else {
                altTextInput.value = ''; // Clear alt text if title is empty
            }
            
            // Title value has been applied to alt text
        });
    }
}

    /**
     * Pluralizes a measurement name based on quantity
     * @param {string} measurementName The measurement name to pluralize
     * @param {number} quantity The quantity of the measurement
     * @returns {string} The properly pluralized measurement name
     */
    function pluralizeMeasurement(measurementName, quantity) {
    if (!measurementName || measurementName === '(none)') return '';
    
    // If quantity is 1 or less, use singular form
    if (quantity <= 1) {
        return measurementName;
    }
    
    // Handle specific measurement cases
    switch (measurementName.toLowerCase().trim()) {
        // Measurements that have special plural forms
        case 'dash':
            return 'dashes';
            
        case 'pinch':
            return 'pinches';
            
        // Default: just add 's' for all other measurements
        default:
            return measurementName + 's';
    }
}

    /**
     * Initializes dynamic ingredient rows functionality
     * Allows users to add and remove ingredient rows with quantity, measurement, and name
     */
    function initializeIngredients() {
        const ingredientsContainer = document.getElementById('ingredients-container');
        const addIngredientBtn = document.getElementById('add-ingredient');
        let ingredientCount = ingredientsContainer ? 
            ingredientsContainer.querySelectorAll('.ingredient-row').length : 0;
            
        // Store original measurement options for reference
        let originalMeasurementOptions = [];
        const firstMeasurementSelect = document.querySelector('#measurement_0');
        if (firstMeasurementSelect) {
            Array.from(firstMeasurementSelect.options).forEach(option => {
                originalMeasurementOptions.push({
                    value: option.value,
                    text: option.textContent
                });
            });
            // Original measurement options are now stored for reference
        }

        // Initialize fraction helpers for existing ingredient rows
        initializeFractionHelpers();

    /**
     * Initializes the fraction helper buttons for all ingredient rows
     */
    function initializeFractionHelpers() {
        document.querySelectorAll('.fraction-helper').forEach(helper => {
            helper.addEventListener('click', function() {
                const value = parseFloat(this.dataset.value);
                const inputField = this.closest('.quantity-input-group').querySelector('input[type="number"]');
                
                // Get current value and handle whole numbers
                let currentValue = parseFloat(inputField.value) || 0;
                let wholePart = Math.floor(currentValue);
                
                // If the input is empty or 0, just set it to the fraction value
                if (currentValue === 0) {
                    inputField.value = value;
                } else {
                    // If there's already a whole number, add the fraction to it
                    inputField.value = wholePart + value;
                }
                
                // Trigger change event to update any dependent fields
                inputField.dispatchEvent(new Event('change'));
                inputField.focus();
            });
        });
    }

    /**
     * Creates a new ingredient row with quantity, measurement, and name fields
     * @param {number} index The index of the new ingredient row
     * @returns {HTMLElement} The created ingredient row element
     */
    function createIngredientRow(index) {
        const row = document.createElement('div');
        row.className = 'ingredient-row';
        
        // Get measurement options from the first row
        const measurementSelect = document.querySelector('#measurement_0');
        const measurementOptions = measurementSelect ? measurementSelect.innerHTML : '';
        
        // Create a new ingredient row with the given index
        
        row.innerHTML = `
            <div class="form-group">
                <label for="quantity_${index}">Quantity</label>
                <div class="quantity-input-group">
                    <input type="number" name="ingredients[${index}][quantity]" id="quantity_${index}" class="form-control" step="0.001" min="0" required>
                    <div class="fraction-helpers">
                        <span class="fraction-helper" data-value="0.125">⅛</span>
                        <span class="fraction-helper" data-value="0.2">⅕</span>
                        <span class="fraction-helper" data-value="0.25">¼</span>
                        <span class="fraction-helper" data-value="0.33">⅓</span>
                        <span class="fraction-helper" data-value="0.5">½</span>
                        <span class="fraction-helper" data-value="0.67">⅔</span>
                        <span class="fraction-helper" data-value="0.75">¾</span>
                        <span class="fraction-helper" data-value="0.875">⅞</span>
                    </div>
                </div>
                <div class="form-error" style="display: none;"></div>
            </div>
            
            <div class="form-group">
                <label for="measurement_${index}" data-base-label="Measurement">Measurement</label>
                <select name="ingredients[${index}][measurement_id]" id="measurement_${index}" class="form-control" required>
                    ${measurementOptions}
                </select>
                <div class="form-error" style="display: none;"></div>
            </div>
            
            <div class="form-group">
                <label for="ingredient_${index}">Ingredient</label>
                <input type="text" name="ingredients[${index}][name]" id="ingredient_${index}" class="form-control" required>
                <div class="form-error" style="display: none;"></div>
            </div>
            
            <button type="button" class="btn btn-danger remove-ingredient">×</button>
        `;
        
        return row;
    }

    // Set up quantity change listeners for existing ingredient rows
    if (ingredientsContainer) {
        const existingRows = ingredientsContainer.querySelectorAll('.ingredient-row');
        existingRows.forEach(row => {
            setupQuantityChangeListeners(row, originalMeasurementOptions);
        });
    }
    
    if (addIngredientBtn && ingredientsContainer) {
        // Add new ingredient
        addIngredientBtn.addEventListener('click', function() {
            const newRow = createIngredientRow(ingredientCount);
            ingredientsContainer.appendChild(newRow);
            ingredientCount++;
            updateRemoveButtons();
            
            // Add event listeners for quantity changes
            setupQuantityChangeListeners(newRow, originalMeasurementOptions);
            
            // Initialize fraction helpers for the new row
            initializeFractionHelpers();
        });

        // Remove ingredient
        ingredientsContainer.addEventListener('click', function(e) {
            const removeButton = e.target.closest('.remove-ingredient');
            if (removeButton && ingredientsContainer.querySelectorAll('.ingredient-row').length > 1) {
                removeButton.closest('.ingredient-row').remove();
                updateRemoveButtons();
            }
        });
    }

    /**
     * Sets up event listeners for quantity changes to update measurement labels
     * @param {HTMLElement} row The ingredient row to set up listeners for
     * @param {Array} originalOptions The original measurement options
     */
    function setupQuantityChangeListeners(row, originalOptions) {
        const quantityInput = row.querySelector('input[type="number"]');
        const measurementSelect = row.querySelector('select');
        
        if (quantityInput && measurementSelect) {
            // Add a data attribute to store the selected measurement's original text
            measurementSelect.addEventListener('change', function() {
                const selectedOption = measurementSelect.options[measurementSelect.selectedIndex];
                if (selectedOption && selectedOption.value) {
                    // Find the original text for this option
                    const originalOption = originalOptions.find(opt => opt.value === selectedOption.value);
                    if (originalOption) {
                        measurementSelect.setAttribute('data-original-text', originalOption.text);
                        
                        // Update pluralization based on current quantity
                        const quantity = parseFloat(quantityInput.value) || 0;
                        updateSelectedMeasurement(quantityInput, measurementSelect, originalOption.text);
                    }
                }
            });
            
            // Update measurement text when quantity changes
            quantityInput.addEventListener('input', function() {
                const originalText = measurementSelect.getAttribute('data-original-text');
                if (originalText) {
                    updateSelectedMeasurement(quantityInput, measurementSelect, originalText);
                }
            });
            
            // Initialize with current values
            const selectedOption = measurementSelect.options[measurementSelect.selectedIndex];
            if (selectedOption && selectedOption.value) {
                const originalOption = originalOptions.find(opt => opt.value === selectedOption.value);
                if (originalOption) {
                    measurementSelect.setAttribute('data-original-text', originalOption.text);
                    updateSelectedMeasurement(quantityInput, measurementSelect, originalOption.text);
                }
            }
        }
    }
    
    /**
     * Updates the selected measurement text based on quantity
     * @param {HTMLInputElement} quantityInput The quantity input element
     * @param {HTMLSelectElement} measurementSelect The measurement select element
     * @param {string} originalText The original measurement text
     */
    function updateSelectedMeasurement(quantityInput, measurementSelect, originalText) {
        const quantity = parseFloat(quantityInput.value) || 0;
        const selectedIndex = measurementSelect.selectedIndex;
        
        if (selectedIndex >= 0 && originalText) {
            const selectedOption = measurementSelect.options[selectedIndex];
            
            // Store the original singular form as a data attribute for form submission
            selectedOption.setAttribute('data-singular', originalText);
            
            if (quantity > 1) {
                const pluralizedText = pluralizeMeasurement(originalText, quantity);
                selectedOption.textContent = pluralizedText;
                // Updated to pluralized measurement
            } else {
                selectedOption.textContent = originalText;
                // Reset to singular measurement
            }
        }
    }
}

    /**
     * Initializes dynamic direction steps functionality
     * Allows users to add and remove direction steps with step number and instructions
     */
    function initializeDirections() {
    const directionsContainer = document.getElementById('directions-container');
    const addStepBtn = document.getElementById('add-step');
    let stepCount = directionsContainer ? 
        directionsContainer.querySelectorAll('.direction-row').length : 0;

    /**
     * Creates a new direction step with step number and instructions
     * @param {number} index The index of the new direction step
     * @returns {HTMLElement} The created direction step element
     */
    function createDirectionRow(index) {
        const row = document.createElement('div');
        row.className = 'direction-row';
        const stepNumber = index + 1;
        
        row.innerHTML = `
            <span class="step-number">${stepNumber}</span>
            <div class="form-group">
                <label for="step_${index}">Step ${stepNumber} Instructions</label>
                <textarea name="steps[${index}][instruction]" id="step_${index}" class="form-control" rows="2" required></textarea>
                <div class="form-error" style="display: none;"></div>
                <input type="hidden" name="steps[${index}][step_number]" value="${stepNumber}">
            </div>
            <button type="button" class="btn btn-danger remove-step">×</button>
        `;
        
        return row;
    }

    /**
     * Updates the step numbers and instructions for all direction steps
     */
    function updateStepNumbers() {
        const steps = directionsContainer.querySelectorAll('.direction-row');
        steps.forEach((step, index) => {
            const stepNumber = index + 1;
            step.querySelector('.step-number').textContent = stepNumber;
            
            // Update the hidden input field for step_number
            const hiddenInput = step.querySelector('input[type="hidden"]');
            hiddenInput.name = `steps[${index}][step_number]`;
            hiddenInput.value = stepNumber;
            
            const textarea = step.querySelector('textarea');
            textarea.name = `steps[${index}][instruction]`;
            textarea.id = `step_${index}`;
            
            const label = step.querySelector('label');
            label.textContent = `Step ${stepNumber} Instructions`;
            label.setAttribute('for', `step_${index}`);
        });
    }

    if (addStepBtn && directionsContainer) {
        // Add new step
        addStepBtn.addEventListener('click', function() {
            const currentStepCount = directionsContainer.querySelectorAll('.direction-row').length;
            const newRow = createDirectionRow(currentStepCount);
            directionsContainer.appendChild(newRow);
            updateRemoveButtons('.remove-step');
        });

        // Remove step
        directionsContainer.addEventListener('click', function(e) {
            const removeButton = e.target.closest('.remove-step');
            if (removeButton && directionsContainer.querySelectorAll('.direction-row').length > 1) {
                removeButton.closest('.direction-row').remove();
                updateStepNumbers();
                updateRemoveButtons('.remove-step');
            }
        });
    }
}

    /**
     * Initializes form validation functionality
     * Checks for required fields and displays error messages if necessary
     */
    function initializeFormValidation() {
    const form = document.querySelector('.recipe-form form');
    if (form) {
        form.addEventListener('submit', function(e) {
            // Restore singular forms of measurement names before submission
            const measurementSelects = form.querySelectorAll('select[id^="measurement_"]');
            measurementSelects.forEach(select => {
                const selectedOption = select.options[select.selectedIndex];
                if (selectedOption && selectedOption.hasAttribute('data-singular')) {
                    // Restore the original singular text for database storage
                    const singularText = selectedOption.getAttribute('data-singular');
                    // Removed console.log for production
                    selectedOption.textContent = singularText;
                }
            });
            
            let isValid = true;
            const requiredFields = form.querySelectorAll('[required]');
            
            // Remove existing error messages
            form.querySelectorAll('.form-error').forEach(el => {
                el.style.display = 'none';
                el.textContent = '';
            });
            form.querySelectorAll('.error').forEach(el => el.classList.remove('error'));

            // Check each required field
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('error');
                    
                    // Find or create error message container
                    let errorContainer = field.nextElementSibling;
                    if (!errorContainer || !errorContainer.classList.contains('form-error')) {
                        errorContainer = document.createElement('div');
                        errorContainer.className = 'form-error';
                        field.parentNode.insertBefore(errorContainer, field.nextSibling);
                    }
                    
                    // Set error message
                    errorContainer.textContent = field.getAttribute('data-error-message') || 'This field is required';
                    errorContainer.style.display = 'block';
                }
            });

            // Check file input if required
            const fileInput = form.querySelector('input[type="file"][required]');
            if (fileInput && !fileInput.files.length) {
                isValid = false;
                fileInput.classList.add('error');
                
                let errorContainer = fileInput.nextElementSibling;
                if (!errorContainer || !errorContainer.classList.contains('form-error')) {
                    errorContainer = document.createElement('div');
                    errorContainer.className = 'form-error';
                    fileInput.parentNode.insertBefore(errorContainer, fileInput.nextSibling);
                }
                
                errorContainer.textContent = 'Please select a file';
                errorContainer.style.display = 'block';
            }

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
    }
}

    /**
     * Initializes recipe header background functionality
     * Sets the background image of the recipe header based on the uploaded image
     */
    function initializeRecipeHeaderBackground() {
        const imageInput = document.getElementById('recipe_image');
        const headerContainer = document.querySelector('.recipe-form .page-header');
        
        if (imageInput && headerContainer) {
            imageInput.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    // Create a URL for the uploaded image
                    const url = URL.createObjectURL(file);
                    
                    // Set the background image of the recipe header
                    headerContainer.style.backgroundImage = `url(${url})`;
                }
            });
        }
    }
    
    // Public API
    return {
        init: initialize
    };
})();
