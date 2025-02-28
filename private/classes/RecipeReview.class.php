<?php

/**
 * Review class for handling recipe ratings and comments
 */
class RecipeReview extends DatabaseObject {
    /** @var string Database table name for ratings */
    static protected $table_name = "recipe_rating";
    
    /** @var string Primary key field name */
    static protected $primary_key = "rating_id";
    
    /** @var array List of database columns */
    static protected $db_columns = ['rating_id', 'recipe_id', 'user_id', 'rating_value'];

    /** @var int Unique identifier for the rating */
    public $rating_id;
    
    /** @var int Recipe ID this review belongs to */
    public $recipe_id;
    
    /** @var int User ID who wrote the review */
    public $user_id;
    
    /** @var int Rating value (1-5) */
    public $rating_value;
    
    /** @var string Optional comment text from recipe_comment table */
    public $comment_text;
    
    /** @var string Username of the reviewer */
    public $username;

    /** @var string Comment creation timestamp */
    public $comment_created_at;

    /**
     * Constructor for Review class
     * @param array $args Associative array of property values
     */
    public function __construct($args=[]) {
        $this->rating_id = $args['rating_id'] ?? '';
        $this->recipe_id = $args['recipe_id'] ?? '';
        $this->user_id = $args['user_id'] ?? '';
        $this->rating_value = $args['rating_value'] ?? '';
        $this->comment_text = $args['comment_text'] ?? '';
        $this->username = $args['username'] ?? '';
        $this->comment_created_at = $args['comment_created_at'] ?? null;
    }

    /**
     * Saves the review and its associated comment
     * @return bool True if both rating and comment were saved successfully
     */
    public function save() {
        // Debug logging
        error_log("Saving review with data: " . print_r([
            'recipe_id' => $this->recipe_id,
            'user_id' => $this->user_id,
            'rating_value' => $this->rating_value,
            'comment_text' => $this->comment_text
        ], true));
        
        // First save the rating
        $result = parent::save();
        
        // Debug logging
        error_log("Rating save result: " . ($result ? "true" : "false"));
        if (!$result) {
            error_log("Rating save error: " . self::$database->error);
            return false;
        }
        
        // If rating saved and we have a comment, save the comment
        if($result && !empty(trim($this->comment_text))) {
            // First check if a comment already exists for this rating
            $sql = "SELECT comment_id FROM recipe_comment ";
            $sql .= "WHERE recipe_id='" . self::$database->escape_string($this->recipe_id) . "' ";
            $sql .= "AND user_id='" . self::$database->escape_string($this->user_id) . "'";
            
            $existing_comment = self::$database->query($sql);
            
            if($existing_comment && $existing_comment->num_rows > 0) {
                // Update existing comment
                $sql = "UPDATE recipe_comment SET ";
                $sql .= "comment_text='" . self::$database->escape_string(trim($this->comment_text)) . "' ";
                $sql .= "WHERE recipe_id='" . self::$database->escape_string($this->recipe_id) . "' ";
                $sql .= "AND user_id='" . self::$database->escape_string($this->user_id) . "'";
            } else {
                // Insert new comment
                $sql = "INSERT INTO recipe_comment ";
                $sql .= "(recipe_id, user_id, comment_text) ";
                $sql .= "VALUES (";
                $sql .= "'" . self::$database->escape_string($this->recipe_id) . "',";
                $sql .= "'" . self::$database->escape_string($this->user_id) . "',";
                $sql .= "'" . self::$database->escape_string(trim($this->comment_text)) . "'";
                $sql .= ")";
            }
            
            // Debug logging
            error_log("Comment SQL: " . $sql);
            
            $result = self::$database->query($sql);
            if (!$result) {
                error_log("Comment save error: " . self::$database->error);
                return false;
            }
        }
        
        return $result;
    }

    /**
     * Finds all reviews for a specific recipe
     * @param int $recipe_id The ID of the recipe
     * @return array Array of Review objects with comments
     */
    public static function find_by_recipe_id($recipe_id) {
        $sql = "SELECT r.*, rc.comment_text, rc.created_at as comment_created_at, u.username ";
        $sql .= "FROM " . static::$table_name . " AS r ";
        $sql .= "LEFT JOIN user_account AS u ON r.user_id = u.user_id ";
        $sql .= "LEFT JOIN recipe_comment AS rc ON r.recipe_id = rc.recipe_id AND r.user_id = rc.user_id ";
        $sql .= "WHERE r.recipe_id='" . self::$database->escape_string($recipe_id) . "' ";
        $sql .= "ORDER BY CASE WHEN rc.created_at IS NULL THEN 1 ELSE 0 END, rc.created_at DESC";
        
        // Debug logging
        error_log("Find reviews SQL: " . $sql);
        
        $result = static::find_by_sql($sql);
        
        // Debug the results
        error_log("Reviews found for recipe " . $recipe_id . ": " . print_r($result, true));
        
        return $result;
    }

    /**
     * Gets the user who wrote this review
     * @return User|false User object or false if not found
     */
    public function user() {
        if($this->user_id) {
            return User::find_by_id($this->user_id);
        }
        return false;
    }
}
?>
