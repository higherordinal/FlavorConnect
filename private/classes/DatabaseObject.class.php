<?php

/**
 * Abstract base class for database objects
 * Provides common database operations and attribute handling
 */
abstract class DatabaseObject {
    /** @var string Table name for the database object */
    protected static $table_name;
    
    /** @var array List of database columns for the object */
    protected static $db_columns = [];
    
    /** @var mysqli Database connection instance */
    protected static $database;
    
    /** @var string Primary key field name */
    protected static $primary_key;
    
    /** @var array List of validation errors */
    public $errors = [];

    /**
     * Sets the database connection for all DatabaseObject instances
     * @param mysqli $database The database connection instance
     */
    public static function set_database($database) {
        self::$database = $database;
        error_log("Database connection set: " . (self::$database ? "true" : "false"));
        if (self::$database) {
            error_log("Connected to database: " . self::$database->host_info);
        }
    }

    /**
     * Gets the current database connection
     * @return mysqli The database connection instance
     * @throws Exception if database connection is not set
     */
    public static function get_database() {
        if(!isset(static::$database)) {
            throw new Exception('Database connection not set.');
        }
        return static::$database;
    }

    /**
     * Gets the primary key field name
     * @return string Name of the primary key field
     */
    protected static function get_primary_key() {
        return static::$primary_key;
    }

    /**
     * Finds database objects using a custom SQL query
     * @param string $sql The SQL query to execute
     * @return array Array of instantiated objects
     */
    public static function find_by_sql($sql) {
        $database = static::get_database();
        $result = mysqli_query($database, $sql);
        if(!$result) {
            exit("Database query failed: " . mysqli_error($database) . "\nQuery: " . $sql);
        }

        $object_array = [];
        while($record = mysqli_fetch_assoc($result)) {
            $object_array[] = static::instantiate($record);
        }
        mysqli_free_result($result);
        return $object_array;
    }

    /**
     * Finds all records in the table
     * @return array Array of all objects in the table
     */
    public static function find_all() {
        $sql = "SELECT * FROM " . static::$table_name;
        return static::find_by_sql($sql);
    }

    /**
     * Counts all records in the table
     * @return int Total number of records
     */
    public static function count_all() {
        $database = static::get_database();
        $sql = "SELECT COUNT(*) FROM " . static::$table_name;
        $result = mysqli_query($database, $sql);
        $row = mysqli_fetch_array($result);
        mysqli_free_result($result);
        return array_shift($row);
    }

    /**
     * Finds a single record by its ID
     * @param mixed $id The ID to search for
     * @return mixed The found object or false if not found
     */
    public static function find_by_id($id) {
        $database = static::get_database();
        $sql = "SELECT * FROM " . static::$table_name . " ";
        $sql .= "WHERE " . static::get_primary_key() . "='" . db_escape($database, $id) . "'";
        $obj_array = static::find_by_sql($sql);
        if(!empty($obj_array)) {
            return array_shift($obj_array);
        } else {
            return false;
        }
    }

    /**
     * Creates an object instance from a database record
     * @param array $record The database record
     * @return static New instance of the class
     */
    protected static function instantiate($record) {
        $object = new static;
        foreach($record as $property => $value) {
            if(property_exists($object, $property)) {
                $object->$property = $value;
            }
        }
        return $object;
    }

    /**
     * Validates the object's attributes
     * @return array Array of validation errors
     */
    protected function validate() {
        $this->errors = [];
        return $this->errors;
    }

    /**
     * Creates a new record in the database
     * @return bool True if creation was successful
     */
    protected function create() {
        $this->validate();
        if(!empty($this->errors)) { return false; }

        $attributes = $this->sanitized_attributes();
        $sql = "INSERT INTO " . static::$table_name . " (";
        $sql .= join(', ', array_keys($attributes));
        $sql .= ") VALUES ('";
        $sql .= join("', '", array_values($attributes));
        $sql .= "')";

        try {
            $database = static::get_database();
            $result = mysqli_query($database, $sql);
            if($result) {
                $insert_id = mysqli_insert_id($database);
                $pk = static::get_primary_key();
                if($pk && $insert_id) {
                    $this->$pk = $insert_id;
                }
                return true;
            }
        } catch(mysqli_sql_exception $e) {
            if($e->getCode() == 1062) { // Duplicate entry error
                $this->errors[] = "This name already exists.";
            } else {
                $this->errors[] = "Database error: " . $e->getMessage();
            }
        }
        return false;
    }

    /**
     * Updates an existing record in the database
     * @return bool True if update was successful
     */
    protected function update() {
        $this->validate();
        if(!empty($this->errors)) { return false; }

        $attributes = $this->sanitized_attributes();
        $attribute_pairs = [];
        foreach($attributes as $key => $value) {
            $attribute_pairs[] = "{$key}='{$value}'";
        }

        $sql = "UPDATE " . static::$table_name . " SET ";
        $sql .= join(', ', $attribute_pairs);
        $pk = static::get_primary_key();
        $sql .= " WHERE " . $pk . "='" . db_escape(static::get_database(), $this->$pk) . "' ";
        $sql .= "LIMIT 1";

        $database = static::get_database();
        $result = mysqli_query($database, $sql);
        return $result;
    }

    /**
     * Saves the object to the database (creates or updates)
     * @return bool True if save was successful
     */
    public function save() {
        $pk = static::get_primary_key();
        error_log("DatabaseObject save - PK: " . $pk);
        error_log("DatabaseObject save - this->$pk: " . (isset($this->$pk) ? $this->$pk : 'not set'));
        
        if(isset($this->$pk) && !empty($this->$pk)) {
            error_log("DatabaseObject save - Updating record");
            return $this->update();
        } else {
            error_log("DatabaseObject save - Creating new record");
            return $this->create();
        }
    }

    /**
     * Merges an array of attributes into the object
     * @param array $args Array of attributes to merge
     */
    public function merge_attributes($args=[]) {
        foreach($args as $key => $value) {
            if(property_exists($this, $key) && !is_null($value)) {
                $this->$key = $value;
            }
        }
    }

    /**
     * Gets all attributes except the primary key
     * @return array Array of object attributes
     */
    public function attributes() {
        $attributes = [];
        $pk = static::get_primary_key();
        foreach(static::$db_columns as $column) {
            if($column == $pk) { continue; }
            $attributes[$column] = $this->$column;
        }
        return $attributes;
    }

    /**
     * Gets sanitized attributes for database operations
     * @return array Array of sanitized attributes
     */
    protected function sanitized_attributes() {
        $database = static::get_database();
        $sanitized = [];
        foreach($this->attributes() as $key => $value) {
            $sanitized[$key] = db_escape($database, $value);
        }
        return $sanitized;
    }

    /**
     * Deletes the record from the database
     * @return bool True if deletion was successful
     */
    public function delete() {
        $pk = static::get_primary_key();
        $sql = "DELETE FROM " . static::$table_name . " ";
        $sql .= "WHERE " . $pk . "='" . db_escape(static::get_database(), $this->$pk) . "' ";
        $sql .= "LIMIT 1";

        $database = static::get_database();
        $result = mysqli_query($database, $sql);
        return $result;
    }
}