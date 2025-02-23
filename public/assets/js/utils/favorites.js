/**
 * Toggle the favorite status of a recipe
 * @param {string} recipeId - The ID of the recipe to toggle
 * @returns {Promise<Object>} - The response from the server
 */
export async function toggleFavorite(recipeId) {
    try {
        const response = await fetch('http://localhost:3000/api/favorites', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                userId: window.initialUserData.userId,
                recipeId: parseInt(recipeId)
            })
        });

        if (!response.ok) {
            const data = await response.json();
            throw new Error(data.error || `HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        return {
            success: true,
            is_favorited: data.is_favorited,
            message: data.message
        };
    } catch (error) {
        console.error('Error toggling favorite:', error);
        return {
            success: false,
            error: error.message
        };
    }
}
