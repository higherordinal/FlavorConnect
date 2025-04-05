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
        $this->name = $args['name'] ?? '';
    }

    /**
     * Get the measurement object
     * @return Measurement|null The measurement object or null if not found
     */
    public function getMeasurement() {
        if(!isset($this->_measurement) && $this->measurement_id) {
            $this->_measurement = Measurement::find_by_id($this->measurement_id);
        }
        return $this->_measurement;
    }

    /**
     * Get the ingredient object
     * @return object|null The ingredient object or null if not found
     */
    public function getIngredient() {
        if(!isset($this->_ingredient) && $this->ingredient_id) {
            $sql = "SELECT name FROM ingredient WHERE ingredient_id = ?";
            $stmt = self::$database->prepare($sql);
            $stmt->bind_param("i", $this->ingredient_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if($row = $result->fetch_object()) {
                $this->_ingredient = $row;
            }
        }
        return $this->_ingredient;
    }

    /**
     * Magic getter for measurement property
     */
    public function __get($name) {
        if($name === 'measurement') {
            return $this->getMeasurement();
        }
        if($name === 'ingredient') {
            return $this->getIngredient();
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
        // Check if ingredient exists
        $sql = "SELECT ingredient_id FROM ingredient WHERE name='" . self::$database->escape_string($name) . "' LIMIT 1";
        $result = self::$database->query($sql);
        
        if($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['ingredient_id'];
        }
        
        // Create new ingredient
        $sql = "INSERT INTO ingredient (name, recipe_id) VALUES ('" . self::$database->escape_string($name) . "', '" . self::$database->escape_string($this->recipe_id) . "')";
        $result = self::$database->query($sql);
        
        if($result) {
            return self::$database->insert_id;
        } else {
            // If failed to create, return a default ID
            return 1;
        }
    }
    
    /**
     * Creates a new record in the database
     * @return bool True if creation was successful
     */
    protected function create() {
        // If name is provided but no ingredient_id, we need to create or find an ingredient
        if (!empty($this->name) && empty($this->ingredient_id)) {
            $this->ingredient_id = $this->get_or_create_ingredient($this->name);
        }
        
        $this->validate();
        if(!empty($this->errors)) { return false; }
        
        $attributes = $this->sanitized_attributes();
        
        // Ensure ingredient_id is properly set
        if (empty($attributes['ingredient_id']) || $attributes['ingredient_id'] === '') {
            // If still empty, use a default value
            $attributes['ingredient_id'] = 1;
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
     * Gets the ingredient object associated with this recipe ingredient
     * @return Ingredient|null The ingredient object or null if not found
     */
    public function get_ingredient() {
        if($this->ingredient_id) {
            return Ingredient::find_by_id($this->ingredient_id);
        }
        return null;
    }

    /**
     * Gets the measurement object associated with this recipe ingredient
     * @return Measurement|null The measurement object or null if not found
     */
    public function get_measurement() {
        return Measurement::find_by_id($this->measurement_id);
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
            $ingredients[] = static::instantiate($row);
        }
        return $ingredients;
    }
}

?>
