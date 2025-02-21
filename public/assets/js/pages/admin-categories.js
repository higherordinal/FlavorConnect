document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    
    // Handle delete buttons
    document.querySelectorAll('.action.delete').forEach(button => {
        button.addEventListener('click', function() {
            if(confirm('Are you sure you want to delete this item?')) {
                const row = this.closest('tr');
                const id = this.dataset.id;
                const type = this.closest('section').querySelector('h2').textContent.toLowerCase().replace('recipe ', '').replace('s', '');
                
                // Create hidden input for deletion
                const deleteInput = document.createElement('input');
                deleteInput.type = 'hidden';
                deleteInput.name = `delete_${type}[]`;
                deleteInput.value = id;
                form.appendChild(deleteInput);
                
                // Hide the row visually
                row.style.display = 'none';
            }
        });
    });
});
