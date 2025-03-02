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
    /** @var string Type of attribute (style, diet, type) */
    private $type;

    /**
     * Constructor for RecipeAttribute class
     * @param array $args Associative array of property values
     */
    public function __construct($args=[]) {
        $this->id = $args['id'] ?? '';
        $this->name = $args['name'] ?? '';
        if (isset($args['type'])) {
            $this->type = $args['type'];
            self::setup_for_type($this->type);
        }
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
        $this->errors = [];
        
        // Check if name is blank
        if(is_blank($this->name)) {
            $this->errors[] = "Name cannot be blank.";
        } elseif (!has_length($this->name, ['min' => 2, 'max' => 255])) {
            $this->errors[] = "Name must be between 2 and 255 characters.";
        }
        
        // Check if name is unique
        $sql = "SELECT COUNT(*) FROM " . static::$table_name;
        $sql .= " WHERE name='" . db_escape(self::$database, $this->name) . "'";
        if($this->id != '') {
            $sql .= " AND " . static::$primary_key . " != '" . db_escape(self::$database, $this->id) . "'";
        }
        
        $result = mysqli_query(self::$database, $sql);
        if(!$result) {
            $this->errors[] = "Database error: " . mysqli_error(self::$database);
            return $this->errors;
        }
        
        $row = mysqli_fetch_row($result);
        if($row[0] > 0) {
            $this->errors[] = "A " . $this->type . " with this name already exists.";
        }
        
        return $this->errors;
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
     * Finds one attribute by ID
     * @param int $id The attribute ID
     * @return RecipeAttribute|false RecipeAttribute object or false if not found
     */
    public static function find_by_id($id) {
        if (!isset(static::$table_name)) {
            throw new Exception("Type must be set before calling find_by_id");
        }
        
        $sql = "SELECT " . self::$primary_key . " as id, name FROM " . self::$table_name;
        $sql .= " WHERE " . self::$primary_key . " = ?";
        
        $stmt = self::$database->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $obj_array = static::instantiate_result($result);
        $obj = !empty($obj_array) ? array_shift($obj_array) : false;
        
        if ($obj) {
            // Determine type from table name
            foreach (self::$valid_types as $type => $info) {
                if ($info['table'] === static::$table_name) {
                    $obj->type = $type;
                    break;
                }
            }
        }
        
        return $obj;
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

    /**
     * Instantiates a RecipeAttribute object from a database record
     * @param array $record Database record
     * @return RecipeAttribute RecipeAttribute object
     */
    protected static function instantiate($record) {
        $object = new static();
        
        foreach($record as $property => $value) {
            if(property_exists($object, $property)) {
                $object->$property = $value;
            }
        }
        
        // Set type based on current table
        foreach (self::$valid_types as $type => $info) {
            if ($info['table'] === static::$table_name) {
                $object->type = $type;
                break;
            }
        }
        
        return $object;
    }

    /**
     * Checks if a name already exists for this attribute type
     * @param string $name The name to check
     * @param int|null $exclude_id ID to exclude from the check (for updates)
     * @return bool True if name exists, false otherwise
     */
    public static function name_exists($name, $exclude_id = null) {
        $sql = "SELECT * FROM " . static::$table_name . " WHERE name = '" . db_escape(static::$database, $name) . "'";
        if ($exclude_id !== null) {
            $sql .= " AND " . static::$primary_key . " != '" . db_escape(static::$database, $exclude_id) . "'";
        }
        $sql .= " LIMIT 1";
        $result = static::find_by_sql($sql);
        return !empty($result);
    }

    /**
     * Gets the current table name
     * @return string The table name
     */
    public static function get_table_name() {
        return static::$table_name;
    }

    /**
     * Gets the current primary key column name
     * @return string The primary key column name
     */
    public static function get_primary_key_column() {
        return static::$primary_key;
    }

    /**
     * Gets all attributes except the primary key
     * @return array Array of object attributes
     */
    public function attributes() {
        $attributes = [];
        foreach(static::$db_columns as $column) {
            if($column == static::$primary_key) { continue; }
            $attributes[$column] = $this->$column;
        }
        return $attributes;
    }

    /**
     * Saves the attribute to the database
     * @return bool True if save was successful
     */
    public function save() {
        if (!isset($this->type)) {
            throw new Exception("Type must be set before saving");
        }
        
        self::setup_for_type($this->type);
        return parent::save();
    }

    /**
     * Counts recipes using a specific attribute
     * @param int $id The attribute ID
     * @param string $type The attribute type (style, diet, type)
     * @return int Number of recipes using this attribute
     */
    public static function count_by_attribute_id($id, $type) {
        self::setup_for_type($type);
        
        $column = self::$valid_types[$type]['id'];
        
        $sql = "SELECT COUNT(*) FROM recipe WHERE {$column} = ?";
        
        $stmt = self::$database->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $row = $result->fetch_row();
        return $row[0] ?? 0;
    }
}
