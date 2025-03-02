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
        $measurement_data = [
            'name' => $this->name
        ];
        
        $this->errors = validate_measurement_data($measurement_data, $this->measurement_id);
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
