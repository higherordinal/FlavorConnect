document.addEventListener('DOMContentLoaded', function() {
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
});

// Image Preview Functionality
function initializeImagePreview() {
    const imageInput = document.getElementById('recipe_image');
    const previewContainer = document.createElement('div');
    previewContainer.className = 'mt-2';
    
    if (imageInput) {
        imageInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                // Remove any existing preview
                while (previewContainer.firstChild) {
                    previewContainer.removeChild(previewContainer.firstChild);
                }

                // Create preview image
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.alt = 'Recipe image preview';
                        img.className = 'img-thumbnail';
                        img.style.maxWidth = '200px';
                        previewContainer.appendChild(img);
                    };
                    reader.readAsDataURL(file);
                }
            }
        });

        // Insert preview container after the file input
        imageInput.parentNode.insertBefore(previewContainer, imageInput.nextSibling);
    }
}

// Alt Text Generation
function initializeAltTextGeneration() {
    const titleInput = document.getElementById('title');
    const altTextInput = document.getElementById('alt_text');
    
    if (titleInput && altTextInput) {
        titleInput.addEventListener('input', function() {
            // Only update alt text if it's empty or matches previous title
            const currentAlt = altTextInput.value;
            const previousTitle = titleInput.dataset.previousTitle || '';
            
            if (!currentAlt || currentAlt === previousTitle + ' recipe image') {
                altTextInput.value = this.value + ' recipe image';
            }
            
            // Store current title for next comparison
            titleInput.dataset.previousTitle = this.value;
        });
    }
}

// Ingredients Functionality
function initializeIngredients() {
    const ingredientsContainer = document.getElementById('ingredients-container');
    const addIngredientBtn = document.getElementById('add-ingredient');
    let ingredientCount = ingredientsContainer ? 
        ingredientsContainer.querySelectorAll('.ingredient-row').length : 0;

    function createIngredientRow(index) {
        const row = document.createElement('div');
        row.className = 'ingredient-row';
        
        // Get measurement options from the first row
        const measurementSelect = document.querySelector('#measurement_0');
        const measurementOptions = measurementSelect ? measurementSelect.innerHTML : '';
        
        row.innerHTML = `
            <div class="form-group">
                <label for="quantity_${index}">Quantity</label>
                <input type="number" name="ingredients[${index}][quantity]" id="quantity_${index}" class="form-control" step="0.01" min="0" required>
                <div class="form-error" style="display: none;"></div>
            </div>
            
            <div class="form-group">
                <label for="measurement_${index}">Measurement</label>
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

    if (addIngredientBtn && ingredientsContainer) {
        // Add new ingredient
        addIngredientBtn.addEventListener('click', function() {
            const newRow = createIngredientRow(ingredientCount);
            ingredientsContainer.appendChild(newRow);
            ingredientCount++;
            updateRemoveButtons();
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

    function updateRemoveButtons() {
        const removeButtons = document.querySelectorAll('.remove-ingredient');
        const showButtons = removeButtons.length > 1;
        removeButtons.forEach(button => button.style.display = showButtons ? 'flex' : 'none');
    }
}

// Directions Functionality
function initializeDirections() {
    const directionsContainer = document.getElementById('directions-container');
    const addStepBtn = document.getElementById('add-step');
    let stepCount = directionsContainer ? 
        directionsContainer.querySelectorAll('.direction-row').length : 0;

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

    function updateStepNumbers() {
        const steps = directionsContainer.querySelectorAll('.direction-row');
        steps.forEach((step, index) => {
            const stepNumber = index + 1;
            step.querySelector('.step-number').textContent = stepNumber;
            step.querySelector('input[type="hidden"]').value = stepNumber;
            
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
            updateRemoveStepButtons();
        });

        // Remove step
        directionsContainer.addEventListener('click', function(e) {
            const removeButton = e.target.closest('.remove-step');
            if (removeButton && directionsContainer.querySelectorAll('.direction-row').length > 1) {
                removeButton.closest('.direction-row').remove();
                updateStepNumbers();
                updateRemoveStepButtons();
            }
        });
    }

    function updateRemoveStepButtons() {
        const removeButtons = document.querySelectorAll('.remove-step');
        const showButtons = removeButtons.length > 1;
        removeButtons.forEach(button => button.style.display = showButtons ? 'flex' : 'none');
    }
}

// Form Validation
function initializeFormValidation() {
    const form = document.querySelector('.recipe-form form');
    if (form) {
        form.addEventListener('submit', function(e) {
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
