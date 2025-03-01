document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    
    // Handle add buttons
    document.querySelectorAll('.action.add').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const section = this.closest('section');
            const type = section.querySelector('h2').textContent.toLowerCase().replace('recipe ', '').replace('s', '');
            const tbody = section.querySelector('tbody');
            const input = this.closest('tr').querySelector('input[type="text"]');
            
            if (input.value.trim() === '') {
                return;
            }

            // Create new row for the item
            const newRow = document.createElement('tr');
            const timestamp = Date.now();
            
            // Check if we're in the measurements section by counting table headers
            const headers = section.querySelectorAll('thead th');
            const hasTwoColumns = headers.length === 2; // Measurements only has Name and Actions
            
            // Create HTML based on the table structure
            let rowHtml = `
                <td data-label="Name">
                    <input type="text" name="${type}s[new_${timestamp}][name]" value="${input.value}" class="form-control">
                </td>`;
                
            // Only add Recipes column if the table has 3 columns
            if (!hasTwoColumns) {
                rowHtml += `<td data-label="Recipes">0</td>`;
            }
            
            rowHtml += `
                <td data-label="Actions" class="actions">
                    <button type="button" class="action delete" title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>`;
                
            newRow.innerHTML = rowHtml;

            // Insert new row before the "new" row
            const newItemRow = tbody.querySelector('.new-row');
            tbody.insertBefore(newRow, newItemRow);

            // Clear the input
            input.value = '';

            // Add delete handler to new row
            addDeleteHandler(newRow.querySelector('.action.delete'));
        });
    });

    // Function to add delete handler to a button
    function addDeleteHandler(button) {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            if(confirm('Are you sure you want to delete this item?')) {
                const row = this.closest('tr');
                const input = row.querySelector('input[type="text"]');
                const section = row.closest('section');
                const type = section.querySelector('h2').textContent.toLowerCase().replace('recipe ', '').replace('s', '');
                
                // If this is an existing item (has an ID), mark it for deletion
                if (input && !input.name.includes('new_')) {
                    const match = input.name.match(/\[(\d+)\]/);
                    if (match) {
                        const id = match[1];
                        const hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.name = `delete_${type}s[]`;
                        hiddenInput.value = id;
                        form.appendChild(hiddenInput);
                    }
                }
                
                row.remove();
            }
        });
    }

    // Add delete handlers to existing delete buttons
    document.querySelectorAll('.action.delete').forEach(button => {
        addDeleteHandler(button);
    });

    // Form submission handler
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Show loading state
        const submitButton = this.querySelector('button[type="submit"]');
        const originalText = submitButton.textContent;
        submitButton.disabled = true;
        submitButton.textContent = 'Saving...';

        // Create a new FormData object
        const formData = new FormData(this);
        
        // No need to manually append inputs - FormData constructor handles this
        
        // Submit form data
        fetch(this.action, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                const message = document.createElement('div');
                message.className = 'message success';
                message.textContent = data.message || 'Changes saved successfully.';
                form.insertBefore(message, form.firstChild);

                // Remove success message after 3 seconds
                setTimeout(() => message.remove(), 3000);

                // Refresh the page to show updated data
                setTimeout(() => location.reload(), 1000);
            } else {
                // Show error message
                const message = document.createElement('div');
                message.className = 'message error';
                message.textContent = data.message || 'Failed to save changes.';
                form.insertBefore(message, form.firstChild);

                // Reset button state
                submitButton.disabled = false;
                submitButton.textContent = originalText;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Show error message
            const message = document.createElement('div');
            message.className = 'message error';
            message.textContent = 'An error occurred while saving changes.';
            form.insertBefore(message, form.firstChild);

            // Reset button state
            submitButton.disabled = false;
            submitButton.textContent = originalText;
        });
    });

    // Store original values when the page loads
    document.querySelectorAll('input[type="text"]').forEach(input => {
        input.setAttribute('data-original-value', input.value.trim());
    });
});
