/**
 * Toggle the favorite status of a recipe
 * @param {string} recipeId - The ID of the recipe to toggle
 * @returns {Promise<Object>} - The response from the server
 */
export async function toggleFavorite(recipeId) {
    try {
        const response = await fetch(`/FlavorConnect/public/api/recipes/index.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                action: 'toggle_favorite',
                recipe_id: recipeId
            })
        });

        const data = await response.json();
        
        if (data.error) {
            throw new Error(data.error);
        }

        return data;
    } catch (error) {
        console.error('Error toggling favorite:', error);
        return { success: false, error: error.message };
    }
}
