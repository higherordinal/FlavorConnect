<?php

/**
 * RecipeAttribute class for managing recipe attributes (style, diet, type)
 * Extends DatabaseObject for database operations
 */
class RecipeAttribute extends DatabaseObject {
    /** @var string Database table name - set dynamically based on type */
    protected static $table_name;
    /** @var array Database columns */
    protected static $db_columns;
    /** @var string Primary key column */
    protected static $primary_key;

    /** @var array Valid attribute types and their column mappings */
    private static $valid_types = [
        'style' => ['table' => 'recipe_style', 'id' => 'style_id'],
        'diet' => ['table' => 'recipe_diet', 'id' => 'diet_id'],
        'type' => ['table' => 'recipe_type', 'id' => 'type_id']
    ];

    /** @var int Unique identifier for the attribute */
    public $id;
    /** @var string Name/value of the attribute */
    public $name;

    /**
     * Constructor for RecipeAttribute class
     * @param array $args Associative array of property values
     */
    public function __construct($args=[]) {
        $this->id = $args['id'] ?? '';
        $this->name = $args['name'] ?? '';
    }

    /**
     * Sets up the class for a specific attribute type
     * @param string $type The attribute type (style, diet, type)
     * @throws InvalidArgumentException if type is invalid
     */
    private static function setup_for_type($type) {
        if (!isset(self::$valid_types[$type])) {
            throw new InvalidArgumentException("Invalid attribute type: {$type}");
        }
        
        $type_info = self::$valid_types[$type];
        self::$table_name = $type_info['table'];
        self::$primary_key = $type_info['id'];
        self::$db_columns = [self::$primary_key, 'name'];
    }

    /**
     * Validates the attribute data
     * @return array Array of validation errors
     */
    protected function validate() {
        return validate_metadata(['name' => $this->name]);
    }

    /**
     * Gets all attributes of a specific type
     * @param string $type The attribute type (style, diet, type)
     * @return array Array of RecipeAttribute objects
     */
    public static function find_by_type($type) {
        self::setup_for_type($type);
        
        $sql = "SELECT " . self::$primary_key . " as id, name FROM " . self::$table_name . " ORDER BY name";
        
        $stmt = self::$database->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return static::instantiate_result($result);
    }

    /**
     * Finds one attribute by ID and type
     * @param int $id The attribute ID
     * @param string $type The attribute type (style, diet, type)
     * @return RecipeAttribute|false RecipeAttribute object or false if not found
     */
    public static function find_one($id, $type) {
        self::setup_for_type($type);
        
        $sql = "SELECT " . self::$primary_key . " as id, name FROM " . self::$table_name;
        $sql .= " WHERE " . self::$primary_key . " = ?";
        
        $stmt = self::$database->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $obj_array = static::instantiate_result($result);
        return !empty($obj_array) ? array_shift($obj_array) : false;
    }

    /**
     * Instantiates RecipeAttribute objects from a database result
     * @param mysqli_result $result Database result
     * @return array Array of RecipeAttribute objects
     */
    protected static function instantiate_result($result) {
        $object_array = [];
        while($record = $result->fetch_assoc()) {
            $object_array[] = static::instantiate($record);
        }
        return $object_array;
    }
}
