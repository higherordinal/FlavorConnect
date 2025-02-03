<?php
/**
 * Recipe API Endpoints
 * Handles all recipe-related API requests
 */

class RecipeEndpoints {
    /**
     * Handle recipe requests
     * @param array $request Request data
     * @return array Response data
     */
    public static function handle($request) {
        $action = $request['query']['action'] ?? '';

        switch ($action) {
            case 'list':
                return self::listRecipes();
            case 'get':
                return self::getRecipe($request['query']['id'] ?? null);
            default:
                throw new Exception('Invalid action', 400);
        }
    }

    /**
     * List all recipes
     * @return array Array of recipes
     */
    private static function listRecipes() {
        $recipes = Recipe::find_all();
        $response = [];

        foreach($recipes as $recipe) {
            $user = User::find_by_id($recipe->user_id);
            $is_favorited = false;
            
            // Check if recipe is favorited by current user
            if($session->is_logged_in()) {
                $is_favorited = UserFavorite::is_favorite($session->get_user_id(), $recipe->recipe_id);
            }

            $response[] = [
                'id' => $recipe->recipe_id,
                'title' => $recipe->title,
                'description' => $recipe->description,
                'style' => $recipe->style() ? $recipe->style()->name : null,
                'style_id' => $recipe->style_id,
                'diet' => $recipe->diet() ? $recipe->diet()->name : null,
                'diet_id' => $recipe->diet_id,
                'type' => $recipe->type() ? $recipe->type()->name : null,
                'type_id' => $recipe->type_id,
                'prep_time' => $recipe->prep_time,
                'cook_time' => $recipe->cook_time,
                'image' => $recipe->img_file_path,
                'created_at' => $recipe->created_at,
                'user_id' => $recipe->user_id,
                'username' => $user ? $user->username : 'Unknown',
                'is_favorited' => $is_favorited,
                'rating' => $recipe->get_average_rating(),
                'rating_count' => $recipe->rating_count()
            ];
        }

        return $response;
    }

    /**
     * Get a single recipe
     * @param int|null $id Recipe ID
     * @return array Recipe data
     */
    private static function getRecipe($id) {
        if (!$id) {
            throw new Exception('Recipe ID is required', 400);
        }

        $recipe = Recipe::find_by_id($id);
        if (!$recipe) {
            throw new Exception('Recipe not found', 404);
        }

        $user = User::find_by_id($recipe->user_id);
        $is_favorited = false;
        
        if($session->is_logged_in()) {
            $is_favorited = UserFavorite::is_favorite($session->get_user_id(), $recipe->recipe_id);
        }

        return [
            'id' => $recipe->recipe_id,
            'title' => $recipe->title,
            'description' => $recipe->description,
            'style' => $recipe->style() ? $recipe->style()->name : null,
            'style_id' => $recipe->style_id,
            'diet' => $recipe->diet() ? $recipe->diet()->name : null,
            'diet_id' => $recipe->diet_id,
            'type' => $recipe->type() ? $recipe->type()->name : null,
            'type_id' => $recipe->type_id,
            'prep_time' => $recipe->prep_time,
            'cook_time' => $recipe->cook_time,
            'image' => $recipe->img_file_path,
            'created_at' => $recipe->created_at,
            'user_id' => $recipe->user_id,
            'username' => $user ? $user->username : 'Unknown',
            'is_favorited' => $is_favorited,
            'rating' => $recipe->get_average_rating(),
            'rating_count' => $recipe->rating_count()
        ];
    }
}
