import { toggleFavorite } from '../../utils/favorites.js';

document.addEventListener('DOMContentLoaded', () => {
    // Handle favorite button clicks
    document.querySelectorAll('.favorite-btn').forEach(button => {
        button.addEventListener('click', async (e) => {
            e.preventDefault();
            e.stopPropagation();
            
            const recipeId = button.dataset.recipeId;
            const result = await toggleFavorite(recipeId);
            
            if (result.success) {
                // If unfavorited from favorites page, remove the card with animation
                const card = button.closest('.recipe-card');
                if (card) {
                    card.style.transition = 'opacity 0.3s ease-out';
                    card.style.opacity = '0';
                    setTimeout(() => {
                        card.remove();
                        
                        // If no more recipes, show the empty state
                        const recipeGrid = document.querySelector('.recipe-grid');
                        if (recipeGrid && recipeGrid.children.length === 0) {
                            const emptyState = `
                                <div class="no-recipes">
                                    <p>You haven't favorited any recipes yet. Browse our recipes and click the heart icon to add them to your favorites!</p>
                                    <a href="/FlavorConnect/public/recipes/index.php" class="btn btn-primary">Browse Recipes</a>
                                </div>
                            `;
                            recipeGrid.insertAdjacentHTML('beforebegin', emptyState);
                            recipeGrid.remove();
                        }
                    }, 300);
                }
            } else {
                console.error('Failed to toggle favorite:', result.error);
            }
        });
    });
});
