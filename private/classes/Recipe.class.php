<?php

/**
 * Recipe class for managing recipe data and operations
 * Extends DatabaseObject for database operations
 */
class Recipe extends DatabaseObject {
    /** @var string Database table name */
    static protected $table_name = 'recipe';
    /** @var array Database columns */
    static protected $db_columns = ['recipe_id', 'user_id', 'title', 'description', 'style_id', 
                                  'diet_id', 'type_id', 'prep_time', 'cook_time', 'video_url', 
                                  'img_file_path', 'alt_text', 'is_featured', 'created_at'];
    /** @var string Primary key column */
    static protected $primary_key = 'recipe_id';

    /** @var int Unique identifier for the recipe */
    public $recipe_id;
    /** @var int ID of user who created the recipe */
    public $user_id;
    /** @var string Recipe title */
    public $title;
    /** @var string Recipe description */
    public $description;
    /** @var int Style ID */
    public $style_id;
    /** @var int Diet ID */
    public $diet_id;
    /** @var int Type ID */
    public $type_id;
    /** @var int Preparation time in seconds */
    public $prep_time;
    /** @var int Cooking time in seconds */
    public $cook_time;
    /** @var string Video URL */
    public $video_url;
    /** @var string Image file path */
    public $img_file_path;
    /** @var string Alt text for image */
    public $alt_text;
    /** @var bool Whether the recipe is featured */
    public $is_featured;
    /** @var string Timestamp when recipe was created */
    public $created_at;

    /**
     * Constructor for Recipe class
     * @param array $args Associative array of property values
     */
    public function __construct($args=[]) {
        $this->title = $args['title'] ?? '';
        $this->description = $args['description'] ?? '';
        $this->style_id = $args['style_id'] ?? null;
        $this->diet_id = $args['diet_id'] ?? null;
        $this->type_id = $args['type_id'] ?? null;
        $this->prep_time = $args['prep_time'] ?? 0;
        $this->cook_time = $args['cook_time'] ?? 0;
        $this->video_url = $args['video_url'] ?? '';
        $this->img_file_path = $args['img_file_path'] ?? '';
        $this->alt_text = $args['alt_text'] ?? '';
        $this->is_featured = $args['is_featured'] ?? false;
        $this->created_at = $args['created_at'] ?? date('Y-m-d H:i:s');
    }

    /**
     * Gets the style attribute for this recipe
     * @return RecipeAttribute|null RecipeAttribute object or null if not found
     */
    public function style() {
        if($this->style_id) {
            return RecipeAttribute::find_one($this->style_id, 'style');
        }
        return null;
    }

    /**
     * Gets the diet attribute for this recipe
     * @return RecipeAttribute|null RecipeAttribute object or null if not found
     */
    public function diet() {
        if($this->diet_id) {
            return RecipeAttribute::find_one($this->diet_id, 'diet');
        }
        return null;
    }

    /**
     * Gets the type attribute for this recipe
     * @return RecipeAttribute|null RecipeAttribute object or null if not found
     */
    public function type() {
        if($this->type_id) {
            return RecipeAttribute::find_one($this->type_id, 'type');
        }
        return null;
    }

    /**
     * Gets the user who created this recipe
     * @return User|null User object or null if not found
     */
    public function user() {
        if($this->user_id) {
            return User::find_by_id($this->user_id);
        }
        return null;
    }

    /**
     * Gets all ingredients for this recipe
     * @return array Array of RecipeIngredient objects
     */
    public function ingredients() {
        return RecipeIngredient::find_by_recipe_id($this->recipe_id);
    }

    /**
     * Gets all steps for this recipe
     * @return array Array of RecipeStep objects
     */
    public function steps() {
        return RecipeStep::find_by_recipe_id($this->recipe_id);
    }

    /**
     * Find featured recipes
     * @param int $limit Optional limit of recipes to return
     * @return array Array of Recipe objects
     */
    public static function find_featured($limit = 4) {
        $sql = "SELECT * FROM " . static::$table_name;
        $sql .= " WHERE is_featured = 1";
        $sql .= " ORDER BY created_at DESC";
        $sql .= " LIMIT " . (int)$limit;
        return static::find_by_sql($sql);
    }

