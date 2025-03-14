document.addEventListener('DOMContentLoaded', function() {
    // Find all favorite buttons
    const favoriteButtons = document.querySelectorAll('.favorite-btn');

    favoriteButtons.forEach(button => {
        button.addEventListener('click', async function(e) {
            e.preventDefault();
            
            const recipeId = this.dataset.recipeId;
            if (!recipeId) return;

            try {
                // Send request to toggle favorite
                const formData = new FormData();
                formData.append('recipe_id', recipeId);

                const response = await fetch('/api/toggle_favorite.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();
                
                if (data.success) {
                    // Update button state
                    this.classList.toggle('favorited', data.is_favorited);
                    
                    // Update aria-label
                    const actionText = data.is_favorited ? 'Remove from' : 'Add to';
                    this.setAttribute('aria-label', `${actionText} favorites`);
                    
                    // Update icon
                    const icon = this.querySelector('i');
                    if (icon) {
                        icon.classList.toggle('fas', data.is_favorited);
                        icon.classList.toggle('far', !data.is_favorited);
                    }
                }
            } catch (error) {
                console.error('Error toggling favorite:', error);
            }
        });
    });
});
