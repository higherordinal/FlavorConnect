// Get the API base URL from the initialUserData
const apiBaseUrl = window.initialUserData?.apiBaseUrl || 'http://localhost:3000';

/**
 * Check if a recipe is favorited by the current user
 * @param {number} recipeId - The ID of the recipe to check
 * @returns {Promise<boolean>} - Whether the recipe is favorited
 */
export async function checkFavoriteStatus(recipeId) {
    try {
        if (!window.initialUserData?.isLoggedIn) return false;
        
        const userId = window.initialUserData.userId;
        const response = await fetch(`${apiBaseUrl}/api/favorites/${userId}/${recipeId}`);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const data = await response.json();
        return data.isFavorited;
    } catch (error) {
        console.error('Error checking favorite status:', error);
        return false;
    }
}

/**
 * Toggle the favorite status of a recipe
 * @param {number} recipeId - The ID of the recipe to toggle
 * @returns {Promise<Object>} - Object with success and isFavorited properties
 */
export async function toggleFavorite(recipeId) {
    try {
        if (!window.initialUserData?.isLoggedIn) {
            window.location.href = '/FlavorConnect/public/login.php';
            return { success: false, error: 'User not logged in' };
        }

        const userId = window.initialUserData.userId;
        const response = await fetch(`${apiBaseUrl}/api/favorites/toggle`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ userId, recipeId })
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        return { success: true, isFavorited: data.isFavorited };
    } catch (error) {
        console.error('Error toggling favorite:', error);
        return { success: false, error: error.message };
    }
}

/**
 * Initialize favorite buttons with click handlers
 */
export function initializeFavoriteButtons() {
    document.querySelectorAll('.favorite-btn').forEach(btn => {
        if (!btn.dataset.initialized) {
            btn.dataset.initialized = 'true';
            btn.addEventListener('click', async (e) => {
                e.preventDefault();
                const recipeId = btn.dataset.recipeId;
                const result = await toggleFavorite(recipeId);
                
                // Check if we're on the favorites page
                const isFavoritesPage = window.location.pathname.includes('/users/favorites.php');
                
                if (isFavoritesPage && !result.isFavorited) {
                    // If unfavorited from favorites page, remove the card with animation
                    const card = btn.closest('.recipe-card');
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
                                        <a href="/recipes/index.php" class="btn btn-primary">Browse Recipes</a>
                                    </div>
                                `;
                                recipeGrid.insertAdjacentHTML('beforebegin', emptyState);
                                recipeGrid.remove();
                            }
                        }, 300);
                    }
                } else {
                    // Update button appearance for non-favorites pages
                    if (result.isFavorited) {
                        btn.classList.add('favorited');
                        btn.querySelector('i').classList.remove('far');
                        btn.querySelector('i').classList.add('fas');
                    } else {
                        btn.classList.remove('favorited');
                        btn.querySelector('i').classList.remove('fas');
                        btn.querySelector('i').classList.add('far');
                    }
                }
            });
        }
    });
}
