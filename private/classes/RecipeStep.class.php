<?php

/**
 * RecipeStep class for managing recipe steps/instructions
 * Extends DatabaseObject for database operations
 */
class RecipeStep extends DatabaseObject {
    /** @var string Database table name */
    static protected $table_name = 'recipe_step';
    /** @var array Database columns */
    static protected $db_columns = ['step_id', 'recipe_id', 'step_number', 'instruction'];
    /** @var string Primary key column */
    static protected $primary_key = 'step_id';

    /** @var int Unique identifier for the recipe step */
    public $step_id;
    /** @var int Recipe ID */
    public $recipe_id;
    /** @var int Step number/order */
    public $step_number;
    /** @var string Instruction text */
    public $instruction;

    /**
     * Constructor for RecipeStep class
     * @param array $args Associative array of property values
     */
    public function __construct($args=[]) {
        $this->recipe_id = $args['recipe_id'] ?? '';
        $this->step_number = $args['step_number'] ?? '';
        $this->instruction = $args['instruction'] ?? '';
    }

    /**
     * Find all steps for a specific recipe
     * @param int $recipe_id Recipe ID to find steps for
     * @return array Array of RecipeStep objects
     */
    public static function find_by_recipe_id($recipe_id) {
        $sql = "SELECT * FROM " . static::$table_name;
        $sql .= " WHERE recipe_id = ?";
        $sql .= " ORDER BY step_number ASC";
        
        $stmt = self::$database->prepare($sql);
        $stmt->bind_param("i", $recipe_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $steps = [];
        while($row = $result->fetch_assoc()) {
            $steps[] = static::instantiate($row);
        }
        return $steps;
    }

    /**
     * Validates recipe step data
     * @return bool True if validation passes, false otherwise
     */
    protected function validate() {
        $step_data = [
            'recipe_id' => $this->recipe_id,
            'step_number' => $this->step_number,
            'instruction' => $this->instruction
        ];
        
        $this->errors = validate_recipe_step_data($step_data);
        return empty($this->errors);
    }
}

?>
