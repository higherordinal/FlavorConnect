<?php

/**
 * RecipeIngredient class for managing recipe ingredients
 * Extends DatabaseObject for database operations
 */
class RecipeIngredient extends DatabaseObject {
    /** @var string Database table name */
    static protected $table_name = 'recipe_ingredient';
    /** @var array Database columns */
    static protected $db_columns = ['recipe_ingredient_id', 'recipe_id', 'ingredient_id', 'measurement_id', 'quantity'];
    /** @var string Primary key column */
    static protected $primary_key = 'recipe_ingredient_id';

    /** @var int Unique identifier for the recipe ingredient */
    public $recipe_ingredient_id;
    /** @var int Recipe ID */
    public $recipe_id;
    /** @var int|null Ingredient ID */
    public $ingredient_id;
    /** @var int Measurement ID */
    public $measurement_id;
    /** @var int Quantity */
    public $quantity;
    /** @var string Ingredient name */
    public $name;

    private $_measurement;
    private $_ingredient;

    /**
     * Constructor for RecipeIngredient class
     * @param array $args Associative array of property values
     */
    public function __construct($args=[]) {
        $this->recipe_id = $args['recipe_id'] ?? '';
        $this->ingredient_id = isset($args['ingredient_id']) && $args['ingredient_id'] !== '' ? (int)$args['ingredient_id'] : null;
        $this->measurement_id = $args['measurement_id'] ?? '';
        $this->quantity = $args['quantity'] ?? '';
        // Convert ingredient name to lowercase
        $this->name = isset($args['name']) ? strtolower($args['name']) : '';
    }

    /**
     * Get the measurement object
     * @return Measurement|null The measurement object or null if not found
     */
    public function get_measurement() {
        if(!isset($this->_measurement) && $this->measurement_id) {
            $this->_measurement = Measurement::find_by_id($this->measurement_id);
        }
        return $this->_measurement;
    }

