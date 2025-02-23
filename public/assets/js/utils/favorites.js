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
 * @returns {Promise<boolean>} - The new favorite status
 */
export async function toggleFavorite(recipeId) {
    try {
        if (!window.initialUserData?.isLoggedIn) {
            window.location.href = '/FlavorConnect/public/login.php';
            return false;
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
        return data.isFavorited;
    } catch (error) {
        console.error('Error toggling favorite:', error);
        return false;
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
                const isFavorited = await toggleFavorite(recipeId);
                
                // Update button appearance
                if (isFavorited) {
                    btn.classList.add('favorited');
                    btn.querySelector('i').classList.remove('far');
                    btn.querySelector('i').classList.add('fas');
                } else {
                    btn.classList.remove('favorited');
                    btn.querySelector('i').classList.remove('fas');
                    btn.querySelector('i').classList.add('far');
                }
            });
        }
    });
}
