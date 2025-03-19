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
                                  'img_file_path', 'alt_text', 'is_featured', 'created_at', 'updated_at'];
    /** @var string Primary key column */
    static protected $primary_key = 'recipe_id';

    /**
     * Gets the table name for Recipe class
     * @return string Table name
     */
    public static function table_name() {
        return static::$table_name;
    }

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
    /** @var string Timestamp when recipe was last updated */
    public $updated_at;
    /** @var bool Whether the recipe is favorited by the current user */
    public $is_favorited = false;
    /** @var array Array of ingredients for this recipe */
    public $ingredients = [];
    /** @var array Array of instructions for this recipe */
    public $instructions = [];

    /**
     * Get prep time hours
     * @return int Hours part of prep time
     */
    public function get_prep_hours() {
        return floor($this->prep_time / 3600);
    }

    /**
     * Get prep time minutes
     * @return int Minutes part of prep time
     */
    public function get_prep_minutes() {
        return floor(($this->prep_time % 3600) / 60);
    }

    /**
     * Get cook time hours
     * @return int Hours part of cook time
     */
    public function get_cook_hours() {
        return floor($this->cook_time / 3600);
    }

    /**
     * Get cook time minutes
     * @return int Minutes part of cook time
     */
    public function get_cook_minutes() {
        return floor(($this->cook_time % 3600) / 60);
    }

    /**
     * Getter for time properties
     */
    public function __get($name) {
        switch($name) {
            case 'prep_hours':
                return $this->get_prep_hours();
            case 'prep_minutes':
                return $this->get_prep_minutes();
            case 'cook_hours':
                return $this->get_cook_hours();
            case 'cook_minutes':
                return $this->get_cook_minutes();
            default:
                return null;
        }
    }

    /**
     * Constructor for Recipe class
     * @param array $args Associative array of property values
     */
    public function __construct($args=[]) {
        // Explicitly set recipe_id to null for new recipes
        $this->recipe_id = $args['recipe_id'] ?? null;
        
        $this->title = $args['title'] ?? '';
        $this->description = $args['description'] ?? '';
        $this->user_id = $args['user_id'] ?? '';
        $this->style_id = $args['style_id'] ?? '';
        $this->diet_id = $args['diet_id'] ?? '';
        $this->type_id = $args['type_id'] ?? '';
        $this->video_url = $args['video_url'] ?? '';
        $this->img_file_path = $args['img_file_path'] ?? '';
        $this->alt_text = $args['alt_text'] ?? '';
        $this->is_featured = $args['is_featured'] ?? false; // Allow boolean value
        $this->created_at = $args['created_at'] ?? date('Y-m-d H:i:s');
        $this->updated_at = $args['updated_at'] ?? date('Y-m-d H:i:s');

        // Convert hours and minutes to seconds for prep_time
        if (isset($args['prep_hours']) || isset($args['prep_minutes'])) {
            $hours = intval($args['prep_hours'] ?? 0);
            $minutes = intval($args['prep_minutes'] ?? 0);
            $this->prep_time = ($hours * 3600) + ($minutes * 60);
        } else {
            $this->prep_time = isset($args['prep_time']) ? intval($args['prep_time']) : 0;
        }

        // Convert hours and minutes to seconds for cook_time
        if (isset($args['cook_hours']) || isset($args['cook_minutes'])) {
            $hours = intval($args['cook_hours'] ?? 0);
            $minutes = intval($args['cook_minutes'] ?? 0);
            $this->cook_time = ($hours * 3600) + ($minutes * 60);
        } else {
            $this->cook_time = isset($args['cook_time']) ? intval($args['cook_time']) : 0;
        }
        
        // Initialize ingredients and instructions if provided
        $this->ingredients = $args['ingredients'] ?? [];
        $this->instructions = $args['instructions'] ?? [];
    }

    /**
     * Updates an existing record in the database
     * @return bool True if update was successful
     */
    protected function update() {
        $this->validate();
        if(!empty($this->errors)) { return false; }

        // Set the updated_at timestamp
        $this->updated_at = date('Y-m-d H:i:s');

        // If image is being updated, delete the old image file
        if (array_key_exists('img_file_path', $this->attributes())) {
            $old_recipe = self::find_by_id($this->recipe_id);
            if ($old_recipe && $old_recipe->img_file_path && $old_recipe->img_file_path !== $this->img_file_path) {
                $old_image_path = PUBLIC_PATH . '/assets/uploads/recipes/' . $old_recipe->img_file_path;
                if (file_exists($old_image_path)) {
                    unlink($old_image_path);
                }
            }
        }
        
        $attributes = $this->attributes();
        $attribute_pairs = [];
        
        foreach($attributes as $key => $value) {
            // Handle NULL values properly
            if($value === '' || $value === null) {
                // Skip img_file_path if it's empty to keep the existing value
                if($key === 'img_file_path') {
                    continue; // Skip this attribute
                }
                if (in_array($key, ['style_id', 'diet_id', 'type_id', 'prep_time', 'cook_time'])) {
                    $attribute_pairs[] = "{$key}=NULL";
                } else if ($key === 'updated_at') {
                    $attribute_pairs[] = "{$key}='" . date('Y-m-d H:i:s') . "'";
                } else {
                    $attribute_pairs[] = "{$key}=''"; // Set to empty string instead of NULL
                }
            } else {
                $attribute_pairs[] = "{$key}='" . db_escape(static::get_database(), $value) . "'";
            }
        }
        
        $sql = "UPDATE " . static::$table_name . " SET ";
        $sql .= join(', ', $attribute_pairs);
        $sql .= " WHERE recipe_id='" . db_escape(static::get_database(), $this->recipe_id) . "'";
        $sql .= " LIMIT 1";
        
        $result = mysqli_query(static::get_database(), $sql);
        if(!$result) {
            $this->errors[] = "Update failed: " . mysqli_error(static::get_database());
        }
        return $result;
    }

    /**
     * Saves the recipe to the database (creates or updates)
     * @return bool True if save was successful
     */
    public function save() {
        // Store ingredients and instructions temporarily
        $temp_ingredients = $this->ingredients;
        $temp_instructions = $this->instructions;
        
        // Call parent save method first to ensure we have a recipe_id
        $result = parent::save();
        
        if ($result) {
            // Process image if it exists
            if (!empty($this->img_file_path) && file_exists(PUBLIC_PATH . '/assets/uploads/recipes/' . $this->img_file_path)) {
                $this->process_image();
            }
            
            // Restore ingredients and instructions
            $this->ingredients = $temp_ingredients;
            $this->instructions = $temp_instructions;
            
            // Save ingredients and instructions
            $ingredients_result = $this->save_ingredients();
            $instructions_result = $this->save_instructions();
            
            return $ingredients_result && $instructions_result;
        }
        
        return $result;
    }

    /**
     * Validates the recipe's attributes
     * @return array Array of validation errors
     */
    protected function validate() {
        $this->errors = [];
        
        if(is_blank($this->title)) {
            $this->errors[] = "Title cannot be blank.";
        }
        
        if(is_blank($this->description)) {
            $this->errors[] = "Description cannot be blank.";
        }
        
        if(is_blank($this->user_id)) {
            $this->errors[] = "User ID cannot be blank.";
        }
        
        // Validate style_id, diet_id, and type_id if they are set
        if(!empty($this->style_id) && !RecipeAttribute::find_one($this->style_id, 'style')) {
            $this->errors[] = "Invalid style selected.";
        }
        
        if(!empty($this->diet_id) && !RecipeAttribute::find_one($this->diet_id, 'diet')) {
            $this->errors[] = "Invalid diet selected.";
        }
        
        if(!empty($this->type_id) && !RecipeAttribute::find_one($this->type_id, 'type')) {
            $this->errors[] = "Invalid type selected.";
        }
        
        // Validate prep_time and cook_time are numeric
        if(!empty($this->prep_time) && !is_numeric($this->prep_time)) {
            $this->errors[] = "Prep time must be a number.";
        }
        
        if(!empty($this->cook_time) && !is_numeric($this->cook_time)) {
            $this->errors[] = "Cook time must be a number.";
        }
        
        // Validate video_url is a valid URL if provided
        if(!empty($this->video_url) && !is_valid_url($this->video_url)) {
            $this->errors[] = "Video URL must be a valid URL.";
        }
        
        // Validate alt_text is provided if img_file_path is set
        if(!empty($this->img_file_path) && is_blank($this->alt_text)) {
            $this->errors[] = "Alt text is required for the image.";
        }
        
        return $this->errors;
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
     * @param string $size The size of the image to return ('thumb', 'optimized', 'banner')
     * @return string Relative image path for use with url_for function
     */
    public function get_image_path($size = 'optimized') {
        if (!$this->img_file_path) {
            return '';
        }
        
        $path_parts = pathinfo($this->img_file_path);
        $filename = $path_parts['filename'];
        $extension = 'webp'; // Always use WebP for processed images
        
        // For processed images (thumb, optimized, banner), we use the filename without extension
        // and append the size suffix and .webp extension
        $base_path = '/assets/uploads/recipes/';
        $file_path = '';
        
        switch($size) {
            case 'thumb':
                $file_path = $base_path . $filename . '_thumb.' . $extension;
                break;
            case 'banner':
                $file_path = $base_path . $filename . '_banner.' . $extension;
                break;
            case 'original': // Original now redirects to optimized since we don't keep originals
            case 'optimized':
            default:
                $file_path = $base_path . $filename . '_optimized.' . $extension;
                break;
        }
        
        // Check if the file actually exists
        if (file_exists(PUBLIC_PATH . $file_path)) {
            return $file_path;
        }
        
        // If the requested size doesn't exist, try to return the optimized version as a fallback
        if ($size !== 'optimized') {
            $fallback_path = $base_path . $filename . '_optimized.' . $extension;
            if (file_exists(PUBLIC_PATH . $fallback_path)) {
                return $fallback_path;
            }
        }
        
        // If we still don't have a valid file, return empty string
        return '';
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
        // Delegate to RecipeFilter
        $filter_config = [
            'search' => $search,
            'style_id' => $style_id,
            'diet_id' => $diet_id,
            'type_id' => $type_id
        ];
        
        $filter = new RecipeFilter($filter_config);
        return $filter->count();
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
        // Delegate to RecipeFilter
        $filter_config = [
            'search' => $search,
            'style_id' => $style_id,
            'diet_id' => $diet_id,
            'type_id' => $type_id,
            'sort' => $sort,
            'limit' => $limit,
            'offset' => $offset
        ];
        
        $filter = new RecipeFilter($filter_config);
        return $filter->apply();
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
     * Public wrapper for instantiate_result to be used by RecipeFilter
     * @param mysqli_result $result Database result set
     * @return array Array of Recipe objects
     */
    public static function create_objects_from_result($result) {
        return static::instantiate_result($result);
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

    /**
     * Gets all recipes favorited by a specific user
     * @param int $user_id The ID of the user
     * @return array Array of Recipe objects
     */
    static public function find_favorites_by_user_id($user_id) {
        $database = static::get_database();
        $sql = "SELECT r.* FROM " . static::$table_name . " r ";
        $sql .= "JOIN user_favorite f ON r.recipe_id = f.recipe_id ";
        $sql .= "WHERE f.user_id = ? ";
        $sql .= "ORDER BY f.created_at DESC";
        
        $stmt = $database->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return static::instantiate_result($result);
    }

    /**
     * Checks if this recipe is favorited by a user
     * @param int $user_id The user ID to check
     * @return bool True if favorited
     */
    public function is_favorited_by($user_id) {
        require_once('RecipeFavorite.class.php');
        return RecipeFavorite::is_favorited($user_id, $this->recipe_id);
    }

    /**
     * Check if recipe is favorited by a specific user
     * @param int $user_id User ID to check
     * @return bool True if favorited by user
     */
    public function is_favorited_by_user($user_id) {
        if (!$user_id) return false;
        return RecipeFavorite::is_favorited($user_id, $this->recipe_id);
    }

    /**
     * Add this recipe to a user's favorites
     * @param int $user_id The user ID
     * @return bool True if successful
     */
    public function add_to_favorites($user_id) {
        require_once('RecipeFavorite.class.php');
        return RecipeFavorite::add_favorite($user_id, $this->recipe_id);
    }

    /**
     * Remove this recipe from a user's favorites
     * @param int $user_id The user ID
     * @return bool True if successful
     */
    public function remove_from_favorites($user_id) {
        require_once('RecipeFavorite.class.php');
        return RecipeFavorite::remove_favorite($user_id, $this->recipe_id);
    }

    /**
     * Count recipes by style
     * @param int $style_id The style ID to count
     * @return int Number of recipes with this style
     */
    public static function count_by_style($style_id) {
        $sql = "SELECT COUNT(*) FROM recipe WHERE style_id = ?";
        $stmt = self::$database->prepare($sql);
        $stmt->bind_param("i", $style_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $count = $result->fetch_array()[0];
        return $count;
    }

    /**
     * Count recipes by diet
     * @param int $diet_id The diet ID to count
     * @return int Number of recipes with this diet
     */
    public static function count_by_diet($diet_id) {
        $sql = "SELECT COUNT(*) FROM recipe WHERE diet_id = ?";
        $stmt = self::$database->prepare($sql);
        $stmt->bind_param("i", $diet_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $count = $result->fetch_array()[0];
        return $count;
    }

    /**
     * Count recipes by type
     * @param int $type_id The type ID to count
     * @return int Number of recipes with this type
     */
    public static function count_by_type($type_id) {
        $sql = "SELECT COUNT(*) FROM recipe WHERE type_id = ?";
        $stmt = self::$database->prepare($sql);
        $stmt->bind_param("i", $type_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $count = $result->fetch_array()[0];
        return $count;
    }

    /**
     * Save ingredients for this recipe
     * @return bool True if successful
     */
    protected function save_ingredients() {
        // First delete all existing ingredients
        $sql = "DELETE FROM recipe_ingredient WHERE recipe_id = ?";
        $stmt = self::$database->prepare($sql);
        
        if(!$stmt) {
            $this->errors[] = "Failed to prepare delete statement: " . self::$database->error;
            error_log("Failed to prepare delete statement: " . self::$database->error);
            return false;
        }
        
        $stmt->bind_param("i", $this->recipe_id);
        $stmt->execute();
        $stmt->close();
        
        // Debug: Log the number of ingredients
        error_log("Saving " . count($this->ingredients ?? []) . " ingredients for recipe ID: " . $this->recipe_id);
        
        // Then add all recipe ingredients
        if(!empty($this->ingredients)) {
            // Prepare the insert statement once
            $sql = "INSERT INTO recipe_ingredient (recipe_id, ingredient_id, quantity, measurement_id) VALUES (?, ?, ?, ?)";
            $stmt = self::$database->prepare($sql);
            
            if(!$stmt) {
                $this->errors[] = "Failed to prepare insert statement: " . self::$database->error;
                error_log("Failed to prepare insert statement: " . self::$database->error);
                return false;
            }
            
            foreach($this->ingredients as $i => $ingredient) {
                // Skip empty ingredients
                if(empty($ingredient['name'])) {
                    error_log("Skipping empty ingredient at index " . $i);
                    continue;
                }
                
                // Debug: Log the ingredient being processed
                error_log("Processing ingredient: " . $ingredient['name'] . ", Quantity: " . ($ingredient['quantity'] ?? 'none') . ", Measurement ID: " . ($ingredient['measurement_id'] ?? 'none'));
                
                // Check if ingredient exists or create it
                $ingredient_id = $this->get_or_create_ingredient($ingredient['name']);
                
                // Debug: Log the ingredient ID
                error_log("Ingredient ID: " . $ingredient_id);
                
                // Handle measurement_id - convert empty values to NULL
                $measurement_id = !empty($ingredient['measurement_id']) ? $ingredient['measurement_id'] : null;
                $quantity = $ingredient['quantity'] ?? '';
                
                // Bind parameters and execute
                $stmt->bind_param("iisi", $this->recipe_id, $ingredient_id, $quantity, $measurement_id);
                $result = $stmt->execute();
                
                if(!$result) {
                    $this->errors[] = "Failed to save ingredient: " . $stmt->error;
                    error_log("Failed to save ingredient: " . $stmt->error . " for ingredient: " . $ingredient['name']);
                }
            }
            
            $stmt->close();
            return empty($this->errors);
        } else {
            error_log("No ingredients to save for recipe ID: " . $this->recipe_id);
            return true; // No ingredients to save is not an error
        }
    }
    
    /**
     * Get or create an ingredient
     * @param string $name Ingredient name
     * @return int Ingredient ID
     */
    private function get_or_create_ingredient($name) {
        // Check if ingredient exists
        $sql = "SELECT ingredient_id FROM ingredient WHERE name='" . self::$database->escape_string($name) . "' LIMIT 1";
        $result = self::$database->query($sql);
        
        if($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['ingredient_id'];
        }
        
        // Create new ingredient - use prepared statement to avoid SQL injection
        $sql = "INSERT INTO ingredient (name) VALUES (?)";
        $stmt = self::$database->prepare($sql);
        
        if(!$stmt) {
            $this->errors[] = "Failed to prepare statement: " . self::$database->error;
            error_log("Failed to prepare statement: " . self::$database->error);
            return 1; // Return a default value if prepare fails
        }
        
        $stmt->bind_param("s", $name);
        $result = $stmt->execute();
        
        if($result) {
            $ingredient_id = $stmt->insert_id;
            $stmt->close();
            return $ingredient_id;
        } else {
            $this->errors[] = "Failed to create ingredient: " . $stmt->error;
            error_log("Failed to create ingredient: " . $stmt->error);
            $stmt->close();
            // Return a default value if insert fails
            return 1;
        }
    }
    
    /**
     * Save instructions for this recipe
     * @return bool True if successful
     */
    protected function save_instructions() {
        // First, delete any existing recipe instructions
        $sql = "DELETE FROM recipe_step WHERE recipe_id='" . self::$database->escape_string($this->recipe_id) . "'";
        self::$database->query($sql);
        
        // Then add all recipe instructions
        if(!empty($this->instructions)) {
            foreach($this->instructions as $i => $instruction) {
                // Skip empty instructions
                if(empty($instruction['instruction'])) {
                    continue;
                }
                
                // Check if step_number is provided, otherwise use the index + 1
                $step_number = isset($instruction['step_number']) ? $instruction['step_number'] : $i + 1;
                
                $sql = "INSERT INTO recipe_step (";
                $sql .= "recipe_id, instruction, step_number";
                $sql .= ") VALUES (";
                $sql .= "'" . self::$database->escape_string($this->recipe_id) . "', ";
                $sql .= "'" . self::$database->escape_string($instruction['instruction']) . "', ";
                $sql .= "'" . self::$database->escape_string($step_number) . "'";
                $sql .= ")";
                
                $result = self::$database->query($sql);
                
                if(!$result) {
                    $this->errors[] = "Failed to save instruction: " . self::$database->error;
                    return false;
                }
            }
            return true;
        } else {
            return true; // No instructions to save is not an error
        }
    }

    /**
     * Deletes the recipe and its associated image files from the server
     * @return bool True if deletion was successful
     */
    public function delete() {
        // Delete associated files first
        if(!empty($this->img_file_path)) {
            $file_info = pathinfo($this->img_file_path);
            $filename = $file_info['filename'];
            $extension = $file_info['extension'];
            
            // Define paths for all versions of the image
            $original_path = PUBLIC_PATH . '/assets/uploads/recipes/' . $this->img_file_path;
            $thumb_path = PUBLIC_PATH . '/assets/uploads/recipes/' . $filename . '_thumb.' . $extension;
            $optimized_path = PUBLIC_PATH . '/assets/uploads/recipes/' . $filename . '_optimized.' . $extension;
            $banner_path = PUBLIC_PATH . '/assets/uploads/recipes/' . $filename . '_banner.' . $extension;
            
            // Delete all image files if they exist
            if(file_exists($original_path)) {
                unlink($original_path);
            }
            
            if(file_exists($thumb_path)) {
                unlink($thumb_path);
            }
            
            if(file_exists($optimized_path)) {
                unlink($optimized_path);
            }
            
            if(file_exists($banner_path)) {
                unlink($banner_path);
            }
        }
        
        // Delete recipe from database
        $sql = "DELETE FROM " . static::$table_name . " ";
        $sql .= "WHERE recipe_id='" . self::$database->escape_string($this->recipe_id) . "' ";
        $sql .= "LIMIT 1";
        
        $result = self::$database->query($sql);
        return $result;
    }

    /**
     * Process the recipe image to create thumbnail, optimized, and banner versions
     * 
     * @return bool True if processing was successful, false otherwise
     */
    protected function process_image() {
        if (empty($this->img_file_path)) {
            return false;
        }
        
        // Create image processor
        $processor = new RecipeImageProcessor();
        
        // Define paths
        $source_path = PUBLIC_PATH . '/assets/uploads/recipes/' . $this->img_file_path;
        $destination_dir = PUBLIC_PATH . '/assets/uploads/recipes';
        
        // Check if source file exists
        if (!file_exists($source_path)) {
            $this->errors[] = "Source image file not found: {$source_path}";
            return false;
        }
        
        // Get filename without extension for processing
        $path_parts = pathinfo($this->img_file_path);
        $filename = $path_parts['filename'];
        
        // Process the image using the processor
        $result = $processor->processRecipeImage($source_path, $destination_dir, $filename);
        
        // Log any errors but continue
        if ($result === false) {
            $errors = $processor->getErrors();
            if (is_array($errors) && !empty($errors)) {
                $this->errors[] = "Image processing failed: " . implode(", ", $errors);
            } else {
                $this->errors[] = "Image processing failed with unknown error";
            }
            // Even if processing fails, we continue - the original image will be used
        }
        
        return true;
    }
}
?>