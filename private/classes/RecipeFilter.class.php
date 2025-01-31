<?php

/**
 * RecipeFilter class for handling recipe filtering and search operations
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

    /**
     * Constructor for RecipeFilter class
     * @param array $filters Associative array of filter values
     */
    public function __construct($filters = []) {
        $this->search = $filters['search'] ?? '';
        $this->style_id = $filters['style_id'] ?? null;
        $this->diet_id = $filters['diet_id'] ?? null;
        $this->type_id = $filters['type_id'] ?? null;
        $this->sort = $filters['sort'] ?? 'newest';
        $this->limit = $filters['limit'] ?? 12;
        $this->offset = $filters['offset'] ?? 0;
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
                return " ORDER BY avg_rating DESC NULLS LAST, r.created_at DESC";
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
        $sql = "SELECT r.* FROM " . Recipe::get_table_name() . " r";
        
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
        return Recipe::instantiate_result($result);
    }

    /**
     * Counts total recipes matching the filter
     * @return int Total number of matching recipes
     */
    public function count() {
        $sql = "SELECT COUNT(*) as count FROM " . Recipe::get_table_name() . " r";
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
    }
}

?>