    /**
     * Finds all featured recipes
     * @param int $limit Maximum number of recipes to return
     * @return array Array of Recipe objects
     */
    public static function find_all_featured($limit=3) {
        $sql = "SELECT * FROM " . static::$table_name;
        $sql .= " WHERE is_featured = TRUE";
        $sql .= " ORDER BY created_at DESC";
        $sql .= " LIMIT ?";
        $stmt = self::$database->prepare($sql);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        return static::instantiate_result($result);
    }

    /**
     * Gets the image path for this recipe
     * @return string Full image path or default placeholder path
     */
    public function get_image_path() {
        return $this->img_file_path ? '/assets/uploads/recipes/' . $this->img_file_path : '/assets/images/recipe-placeholder.jpg';
    }

    /**
     * Calculate the average rating for this recipe
     * @return float|null Average rating or null if no ratings
     */
    public function average_rating() {
        $sql = "SELECT AVG(rating_value) as avg_rating FROM recipe_rating ";
        $sql .= "WHERE recipe_id = '" . self::$database->escape_string($this->recipe_id) . "'";
        $result = self::$database->query($sql);
        $row = $result->fetch_assoc();
        return $row['avg_rating'] ? round($row['avg_rating'], 1) : null;
    }

    /**
     * Gets the average rating for this recipe
     * @return float Average rating value between 1 and 5, or 0 if no ratings
     */
    public function get_average_rating() {
        $sql = "SELECT AVG(rating_value) as avg_rating 
                FROM recipe_rating 
                WHERE recipe_id = ?";
        $stmt = self::$database->prepare($sql);
        $stmt->bind_param("i", $this->recipe_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return round($row['avg_rating'] ?? 0, 1);
    }

    /**
     * Gets the total number of ratings for this recipe
     * @return int Number of ratings
     */
    public function rating_count() {
        $sql = "SELECT COUNT(*) as count FROM recipe_rating WHERE recipe_id = ?";
        $stmt = self::$database->prepare($sql);
        $stmt->bind_param("i", $this->recipe_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return (int)$row['count'];
    }

    /**
     * Counts all recipes matching the given filters
     * @param string $search Search query
     * @param int|null $style_id Style ID filter
     * @param int|null $diet_id Diet ID filter
     * @param int|null $type_id Type ID filter
     * @return int Total number of matching recipes
     */
    public static function count_all_filtered($search='', $style_id=null, $diet_id=null, $type_id=null) {
        $sql = "SELECT COUNT(*) as count FROM " . static::$table_name;
        $where_clauses = [];
        $params = [];
        $types = "";
        
        if(!empty($search)) {
            $where_clauses[] = "(title LIKE ? OR description LIKE ?)";
            $search_param = "%{$search}%";
            $params[] = $search_param;
            $params[] = $search_param;
            $types .= "ss";
        }
        
        if(!empty($style_id)) {
            $where_clauses[] = "style_id = ?";
            $params[] = $style_id;
            $types .= "i";
        }
        
        if(!empty($diet_id)) {
            $where_clauses[] = "diet_id = ?";
            $params[] = $diet_id;
            $types .= "i";
        }
        
        if(!empty($type_id)) {
            $where_clauses[] = "type_id = ?";
            $params[] = $type_id;
            $types .= "i";
        }
        
        if(!empty($where_clauses)) {
            $sql .= " WHERE " . implode(" AND ", $where_clauses);
        }

        $stmt = self::$database->prepare($sql);
        if(!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return (int)$row['count'];
    }

    /**
     * Finds recipes matching the given filters
     * @param string $search Search query
     * @param int|null $style_id Style ID filter
     * @param int|null $diet_id Diet ID filter
     * @param int|null $type_id Type ID filter
     * @param string $sort Sort order ('newest', 'oldest', 'rating')
     * @param int $limit Number of recipes per page
     * @param int $offset Offset for pagination
     * @return array Array of Recipe objects
     */
    public static function find_all_filtered($search='', $style_id=null, $diet_id=null, $type_id=null, $sort='newest', $limit=12, $offset=0) {
        $sql = "SELECT r.* FROM " . static::$table_name . " r";
        
        if($sort === 'rating') {
            $sql .= " LEFT JOIN (
                        SELECT recipe_id, AVG(rating_value) as avg_rating 
                        FROM recipe_rating 
                        GROUP BY recipe_id
                    ) ratings ON r.recipe_id = ratings.recipe_id";
        }
        
        $where_clauses = [];
        $params = [];
        $types = "";
        
        if(!empty($search)) {
            $where_clauses[] = "(r.title LIKE ? OR r.description LIKE ?)";
            $search_param = "%{$search}%";
            $params[] = $search_param;
            $params[] = $search_param;
            $types .= "ss";
        }
        
        if(!empty($style_id)) {
            $where_clauses[] = "r.style_id = ?";
            $params[] = $style_id;
            $types .= "i";
        }
        
        if(!empty($diet_id)) {
            $where_clauses[] = "r.diet_id = ?";
            $params[] = $diet_id;
            $types .= "i";
        }
        
        if(!empty($type_id)) {
            $where_clauses[] = "r.type_id = ?";
            $params[] = $type_id;
            $types .= "i";
        }
        
        if(!empty($where_clauses)) {
            $sql .= " WHERE " . implode(" AND ", $where_clauses);
        }
        
        switch($sort) {
            case 'oldest':
                $sql .= " ORDER BY r.created_at ASC";
                break;
            case 'rating':
                $sql .= " ORDER BY avg_rating DESC NULLS LAST, r.created_at DESC";
                break;
            case 'newest':
            default:
                $sql .= " ORDER BY r.created_at DESC";
        }
        
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $types .= "ii";
        
        $stmt = self::$database->prepare($sql);
        if(!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        return static::instantiate_result($result);
    }

    /**
     * Instantiates Recipe objects from a database result
     * @param mysqli_result $result Database result
     * @return array Array of Recipe objects
     */
    protected static function instantiate_result($result) {
        $object_array = [];
        while($record = $result->fetch_assoc()) {
            $object_array[] = parent::instantiate($record);
        }
        return $object_array;
    }

    /**
     * Gets the total time (prep + cook) in a human-readable format
     * @return string Total time in format "X hr Y min"
     */
    public function get_total_time_display() {
        return $this->format_time($this->prep_time + $this->cook_time);
    }

    /**
     * Gets the prep time in a human-readable format
     * @return string Prep time in format "X hr Y min"
     */
    public function get_prep_time_display() {
        return $this->format_time($this->prep_time);
    }

    /**
     * Gets the cook time in a human-readable format
     * @return string Cook time in format "X hr Y min"
     */
    public function get_cook_time_display() {
        return $this->format_time($this->cook_time);
    }

    /**
     * Formats time in seconds to a human-readable string
     * @param int $seconds Time in seconds
     * @return string Formatted time string (e.g., "2 hr 30 min" or "45 min")
     */
    private function format_time($seconds) {
        if ($seconds < 60) {
            return "1 min"; // Round up to 1 minute if less than 60 seconds
        }

        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);

        if ($hours > 0) {
            return $hours . " hr" . ($hours > 1 ? "s" : "") . 
                   ($minutes > 0 ? " " . $minutes . " min" : "");
        } else {
            return $minutes . " min";
        }
    }

    /**
     * Converts hours and minutes to seconds
     * @param int $hours Number of hours
     * @param int $minutes Number of minutes
     * @return int Total seconds
     */
    public static function time_to_seconds($hours, $minutes) {
        return ($hours * 3600) + ($minutes * 60);
    }

    /**
     * Converts seconds to hours and minutes
     * @param int $seconds Number of seconds
     * @return array Associative array with 'hours' and 'minutes' keys
     */
    public static function seconds_to_time($seconds) {
        return [
            'hours' => floor($seconds / 3600),
            'minutes' => floor(($seconds % 3600) / 60)
        ];
    }

    /**
     * Find all recipes created by a specific user
     * @param int $user_id The ID of the user
     * @return array Array of Recipe objects
     */
    public static function find_by_user_id($user_id) {
        $sql = "SELECT * FROM " . static::$table_name;
        $sql .= " WHERE user_id = ?";
        $sql .= " ORDER BY created_at DESC";
        
        $stmt = self::$database->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return static::instantiate_result($result);
    }
}
?>