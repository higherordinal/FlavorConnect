<?php

/**
 * RecipeFilter class for handling recipe filtering and search operations
 * Supports both server-side filtering and client-side filtering via API
 * 
 * @author Henry Vaughn
 */
class RecipeFilter extends DatabaseObject {
    /** @var string Search query */
    private $search;
    /** @var int|null Style ID filter */
    private $style_id;
    /** @var int|null Diet ID filter */
    private $diet_id;
    /** @var int|null Type ID filter */
    private $type_id;
    /** @var string Sort order */
    private $sort;
    /** @var int Number of recipes per page */
    private $limit;
    /** @var int Offset for pagination */
    private $offset;
    /** @var bool Whether to include user favorite status */
    private $include_favorites;
    /** @var int|null User ID for favorite status */
    private $user_id;
    /** @var array Error messages */
    public $errors = [];

    /**
     * Constructor for RecipeFilter class
     * @param array $filters Associative array of filter values
     */
    public function __construct($filters = []) {
        $this->search = $filters['search'] ?? '';
        $this->style_id = !empty($filters['style_id']) ? (int)$filters['style_id'] : null;
        $this->diet_id = !empty($filters['diet_id']) ? (int)$filters['diet_id'] : null;
        $this->type_id = !empty($filters['type_id']) ? (int)$filters['type_id'] : null;
        $this->sort = $filters['sort'] ?? 'newest';
        $this->limit = isset($filters['limit']) ? (int)$filters['limit'] : 12;
        $this->offset = isset($filters['offset']) ? (int)$filters['offset'] : 0;
        $this->include_favorites = $filters['include_favorites'] ?? false;
        $this->user_id = !empty($filters['user_id']) ? (int)$filters['user_id'] : null;
    }

    /**
     * Builds WHERE clause and parameters for the filter
     * @return array Array with 'where', 'params', and 'types' keys
     */
    private function build_where_clause() {
        $where_clauses = [];
        $params = [];
        $types = "";
        
        if(!empty($this->search)) {
            $where_clauses[] = "(r.title LIKE ? OR r.description LIKE ?)";
            $search_param = "%{$this->search}%";
            $params[] = $search_param;
            $params[] = $search_param;
            $types .= "ss";
        }
        
        if(!empty($this->style_id)) {
            $where_clauses[] = "r.style_id = ?";
            $params[] = $this->style_id;
            $types .= "i";
        }
        
        if(!empty($this->diet_id)) {
            $where_clauses[] = "r.diet_id = ?";
            $params[] = $this->diet_id;
            $types .= "i";
        }
        
        if(!empty($this->type_id)) {
            $where_clauses[] = "r.type_id = ?";
            $params[] = $this->type_id;
            $types .= "i";
        }

        return [
            'where' => !empty($where_clauses) ? " WHERE " . implode(" AND ", $where_clauses) : "",
            'params' => $params,
            'types' => $types
        ];
    }

    /**
     * Gets the ORDER BY clause based on sort parameter
     * @return string ORDER BY clause
     */
    private function get_order_clause() {
        switch($this->sort) {
            case 'oldest':
                return " ORDER BY r.created_at ASC";
            case 'rating':
                return " ORDER BY COALESCE(ratings.avg_rating, 0) DESC, r.created_at DESC";
            case 'name_asc':
                return " ORDER BY r.title ASC";
            case 'name_desc':
                return " ORDER BY r.title DESC";
            case 'newest':
            default:
                return " ORDER BY r.created_at DESC";
        }
    }

    /**
     * Applies the filter to get matching recipes
     * @return array Array of Recipe objects
     */
    public function apply() {
        try {
            $sql = "SELECT r.* FROM " . Recipe::table_name() . " r";
            
            // Always add the ratings join if we're sorting by rating
            // This ensures it's available regardless of filtering
            if($this->sort === 'rating') {
                $sql .= " LEFT JOIN (
                            SELECT recipe_id, AVG(rating_value) as avg_rating 
                            FROM recipe_rating 
                            GROUP BY recipe_id
                        ) ratings ON r.recipe_id = ratings.recipe_id";
            }
            
            $where_data = $this->build_where_clause();
            $sql .= $where_data['where'];
            $sql .= $this->get_order_clause();
            $sql .= " LIMIT ? OFFSET ?";
            
            $database = self::get_database();
            $stmt = $database->prepare($sql);
            
            if(!empty($where_data['params'])) {
                $params = array_merge($where_data['params'], [$this->limit, $this->offset]);
                $types = $where_data['types'] . "ii";
                $stmt->bind_param($types, ...$params);
            } else {
                $stmt->bind_param("ii", $this->limit, $this->offset);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            $recipes = Recipe::create_objects_from_result($result);
            
            // Add favorite status if requested
            if($this->include_favorites && !empty($this->user_id)) {
                $this->add_favorite_status($recipes);
            }
            
            return $recipes;
        } catch (Exception $e) {
            $this->errors[] = "Database error: " . $e->getMessage();
            return [];
        }
    }

