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
                $rules = ['id' => ['required', 'number', 'min:1']];
                $errors = validate_api_request($request['query'], $rules);
                if (!empty($errors)) {
                    json_error(implode(', ', $errors));
                }
                return self::getRecipe($request['query']['id']);
                
            case 'create':
                require_login();
                $rules = [
                    'title' => ['required'],
                    'description' => ['required'],
                    'prep_time' => ['required', 'number', 'min:1'],
                    'cook_time' => ['required', 'number', 'min:1']
                ];
                $errors = validate_api_request($request['body'], $rules);
                if (!empty($errors)) {
                    json_error(implode(', ', $errors));
                }
                return self::createRecipe($request['body']);
                
            case 'update':
                require_login();
                $rules = [
                    'id' => ['required', 'number', 'min:1'],
                    'title' => ['required'],
                    'description' => ['required']
                ];
                $errors = validate_api_request(array_merge(
                    ['id' => $request['query']['id']], 
                    $request['body']
                ), $rules);
                if (!empty($errors)) {
                    json_error(implode(', ', $errors));
                }
                return self::updateRecipe($request['query']['id'], $request['body']);

            case 'favorite':
                require_login();
                $rules = ['id' => ['required', 'number', 'min:1']];
                $errors = validate_api_request($request['query'], $rules);
                if (!empty($errors)) {
                    json_error(implode(', ', $errors));
                }
                return self::toggleFavorite($request['query']['id'], $request['method'] === 'POST');
                
            case 'delete':
                require_login();
                $rules = ['id' => ['required', 'number', 'min:1']];
                $errors = validate_api_request($request['query'], $rules);
                if (!empty($errors)) {
                    json_error(implode(', ', $errors));
                }
                return self::deleteRecipe($request['query']['id']);
                
            default:
                json_error('Invalid action', 400);
        }
    }

    /**
     * List all recipes
     * @return array Array of recipes
     */
    private static function listRecipes() {
        $recipes = Recipe::find_all();
        return ['recipes' => array_map([self::class, 'formatRecipe'], $recipes)];
    }

    /**
     * Get a single recipe
     * @param int $id Recipe ID
     * @return array Recipe data
     */
    private static function getRecipe($id) {
        $recipe = Recipe::find_by_id($id);
        if (!$recipe) {
            json_error('Recipe not found', 404);
        }
        return ['recipe' => self::formatRecipe($recipe)];
    }

    /**
     * Create a new recipe
     * @param array $data Recipe data
     * @return array Created recipe
     */
    private static function createRecipe($data) {
        $recipe = new Recipe($data);
        if (!$recipe->save()) {
            json_error('Failed to create recipe', 500);
        }
        return ['recipe' => self::formatRecipe($recipe)];
    }

    /**
     * Update a recipe
     * @param int $id Recipe ID
     * @param array $data Recipe data
     * @return array Updated recipe
     */
    private static function updateRecipe($id, $data) {
        $recipe = Recipe::find_by_id($id);
        if (!$recipe) {
            json_error('Recipe not found', 404);
        }
        
        // Check ownership
        if ($recipe->user_id !== $session->get_user_id()) {
            json_error('You can only update your own recipes', 403);
        }
        
        $recipe->merge_attributes($data);
        if (!$recipe->save()) {
            json_error('Failed to update recipe', 500);
        }
        return ['recipe' => self::formatRecipe($recipe)];
    }

    /**
     * Delete a recipe
     * @param int $id Recipe ID
     * @return array Success message
     */
    private static function deleteRecipe($id) {
        $recipe = Recipe::find_by_id($id);
        if (!$recipe) {
            json_error('Recipe not found', 404);
        }
        
        // Check ownership
        if ($recipe->user_id !== $session->get_user_id()) {
            json_error('You can only delete your own recipes', 403);
        }
        
        if (!$recipe->delete()) {
            json_error('Failed to delete recipe', 500);
        }
        return ['message' => 'Recipe deleted successfully'];
    }

    /**
     * Toggle favorite status for a recipe
     * @param int $recipe_id Recipe ID
     * @param bool $favorite True to favorite, false to unfavorite
     * @return array Response data
     */
    private static function toggleFavorite($recipe_id, $favorite) {
        global $session;
        
        $user_id = $session->get_user_id();
        
        if ($favorite) {
            // Add to favorites
            $sql = "INSERT INTO recipe_favorite ";
            $sql .= "(user_id, recipe_id) ";
            $sql .= "VALUES (";
            $sql .= "'" . db_escape($user_id) . "',";
            $sql .= "'" . db_escape($recipe_id) . "'";
            $sql .= ")";
        } else {
            // Remove from favorites
            $sql = "DELETE FROM recipe_favorite ";
            $sql .= "WHERE user_id = '" . db_escape($user_id) . "' ";
            $sql .= "AND recipe_id = '" . db_escape($recipe_id) . "'";
        }
        
        $result = db_query($sql);
        if ($result) {
            return ['success' => true];
        } else {
            json_error('Failed to update favorite status');
        }
    }

    /**
     * Format a recipe for API response
     * @param Recipe $recipe Recipe to format
     * @return array Formatted recipe data
     */
    private static function formatRecipe($recipe) {
        $user = User::find_by_id($recipe->user_id);
        return [
            'recipe_id' => $recipe->recipe_id,
            'title' => $recipe->title,
            'description' => $recipe->description,
            'prep_time' => $recipe->prep_time,
            'cook_time' => $recipe->cook_time,
            'img_file_path' => $recipe->img_file_path,
            'video_url' => $recipe->video_url,
            'alt_text' => $recipe->alt_text,
            'user' => [
                'user_id' => $user->user_id,
                'username' => $user->username
            ]
        ];
    }
}
