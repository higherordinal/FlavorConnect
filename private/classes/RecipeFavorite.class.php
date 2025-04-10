<?php

/**
 * RecipeFavorite class for managing recipe favorites
 * Extends DatabaseObject for database operations
 */
class RecipeFavorite extends DatabaseObject {
    /** @var string Database table name */
    static protected $table_name = 'user_favorite';
    /** @var array Database columns */
    static protected $db_columns = ['user_id', 'recipe_id', 'created_at'];
    /** @var array Primary key columns */
    static protected $primary_key = ['user_id', 'recipe_id'];

    /** @var int ID of user who favorited */
    public $user_id;
    /** @var int ID of favorited recipe */
    public $recipe_id;
    /** @var string Timestamp when favorited */
    public $created_at;

    /**
     * Constructor for RecipeFavorite class
     * @param array $args Associative array of property values
     */
    public function __construct($args=[]) {
        $this->user_id = $args['user_id'] ?? '';
        $this->recipe_id = $args['recipe_id'] ?? '';
        $this->created_at = $args['created_at'] ?? date('Y-m-d H:i:s');
    }

    /**
     * Checks if a recipe is favorited by a user
     * @param int $user_id The user ID
     * @param int $recipe_id The recipe ID
     * @return bool True if favorited
     */
    public static function is_favorited($user_id, $recipe_id) {
        if (!$user_id || !$recipe_id) return false;
        
        $sql = "SELECT COUNT(*) as count FROM " . static::$table_name;
        $sql .= " WHERE user_id = ? AND recipe_id = ?";
        
        $database = static::get_database();
        $stmt = $database->prepare($sql);
        $stmt->bind_param("ii", $user_id, $recipe_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return $row['count'] > 0;
    }

    /**
     * Add a recipe to user's favorites
     * @param int $user_id The user ID
     * @param int $recipe_id The recipe ID
     * @return bool True if successful
     */
    public static function add_favorite($user_id, $recipe_id) {
        if (!$user_id || !$recipe_id) return false;
        if (static::is_favorited($user_id, $recipe_id)) return true;

        $favorite = new static([
            'user_id' => $user_id,
            'recipe_id' => $recipe_id
        ]);

        return $favorite->save();
    }

    /**
     * Remove a recipe from user's favorites
     * @param int $user_id The user ID
     * @param int $recipe_id The recipe ID
     * @return bool True if successful
     */
    public static function remove_favorite($user_id, $recipe_id) {
        if (!$user_id || !$recipe_id) return false;
        if (!static::is_favorited($user_id, $recipe_id)) return true;

        $sql = "DELETE FROM " . static::$table_name;
        $sql .= " WHERE user_id = ? AND recipe_id = ?";
        
        $database = static::get_database();
        $stmt = $database->prepare($sql);
        $stmt->bind_param("ii", $user_id, $recipe_id);
        
        return $stmt->execute();
    }

    /**
     * Get all favorites for a user
     * @param int $user_id The user ID
     * @return array Array of Recipe objects
     */
    public static function get_user_favorites($user_id) {
        if (!$user_id) return [];

        $sql = "SELECT r.* FROM recipe r ";
        $sql .= "JOIN " . static::$table_name . " f ON r.recipe_id = f.recipe_id ";
        $sql .= "WHERE f.user_id = ?";

        $database = static::get_database();
        $stmt = $database->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $favorites = [];
        while ($row = $result->fetch_assoc()) {
            $favorites[] = Recipe::create_from_array($row);
        }

        return $favorites;
    }

    /**
     * Count total favorites for a user
     * @param int $user_id The user ID
     * @return int Total count of favorites
     */
    public static function count_by_user_id($user_id) {
        if (!$user_id) return 0;
        
        $sql = "SELECT COUNT(*) as count FROM " . static::$table_name;
        $sql .= " WHERE user_id = ?";
        
        $database = static::get_database();
        $stmt = $database->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return (int)$row['count'];
    }

    /**
     * Toggle favorite status for a recipe
     * @param int $user_id The user ID
     * @param int $recipe_id The recipe ID
     * @return array Status array with success and is_favorited
     */
    public static function toggle_favorite($user_id, $recipe_id) {
        if (!$user_id || !$recipe_id) {
            return ['success' => false, 'message' => 'Invalid user or recipe ID'];
        }

        $is_favorited = static::is_favorited($user_id, $recipe_id);
        
        // Perform the appropriate action based on current favorite status
        if ($is_favorited) {
            $success = static::remove_favorite($user_id, $recipe_id);
            $new_status = false;
            $message = $success ? 'Removed from favorites' : 'Failed to remove from favorites';
        } else {
            $success = static::add_favorite($user_id, $recipe_id);
            $new_status = true;
            $message = $success ? 'Added to favorites' : 'Failed to add to favorites';
        }

        // Return a consistent response format
        return [
            'success' => $success,
            'is_favorited' => $new_status,
            'message' => $message
        ];
    }
}
?>