    /**
     * Add favorite status to recipes
     * @param array $recipes Array of Recipe objects
     */
    private function add_favorite_status(&$recipes) {
        if(empty($recipes) || empty($this->user_id)) {
            return;
        }
        
        try {
            // Get all favorited recipe IDs for this user
            $recipe_ids = array_map(function($recipe) {
                return $recipe->recipe_id;
            }, $recipes);
            
            if(empty($recipe_ids)) {
                return;
            }
            
            $placeholders = implode(',', array_fill(0, count($recipe_ids), '?'));
            $sql = "SELECT recipe_id FROM user_favorite WHERE user_id = ? AND recipe_id IN ($placeholders)";
            
            $database = self::get_database();
            $stmt = $database->prepare($sql);
            
            $types = "i" . str_repeat("i", count($recipe_ids));
            $params = array_merge([$this->user_id], $recipe_ids);
            $stmt->bind_param($types, ...$params);
            
            $stmt->execute();
            $result = $stmt->get_result();
            
            $favorited_ids = [];
            while($row = $result->fetch_assoc()) {
                $favorited_ids[] = $row['recipe_id'];
            }
            
            // Set is_favorited flag on recipes
            foreach($recipes as &$recipe) {
                $recipe->is_favorited = in_array($recipe->recipe_id, $favorited_ids);
            }
        } catch (Exception $e) {
            // Just log the error but continue
            error_log("Error adding favorite status: " . $e->getMessage());
        }
    }

    /**
     * Counts total recipes matching the filter
     * @return int Total number of matching recipes
     */
    public function count() {
        try {
            $sql = "SELECT COUNT(*) as count FROM " . Recipe::table_name() . " r";
            $where_data = $this->build_where_clause();
            $sql .= $where_data['where'];
            
            $database = self::get_database();
            $stmt = $database->prepare($sql);
            if(!empty($where_data['params'])) {
                $stmt->bind_param($where_data['types'], ...$where_data['params']);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            return (int)$row['count'];
        } catch (Exception $e) {
            $this->errors[] = "Database error: " . $e->getMessage();
            return 0;
        }
    }

    /**
     * Get recipes as JSON for client-side filtering
     * @return string JSON string of recipes
     */
    public function get_recipes_json() {
        $recipes = $this->apply();
        $recipes_data = [];
        
        foreach($recipes as $recipe) {
            $style = $recipe->style();
            $diet = $recipe->diet();
            $type = $recipe->type();
            $user = User::find_by_id($recipe->user_id);
            $rating = $recipe->get_average_rating();
            
            $recipes_data[] = [
                'recipe_id' => $recipe->recipe_id,
                'user_id' => $recipe->user_id,
                'username' => $user ? $user->username : 'Unknown',
                'title' => $recipe->title,
                'description' => $recipe->description,
                'img_file_path' => $recipe->img_file_path,
                'created_at' => $recipe->created_at,
                'style_id' => $recipe->style_id,
                'diet_id' => $recipe->diet_id,
                'type_id' => $recipe->type_id,
                'style' => $style ? $style->name : '',
                'diet' => $diet ? $diet->name : '',
                'type' => $type ? $type->name : '',
                'rating' => $rating['average'] ?? 0,
                'rating_count' => $rating['count'] ?? 0,
                'is_favorited' => $recipe->is_favorited ?? false
            ];
        }
        
        return json_encode($recipes_data);
    }

    /**
     * Get all recipes for client-side filtering
     * This should only be used when the total number of recipes is manageable
     * @param int $max_recipes Maximum number of recipes to return
     * @return array Array of Recipe objects
     */
    public function get_all_for_client_side($max_recipes = 500) {
        // Save current limit and offset
        $original_limit = $this->limit;
        $original_offset = $this->offset;
        
        // Set new limit and offset
        $this->limit = $max_recipes;
        $this->offset = 0;
        
        // Get recipes
        $recipes = $this->apply();
        
        // Restore original limit and offset
        $this->limit = $original_limit;
        $this->offset = $original_offset;
        
        return $recipes;
    }

    /**
     * Get error messages
     * @return array Array of error messages
     */
    public function errors() {
        return $this->errors;
    }

    /**
     * Check if there are any errors
     * @return bool True if there are errors, false otherwise
     */
    public function has_errors() {
        return !empty($this->errors);
    }
}

?>
