<?php

/**
 * RecipeFavorite class for managing recipe favorites
 * Extends DatabaseObject for database operations
 */
class RecipeFavorite extends DatabaseObject {
    /** @var string Database table name */
    static protected $table_name = 'user_favorite';
    /** @var array Database columns */
    static protected $db_columns = ['favorite_id', 'user_id', 'recipe_id', 'created_at'];
    /** @var string Primary key column */
    static protected $primary_key = 'favorite_id';

    /** @var int Unique identifier for the favorite */
    public $favorite_id;
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
     * Toggles favorite status for a recipe
     * @param int $user_id The user ID
     * @param int $recipe_id The recipe ID
     * @return bool True if now favorited, false if unfavorited
     */
    public static function toggle_favorite($user_id, $recipe_id) {
        if(self::is_favorited($user_id, $recipe_id)) {
            // Remove favorite
            $sql = "DELETE FROM " . static::$table_name;
            $sql .= " WHERE user_id = ? AND recipe_id = ?";
            
            $database = static::get_database();
            $stmt = $database->prepare($sql);
            $stmt->bind_param("ii", $user_id, $recipe_id);
            $stmt->execute();
            
            return false;
        } else {
            // Add favorite
            $favorite = new self([
                'user_id' => $user_id,
                'recipe_id' => $recipe_id
            ]);
            $favorite->create();
            
            return true;
        }
    }
}
?>
