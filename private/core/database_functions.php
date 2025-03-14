<?php

/**
 * Establishes a database connection and sets it for DatabaseObject classes
 * @return mysqli The database connection instance
 */
function db_connect() {
  $connection = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
  confirm_db_connect($connection);
  DatabaseObject::set_database($connection);
  return $connection;
}

/**
 * Verifies that the database connection was successful
 * @param mysqli $connection The database connection to verify
 * @throws Exception if connection fails
 */
function confirm_db_connect($connection) {
  if(mysqli_connect_errno()) {
    $msg = "Database connection failed: ";
    $msg .= mysqli_connect_error();
    $msg .= " (" . mysqli_connect_errno() . ")";
    exit($msg);
  }
}

/**
 * Escapes a string for database insertion
 * @param mysqli $connection The database connection
 * @param string $string The string to escape
 * @return string The escaped string
 */
function db_escape($connection, $string) {
  if ($string === null) {
    return '';
  }
  return mysqli_real_escape_string($connection, $string);
}

?>