    /**
     * Get the ingredient object
     * @return object|null The ingredient object or null if not found
     */
    public function get_ingredient() {
        if(!isset($this->_ingredient) && $this->ingredient_id) {
            $sql = "SELECT name FROM ingredient WHERE ingredient_id = ?";
            $stmt = self::$database->prepare($sql);
            $stmt->bind_param("i", $this->ingredient_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if($row = $result->fetch_object()) {
                // Ensure the name is lowercase when retrieving
                $row->name = strtolower($row->name);
                $this->_ingredient = $row;
            }
        }
        return $this->_ingredient;
    }

    /**
     * Magic getter for measurement and ingredient properties
     */
    public function __get($name) {
        if($name === 'measurement') {
            return $this->get_measurement();
        }
        if($name === 'ingredient') {
            return $this->get_ingredient();
        }
    }

    /**
     * Validates recipe ingredient data
     * @return bool True if validation passes, false otherwise
     */
    protected function validate() {
        $ingredient_data = [
            'recipe_id' => $this->recipe_id,
            'measurement_id' => $this->measurement_id,
            'quantity' => $this->quantity
        ];
        
        $this->errors = validate_recipe_ingredient_data($ingredient_data);
        
        // If name is provided but no ingredient_id, we need to create or find an ingredient
        if (!empty($this->name) && empty($this->ingredient_id)) {
            $this->ingredient_id = $this->get_or_create_ingredient($this->name);
        }
        
        return empty($this->errors);
    }
    
    /**
     * Get or create an ingredient
     * @param string $name Ingredient name
     * @return int Ingredient ID
     */
    private function get_or_create_ingredient($name) {
        // Ensure name is lowercase
        $name = strtolower($name);
        
        // First, try to find an existing ingredient with this name
        $sql = "SELECT ingredient_id FROM ingredient WHERE name = ?";
        $stmt = self::$database->prepare($sql);
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($row = $result->fetch_assoc()) {
            // Ingredient exists, return its ID
            return $row['ingredient_id'];
        } else {
            // Ensure we have a recipe_id
            if (empty($this->recipe_id)) {
                $this->errors[] = "Cannot create ingredient: recipe_id is required";
                return null;
            }
            
            // Create a new ingredient with the recipe_id
            $sql = "INSERT INTO ingredient (name, recipe_id) VALUES (?, ?)";
            $stmt = self::$database->prepare($sql);
            $stmt->bind_param("si", $name, $this->recipe_id);
            $stmt->execute();
            
            if($stmt->affected_rows > 0) {
                return self::$database->insert_id;
            } else {
                $this->errors[] = "Failed to create ingredient: " . self::$database->error;
                return null;
            }
        }
    }
    
    /**
     * Creates a new record in the database
     * @return bool True if creation was successful
     */
    public function create() {
        $this->validate();
        if(!empty($this->errors)) { return false; }
        
        // For recipe_id, we'll handle this in the SQL query
        // It might be empty for new recipes, but will be set by the database's auto-increment
        
        $attributes = $this->sanitized_attributes();
        
        // Ensure ingredient_id is properly set
        if (empty($attributes['ingredient_id']) || $attributes['ingredient_id'] === '') {
            // Try to use a default ingredient if available
            $sql = "SELECT ingredient_id FROM ingredient LIMIT 1";
            $result = self::$database->query($sql);
            if($row = $result->fetch_assoc()) {
                $attributes['ingredient_id'] = $row['ingredient_id'];
            } else {
                // If no ingredients exist, create a basic one
                $sql = "INSERT INTO ingredient (name, recipe_id) VALUES ('other', 1)";
                self::$database->query($sql);
                $attributes['ingredient_id'] = self::$database->insert_id;
            }
        }
        
        $sql = "INSERT INTO " . static::$table_name . " (";
        $sql .= join(', ', array_keys($attributes));
        $sql .= ") VALUES (";
        
        $values = [];
        foreach(array_values($attributes) as $value) {
            if ($value === null || $value === '') {
                $values[] = "NULL";
            } else {
                $values[] = "'" . self::$database->escape_string($value) . "'";
            }
        }
        
        $sql .= join(", ", $values);
        $sql .= ")";
        
        $result = self::$database->query($sql);
        if($result) {
            $this->recipe_ingredient_id = self::$database->insert_id;
            return true;
        } else {
            // INSERT failed
            $this->errors[] = "Database error: " . self::$database->error;
            return false;
        }
    }

    /**
     * Creates a new recipe ingredient with a new ingredient
     * @param array $args Associative array of property values including ingredient name
     * @return bool True if creation was successful
     */
    public static function create_with_ingredient($args=[]) {
        // First create the ingredient
        $ingredient = new Ingredient([
            'recipe_id' => $args['recipe_id'],
            'name' => $args['ingredient_name']
        ]);
        
        if($ingredient->save()) {
            // Then create the recipe ingredient
            $recipe_ingredient = new RecipeIngredient([
                'recipe_id' => $args['recipe_id'],
                'ingredient_id' => $ingredient->ingredient_id,
                'measurement_id' => $args['measurement_id'],
                'quantity' => $args['quantity']
            ]);
            
            return $recipe_ingredient->save();
        }
        return false;
    }

    /**
     * Find all ingredients for a specific recipe
     * @param int $recipe_id Recipe ID to find ingredients for
     * @return array Array of RecipeIngredient objects
     */
    public static function find_by_recipe_id($recipe_id) {
        $sql = "SELECT ri.*, i.name FROM " . static::$table_name . " ri ";
        $sql .= "JOIN ingredient i ON ri.ingredient_id = i.ingredient_id ";
        $sql .= "WHERE ri.recipe_id = ? ";
        $sql .= "ORDER BY ri.recipe_ingredient_id ASC";
        
        $stmt = self::$database->prepare($sql);
        if (!$stmt) {
            return [];
        }
        
        $stmt->bind_param("i", $recipe_id);
        $success = $stmt->execute();
        if (!$success) {
            return [];
        }
        
        $result = $stmt->get_result();
        
        $ingredients = [];
        while($row = $result->fetch_assoc()) {
            // Ensure ingredient name is lowercase
            $row['name'] = strtolower($row['name']);
            $ingredients[] = static::instantiate($row);
        }
        return $ingredients;
    }
}

?>
