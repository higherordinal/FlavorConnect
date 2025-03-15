document.addEventListener('DOMContentLoaded', function() {
    // Find all favorite buttons
    const favoriteButtons = document.querySelectorAll('.favorite-btn');

    favoriteButtons.forEach(button => {
        // Skip if already initialized
        if (button.dataset.initialized) return;
        
        button.addEventListener('click', async function(e) {
            e.preventDefault();
            
            const recipeId = this.dataset.recipeId;
            if (!recipeId) return;

            try {
                // Use the namespaced approach with fallback to global function
                let result;
                if (window.FlavorConnect && window.FlavorConnect.favorites) {
                    result = await window.FlavorConnect.favorites.toggle(recipeId);
                } else if (typeof window.toggleFavorite === 'function') {
                    result = await window.toggleFavorite(recipeId);
                } else {
                    console.error('Favorite functionality not available');
                    return;
                }
                
                if (result && result.success) {
                    // Update button state
                    this.classList.toggle('favorited', result.isFavorited);
                    
                    // Update icon
                    const icon = this.querySelector('i');
                    if (icon) {
                        if (result.isFavorited) {
                            icon.classList.remove('far');
                            icon.classList.add('fas');
                        } else {
                            icon.classList.remove('fas');
                            icon.classList.add('far');
                        }
                    }
                    
                    // Update aria-label
                    const actionText = result.isFavorited ? 'Remove from' : 'Add to';
                    this.setAttribute('aria-label', `${actionText} favorites`);
                }
            } catch (error) {
                console.error('Error toggling favorite:', error);
            }
        });
        
        // Mark as initialized
        button.dataset.initialized = 'true';
    });
});
