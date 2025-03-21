/**
 * JavaScript for pluralizing measurements in recipe forms
 */
document.addEventListener('DOMContentLoaded', function() {
    // Function to pluralize measurement names
    function pluralizeMeasurement(name, quantity) {
        if (quantity <= 1) {
            return name;
        }

        // Handle special cases
        switch (name.toLowerCase()) {
            case 'cup':
                return 'cups';
            case 'tablespoon':
                return 'tablespoons';
            case 'teaspoon':
                return 'teaspoons';
            case 'pound':
                return 'pounds';
            case 'ounce':
                return 'ounces';
            case 'quart':
                return 'quarts';
            case 'pint':
                return 'pints';
            case 'gallon':
                return 'gallons';
            case 'slice':
                return 'slices';
            case 'clove':
                return 'cloves';
            case 'bunch':
                return 'bunches';
            case 'stalk':
                return 'stalks';
            case 'leaf':
                return 'leaves';
            case 'sprig':
                return 'sprigs';
            case 'pinch':
                return 'pinches';
            case 'dash':
                return 'dashes';
            case 'gram':
                return 'grams';
            case 'kilogram':
                return 'kilograms';
            case 'milliliter':
                return 'milliliters';
            case 'liter':
                return 'liters';
            case 'bottle':
                return 'bottles';
            case 'can':
                return 'cans';
            case 'package':
                return 'packages';
            default:
                // General rule: add 's' to the end
                if (name.endsWith('s') || name.endsWith('x') || 
                    name.endsWith('z') || name.endsWith('ch') || 
                    name.endsWith('sh')) {
                    return name + 'es';
                } else {
                    return name + 's';
                }
        }
    }

    // Store original measurement names
    const measurementData = {};
    document.querySelectorAll('select[name^="ingredients["][name$="][measurement_id]"]').forEach(select => {
        const options = select.querySelectorAll('option');
        const data = {};
        options.forEach(option => {
            if (option.value) {
                data[option.value] = option.getAttribute('data-original-name');
            }
        });
        const id = select.id;
        measurementData[id] = data;
    });

    // Add event listeners to quantity inputs
    document.querySelectorAll('input[name^="ingredients["][name$="][quantity]"]').forEach(input => {
        input.addEventListener('input', function() {
            const rowIndex = this.id.split('_')[1];
            const measurementSelect = document.getElementById(`measurement_${rowIndex}`);
            const quantity = parseFloat(this.value) || 0;
            
            // Update measurement options based on quantity
            const options = measurementSelect.querySelectorAll('option');
            options.forEach(option => {
                if (option.value) {
                    const originalName = measurementData[measurementSelect.id][option.value];
                    option.textContent = pluralizeMeasurement(originalName, quantity);
                }
            });
        });
    });

    // Handle dynamically added ingredients
    document.getElementById('add-ingredient').addEventListener('click', function() {
        // Wait for the DOM to update
        setTimeout(() => {
            const container = document.getElementById('ingredients-container');
            const rows = container.querySelectorAll('.ingredient-row');
            const lastRow = rows[rows.length - 1];
            const quantityInput = lastRow.querySelector('input[name^="ingredients["][name$="][quantity]"]');
            const measurementSelect = lastRow.querySelector('select[name^="ingredients["][name$="][measurement_id]"]');
            
            // Store original measurement names for the new row
            const options = measurementSelect.querySelectorAll('option');
            const data = {};
            options.forEach(option => {
                if (option.value) {
                    data[option.value] = option.getAttribute('data-original-name');
                }
            });
            measurementData[measurementSelect.id] = data;
            
            // Add event listener to the new quantity input
            quantityInput.addEventListener('input', function() {
                const quantity = parseFloat(this.value) || 0;
                
                // Update measurement options based on quantity
                const options = measurementSelect.querySelectorAll('option');
                options.forEach(option => {
                    if (option.value) {
                        const originalName = measurementData[measurementSelect.id][option.value];
                        option.textContent = pluralizeMeasurement(originalName, quantity);
                    }
                });
            });
        }, 100);
    });
});
