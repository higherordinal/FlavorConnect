<?php

/**
 * Measurement class for managing recipe measurements
 * Extends DatabaseObject for database operations
 */
class Measurement extends DatabaseObject {
    /** @var string Database table name */
    static protected $table_name = 'measurement';
    /** @var array Database columns */
    static protected $db_columns = ['measurement_id', 'name'];
    /** @var string Primary key column */
    static protected $primary_key = 'measurement_id';

    /** @var int Unique identifier for the measurement */
    public $measurement_id;
    /** @var string Name of the measurement */
    public $name;

    /**
     * Constructor for Measurement class
     * @param array $args Associative array of property values
     */
    public function __construct($args=[]) {
        $this->measurement_id = $args['measurement_id'] ?? '';
        $this->name = $args['name'] ?? '';
    }

    /**
     * Validates the measurement data
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
        if($this->measurement_id != '') {
            $sql .= " AND measurement_id != '" . db_escape(self::$database, $this->measurement_id) . "'";
        }
        
        $result = mysqli_query(self::$database, $sql);
        if(!$result) {
            $this->errors[] = "Database error: " . mysqli_error(self::$database);
            return $this->errors;
        }
        
        $row = mysqli_fetch_row($result);
        if($row[0] > 0) {
            $this->errors[] = "A measurement with this name already exists.";
        }
        
        return $this->errors;
    }

    /**
     * Gets all measurements ordered by name
     * @return array Array of Measurement objects
     */
    public static function find_all_ordered() {
        $sql = "SELECT * FROM " . static::$table_name . " ORDER BY name ASC";
        return static::find_by_sql($sql);
    }
}
?>
