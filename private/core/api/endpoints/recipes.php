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
        global $session;
        $action = $request['query']['action'] ?? '';

        switch ($action) {
            case 'list':
                return self::listRecipes();
            case 'get':
                return self::getRecipe($request['query']['id'] ?? null);
            case 'create':
                if (!$session->is_logged_in()) {
                    throw new Exception('You must be logged in to create recipes', 401);
                }
                return self::createRecipe($request['body'] ?? []);
            case 'update':
                if (!$session->is_logged_in()) {
                    throw new Exception('You must be logged in to update recipes', 401);
                }
                return self::updateRecipe($request['query']['id'] ?? null, $request['body'] ?? []);
            case 'delete':
                if (!$session->is_logged_in()) {
                    throw new Exception('You must be logged in to delete recipes', 401);
                }
                return self::deleteRecipe($request['query']['id'] ?? null);
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

    /**
     * Create a new recipe
     * @param array $data Recipe data
     * @return Recipe Created recipe
     * @throws Exception if validation fails
     */
    private static function createRecipe($data) {
        global $session;
        
        // Add user_id to data
        $data['user_id'] = $session->get_user_id();
        
        $recipe = new Recipe($data);
        if (!$recipe->save()) {
            throw new Exception('Failed to create recipe', 500);
        }

        return $recipe;
    }

    /**
     * Update an existing recipe
     * @param int $id Recipe ID
     * @param array $data Updated recipe data
     * @return Recipe Updated recipe
     * @throws Exception if recipe not found or user lacks permission
     */
    private static function updateRecipe($id, $data) {
        global $session;
        
        if (!$id) {
            throw new Exception('Recipe ID is required', 400);
        }

        $recipe = Recipe::find_by_id($id);
        if (!$recipe) {
            throw new Exception('Recipe not found', 404);
        }

        // Check if user has permission to edit this recipe
        if ($recipe->user_id != $session->get_user_id() && !$session->is_admin()) {
            throw new Exception('You do not have permission to edit this recipe', 403);
        }

        $recipe->merge_attributes($data);
        if (!$recipe->save()) {
            throw new Exception('Failed to update recipe', 500);
        }

        return $recipe;
    }

    /**
     * Delete a recipe
     * @param int $id Recipe ID
     * @return bool True if successful
     * @throws Exception if recipe not found or user lacks permission
     */
    private static function deleteRecipe($id) {
        global $session;
        
        if (!$id) {
            throw new Exception('Recipe ID is required', 400);
        }

        $recipe = Recipe::find_by_id($id);
        if (!$recipe) {
            throw new Exception('Recipe not found', 404);
        }

        // Check if user has permission to delete this recipe
        if ($recipe->user_id != $session->get_user_id() && !$session->is_admin()) {
            throw new Exception('You do not have permission to delete this recipe', 403);
        }

        if (!$recipe->delete()) {
            throw new Exception('Failed to delete recipe', 500);
        }

        return true;
    }
}
