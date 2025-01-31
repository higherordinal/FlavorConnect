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

    /**
     * Constructor for RecipeIngredient class
     * @param array $args Associative array of property values
     */
    public function __construct($args=[]) {
        $this->recipe_id = $args['recipe_id'] ?? '';
        $this->ingredient_id = $args['ingredient_id'] ?? null;
        $this->measurement_id = $args['measurement_id'] ?? '';
        $this->quantity = $args['quantity'] ?? '';
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
        return empty($this->errors);
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
        $sql = "SELECT * FROM " . static::$table_name;
        $sql .= " WHERE recipe_id = ?";
        $sql .= " ORDER BY recipe_ingredient_id ASC";
        
        $stmt = self::$database->prepare($sql);
        $stmt->bind_param("i", $recipe_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $ingredients = [];
        while($row = $result->fetch_assoc()) {
            $ingredients[] = static::instantiate($row);
        }
        return $ingredients;
    }
}

?>
