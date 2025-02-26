<?php
/**
 * Favorites API Endpoints
 * Handles all favorite-related API requests
 */

class FavoriteEndpoints {
    /**
     * Handle favorite requests
     * @param array $request Request data
     * @return array Response data
     * @throws Exception if user is not logged in or request is invalid
     */
    public static function handle($request) {
        global $session;

        // Ensure user is logged in
        if (!$session->is_logged_in()) {
            throw new Exception('You must be logged in to favorite recipes', 401);
        }

        $recipe_id = $request['body']['recipe_id'] ?? '';
        $is_favorited = ($request['body']['is_favorited'] ?? '') === 'true';
        $user_id = $session->get_user_id();

        if (!$recipe_id) {
            throw new Exception('Recipe ID is required', 400);
        }

        try {
            if ($is_favorited) {
                // Add favorite
                UserFavorite::add_favorite($user_id, $recipe_id);
            } else {
                // Remove favorite
                UserFavorite::remove_favorite($user_id, $recipe_id);
            }

            return ['message' => 'Favorite status updated successfully'];
        } catch (Exception $e) {
            throw new Exception('Failed to update favorite status: ' . $e->getMessage(), 500);
        }
    }
}
