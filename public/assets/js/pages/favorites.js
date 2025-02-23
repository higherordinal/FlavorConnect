import { toggleFavorite } from '../utils/favorites.js';

document.addEventListener('DOMContentLoaded', () => {
    // Handle favorite button clicks
    document.querySelectorAll('.favorite-btn').forEach(button => {
        button.addEventListener('click', async (e) => {
            e.preventDefault();
            e.stopPropagation();
            
            const recipeId = button.dataset.recipeId;
            if (!recipeId) {
                console.error('No recipe ID found on favorite button');
                return;
            }

            // Disable button while processing
            button.disabled = true;
            
            try {
                const result = await toggleFavorite(recipeId);
                
                if (result.success) {
                    // If unfavorited from favorites page, remove the card
                    const card = button.closest('.recipe-card');
                    if (card) {
                        // Fade out animation
                        card.style.transition = 'opacity 0.3s ease-out';
                        card.style.opacity = '0';
                        
                        // Remove card after animation
                        setTimeout(() => {
                            card.remove();
                            
                            // Check if there are any recipes left
                            const recipeGrid = document.querySelector('.recipe-grid');
                            if (recipeGrid && recipeGrid.children.length === 0) {
                                // Show empty state
                                const emptyState = `
                                    <div class="no-recipes">
                                        <p>You haven't favorited any recipes yet. Browse our recipes and click the heart icon to add them to your favorites!</p>
                                        <a href="/recipes/index.php" class="btn btn-primary">Browse Recipes</a>
                                    </div>
                                `;
                                recipeGrid.insertAdjacentHTML('beforebegin', emptyState);
                                recipeGrid.remove();
                            }
                        }, 300);
                    }
                } else {
                    throw new Error(result.error || 'Failed to update favorite status');
                }
            } catch (error) {
                console.error('Error:', error);
                // Show error message to user
                const errorMessage = document.createElement('div');
                errorMessage.className = 'alert alert-danger';
                errorMessage.textContent = error.message;
                button.parentNode.insertBefore(errorMessage, button.nextSibling);
                setTimeout(() => errorMessage.remove(), 3000);
            } finally {
                button.disabled = false;
            }
        });
    });
});
