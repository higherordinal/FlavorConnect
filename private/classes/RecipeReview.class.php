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
     * @return bool True if rating was saved successfully
     */
    public function save() {
        // Direct SQL approach for saving rating
        $sql = "INSERT INTO recipe_rating (recipe_id, user_id, rating_value) VALUES (";
        $sql .= "'" . self::$database->escape_string($this->recipe_id) . "', ";
        $sql .= "'" . self::$database->escape_string($this->user_id) . "', ";
        $sql .= "'" . self::$database->escape_string($this->rating_value) . "'";
        $sql .= ")";
        
        $rating_result = self::$database->query($sql);
        
        if ($rating_result) {
            // Get the new rating_id
            $this->rating_id = self::$database->insert_id;
            
            // If comment provided, save it too
            if (!empty(trim($this->comment_text ?? ''))) {
                $sql = "INSERT INTO recipe_comment (recipe_id, user_id, comment_text) VALUES (";
                $sql .= "'" . self::$database->escape_string($this->recipe_id) . "', ";
                $sql .= "'" . self::$database->escape_string($this->user_id) . "', ";
                $sql .= "'" . self::$database->escape_string(trim($this->comment_text)) . "'";
                $sql .= ")";
                
                $comment_result = self::$database->query($sql);
                
                if (!$comment_result) {
                    error_log("Failed to save comment: " . self::$database->error);
                    // Don't return false here, as the rating was saved successfully
                }
            }
            
            return true;
        } else {
            error_log("Failed to save rating: " . self::$database->error);
            return false;
        }
    }

    /**
     * Deletes a review and its associated comment
     * @param int $recipe_id Recipe ID
     * @param int $user_id User ID
     * @return bool True if deletion was successful
     */
    public static function delete_review($recipe_id, $user_id) {
        // Validate parameters
        if (empty($recipe_id) || empty($user_id)) {
            error_log("Invalid parameters for delete_review: recipe_id={$recipe_id}, user_id={$user_id}");
            return false;
        }
        
        $database = self::get_database();
        
        // First delete any associated comment
        $sql = "DELETE FROM recipe_comment ";
        $sql .= "WHERE recipe_id='" . $database->escape_string($recipe_id) . "' ";
        $sql .= "AND user_id='" . $database->escape_string($user_id) . "'";
        
        $comment_result = $database->query($sql);
        if (!$comment_result) {
            error_log("Failed to delete comment: " . $database->error);
            // Continue anyway to delete the rating
        }
        
        // Then delete the rating
        $sql = "DELETE FROM recipe_rating ";
        $sql .= "WHERE recipe_id='" . $database->escape_string($recipe_id) . "' ";
        $sql .= "AND user_id='" . $database->escape_string($user_id) . "'";
        
        $rating_result = $database->query($sql);
        if (!$rating_result) {
            error_log("Failed to delete rating: " . $database->error);
            return false;
        }
        
        return true;
    }

    /**
     * Deletes a review by its ID (for admin use)
     * @param int $rating_id Rating ID to delete
     * @return bool True if deletion was successful
     */
    public static function delete_by_id($rating_id) {
        // Validate parameter
        if (empty($rating_id)) {
            error_log("Invalid parameter for delete_by_id: rating_id={$rating_id}");
            return false;
        }
        
        $database = self::get_database();
        
        // First get the recipe_id and user_id
        $sql = "SELECT recipe_id, user_id FROM recipe_rating ";
        $sql .= "WHERE rating_id='" . $database->escape_string($rating_id) . "' LIMIT 1";
        
        $result = $database->query($sql);
        if (!$result || $result->num_rows === 0) {
            error_log("Rating not found: rating_id={$rating_id}");
            return false;
        }
        
        $rating = $result->fetch_assoc();
        $recipe_id = $rating['recipe_id'];
        $user_id = $rating['user_id'];
        
        // Delete any associated comment
        $sql = "DELETE FROM recipe_comment ";
        $sql .= "WHERE recipe_id='" . $database->escape_string($recipe_id) . "' ";
        $sql .= "AND user_id='" . $database->escape_string($user_id) . "'";
        
        $comment_result = $database->query($sql);
        if (!$comment_result) {
            error_log("Failed to delete comment: " . $database->error);
            // Continue anyway to delete the rating
        }
        
        // Then delete the rating
        $sql = "DELETE FROM recipe_rating ";
        $sql .= "WHERE rating_id='" . $database->escape_string($rating_id) . "'";
        
        $rating_result = $database->query($sql);
        if (!$rating_result) {
            error_log("Failed to delete rating: " . $database->error);
            return false;
        }
        
        return true;
    }

    /**
     * Finds a review by recipe and user IDs
     * @param int $recipe_id Recipe ID
     * @param int $user_id User ID
     * @return RecipeReview|false The review object or false if not found
     */
    public static function find_by_recipe_and_user($recipe_id, $user_id) {
        $sql = "SELECT r.*, rc.comment_text, rc.created_at as comment_created_at, u.username ";
        $sql .= "FROM " . static::$table_name . " AS r ";
        $sql .= "LEFT JOIN user_account AS u ON r.user_id = u.user_id ";
        $sql .= "LEFT JOIN recipe_comment AS rc ON r.recipe_id = rc.recipe_id AND r.user_id = rc.user_id ";
        $sql .= "WHERE r.recipe_id='" . self::$database->escape_string($recipe_id) . "' ";
        $sql .= "AND r.user_id='" . self::$database->escape_string($user_id) . "' ";
        $sql .= "LIMIT 1";
        
        $obj_array = static::find_by_sql($sql);
        if (!empty($obj_array)) {
            return array_shift($obj_array);
        } else {
            return false;
        }
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
        
        return static::find_by_sql($sql);
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

/**
 * Comment class for handling recipe comments
 */
class RecipeComment extends DatabaseObject {
    /** @var string Database table name for comments */
    static protected $table_name = "recipe_comment";
    
    /** @var string Primary key field name */
    static protected $primary_key = "comment_id";
    
    /** @var array List of database columns */
    static protected $db_columns = ['comment_id', 'recipe_id', 'user_id', 'comment_text', 'created_at'];

    /** @var int Unique identifier for the comment */
    public $comment_id;
    
    /** @var int Recipe ID this comment belongs to */
    public $recipe_id;
    
    /** @var int User ID who wrote the comment */
    public $user_id;
    
    /** @var string Comment text */
    public $comment_text;
    
    /** @var string Comment creation timestamp */
    public $created_at;

    /**
     * Constructor for Comment class
     * @param array $args Associative array of property values
     */
    public function __construct($args=[]) {
        $this->comment_id = $args['comment_id'] ?? '';
        $this->recipe_id = $args['recipe_id'] ?? '';
        $this->user_id = $args['user_id'] ?? '';
        $this->comment_text = $args['comment_text'] ?? '';
        $this->created_at = $args['created_at'] ?? null;
    }

    /**
     * Saves the comment to recipe_comment table
     * @return bool True if comment was saved successfully
     */
    public function save() {
        // Debug logging
        error_log("Saving comment with data: " . print_r([
            'recipe_id' => $this->recipe_id,
            'user_id' => $this->user_id,
            'comment_text' => $this->comment_text
        ], true));
        
        // Save the comment
        $result = parent::save();
        
        // Debug logging
        error_log("Comment save result: " . ($result ? "true" : "false"));
        if (!$result) {
            error_log("Comment save error: " . self::$database->error);
            return false;
        }
        
        return $result;
    }

    /**
     * Finds all comments for a specific recipe
     * @param int $recipe_id The ID of the recipe
     * @return array Array of Comment objects
     */
    public static function find_by_recipe_id($recipe_id) {
        $sql = "SELECT * FROM " . static::$table_name . " ";
        $sql .= "WHERE recipe_id='" . self::$database->escape_string($recipe_id) . "' ";
        
        // Debug logging
        error_log("Find comments SQL: " . $sql);
        
        $result = static::find_by_sql($sql);
        
        // Debug the results
        error_log("Comments found for recipe " . $recipe_id . ": " . print_r($result, true));
        
        return $result;
    }

    /**
     * Gets the user who wrote this comment
     * @return User|false User object or false if not found
     */
    public function user() {
        if($this->user_id) {
            return User::find_by_id($this->user_id);
        }
        return false;
    }
}

/**
 * Review class for handling recipe ratings and comments
 */
class RecipeReviewHandler {
    /**
     * Saves the review and its associated comment
     * @param RecipeReview $review The review to save
     * @param RecipeComment $comment The comment to save
     * @return bool True if both rating and comment were saved successfully
     */
    public static function save_review_and_comment(RecipeReview $review, RecipeComment $comment) {
        // Save the review
        $review_saved = $review->save();
        
        // If review saved and we have a comment, save the comment
        if($review_saved && !empty(trim($comment->comment_text ?? ''))) {
            $comment_saved = $comment->save();
            return $comment_saved;
        }
        
        return $review_saved;
    }

    /**
     * Finds all reviews and comments for a specific recipe
     * @param int $recipe_id The ID of the recipe
     * @return array Array of Review objects with comments
     */
    public static function find_reviews_and_comments_by_recipe_id($recipe_id) {
        $reviews = RecipeReview::find_by_recipe_id($recipe_id);
        $comments = RecipeComment::find_by_recipe_id($recipe_id);
        
        $result = [];
        foreach ($reviews as $review) {
            $review->comment_text = '';
            $review->comment_created_at = null;
            foreach ($comments as $comment) {
                if ($review->recipe_id == $comment->recipe_id && $review->user_id == $comment->user_id) {
                    $review->comment_text = $comment->comment_text;
                    $review->comment_created_at = $comment->created_at;
                    break;
                }
            }
            $result[] = $review;
        }
        
        return $result;
    }
}
?>
