<?php
// Simple script to check all tables for auto-increment primary keys
// No framework dependencies

// Try to connect using environment variables first (for Docker)
$host = getenv('MYSQL_HOST') ?: 'db';
$user = getenv('MYSQL_USER') ?: 'hcvaughn';
$pass = getenv('MYSQL_PASSWORD') ?: '@connect4Establish';
$db_name = getenv('MYSQL_DATABASE') ?: 'flavorconnect';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Database Auto-Increment Check</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { color: #333; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .yes { color: green; font-weight: bold; }
        .no { color: red; font-weight: bold; }
        .error { color: red; background-color: #ffeeee; padding: 10px; border: 1px solid #ffcccc; }
    </style>
</head>
<body>
    <h1>Database Auto-Increment Check</h1>";

// Try different connection methods
$connection = null;
$error_messages = [];

// Method 1: Try Docker environment connection
try {
    $connection = new mysqli($host, $user, $pass, $db_name);
    if ($connection->connect_error) {
        $error_messages[] = "Docker environment connection failed: " . $connection->connect_error;
        $connection = null;
    }
} catch (Exception $e) {
    $error_messages[] = "Docker environment connection exception: " . $e->getMessage();
}

// Method 2: Try localhost connection
if (!$connection) {
    try {
        $connection = new mysqli('localhost', $user, $pass, $db_name);
        if ($connection->connect_error) {
            $error_messages[] = "Localhost connection failed: " . $connection->connect_error;
            $connection = null;
        }
    } catch (Exception $e) {
        $error_messages[] = "Localhost connection exception: " . $e->getMessage();
    }
}

// Method 3: Try 127.0.0.1 connection
if (!$connection) {
    try {
        $connection = new mysqli('127.0.0.1', $user, $pass, $db_name);
        if ($connection->connect_error) {
            $error_messages[] = "127.0.0.1 connection failed: " . $connection->connect_error;
            $connection = null;
        }
    } catch (Exception $e) {
        $error_messages[] = "127.0.0.1 connection exception: " . $e->getMessage();
    }
}

// If all connection methods failed
if (!$connection) {
    echo "<div class='error'>";
    echo "<h2>Database Connection Failed</h2>";
    echo "<p>Could not connect to the database using any of the available methods.</p>";
    echo "<h3>Error Details:</h3>";
    echo "<ul>";
    foreach ($error_messages as $error) {
        echo "<li>" . htmlspecialchars($error) . "</li>";
    }
    echo "</ul>";
    echo "<h3>Connection Details Attempted:</h3>";
    echo "<ul>";
    echo "<li>Host: " . htmlspecialchars($host) . " (also tried localhost and 127.0.0.1)</li>";
    echo "<li>User: " . htmlspecialchars($user) . "</li>";
    echo "<li>Database: " . htmlspecialchars($db_name) . "</li>";
    echo "</ul>";
    echo "</div>";
    exit;
}

// Get all tables in the database
$tables_result = $connection->query("SHOW TABLES");

if (!$tables_result) {
    echo "<div class='error'><p>Error fetching tables: " . $connection->error . "</p></div>";
    exit;
}

echo "<h2>Primary Key Auto-Increment Status</h2>";
echo "<table>
    <tr>
        <th>Table Name</th>
        <th>Primary Key</th>
        <th>Auto Increment</th>
        <th>Data Type</th>
    </tr>";

while ($table_row = $tables_result->fetch_array()) {
    $table = $table_row[0];
    
    // Get primary key information
    $keys_result = $connection->query("SHOW KEYS FROM `$table` WHERE Key_name = 'PRIMARY'");
    
    if (!$keys_result) {
        echo "<tr><td>" . htmlspecialchars($table) . "</td><td colspan='3'>Error: " . $connection->error . "</td></tr>";
        continue;
    }
    
    $primary_key_info = $keys_result->fetch_assoc();
    
    if (!$primary_key_info) {
        echo "<tr><td>" . htmlspecialchars($table) . "</td><td colspan='3'>No primary key defined</td></tr>";
        continue;
    }
    
    $primary_key = $primary_key_info['Column_name'];
    
    // Get column details for the primary key
    $column_result = $connection->query("SHOW COLUMNS FROM `$table` WHERE Field = '$primary_key'");
    
    if (!$column_result) {
        echo "<tr><td>" . htmlspecialchars($table) . "</td><td>" . htmlspecialchars($primary_key) . "</td><td colspan='2'>Error: " . $connection->error . "</td></tr>";
        continue;
    }
    
    $column = $column_result->fetch_assoc();
    $is_auto_increment = strpos($column['Extra'], 'auto_increment') !== false;
    $data_type = $column['Type'];
    
    echo "<tr>";
    echo "<td>" . htmlspecialchars($table) . "</td>";
    echo "<td>" . htmlspecialchars($primary_key) . "</td>";
    
    if ($is_auto_increment) {
        echo "<td class='yes'>Yes</td>";
    } else {
        echo "<td class='no'>No</td>";
    }
    
    echo "<td>" . htmlspecialchars($data_type) . "</td>";
    echo "</tr>";
}

echo "</table>";

// Show detailed information for tables with issues (no auto-increment on int primary key)
echo "<h2>Tables Needing Auto-Increment</h2>";
echo "<p>These tables have integer primary keys without auto-increment:</p>";

$tables_result = $connection->query("SHOW TABLES");
$found_issues = false;

echo "<table>
    <tr>
        <th>Table Name</th>
        <th>Primary Key</th>
        <th>Data Type</th>
        <th>SQL to Fix</th>
    </tr>";

while ($table_row = $tables_result->fetch_array()) {
    $table = $table_row[0];
    
    // Get primary key information
    $keys_result = $connection->query("SHOW KEYS FROM `$table` WHERE Key_name = 'PRIMARY'");
    if (!$keys_result) continue;
    
    $primary_key_info = $keys_result->fetch_assoc();
    if (!$primary_key_info) continue;
    
    $primary_key = $primary_key_info['Column_name'];
    
    // Get column details for the primary key
    $column_result = $connection->query("SHOW COLUMNS FROM `$table` WHERE Field = '$primary_key'");
    if (!$column_result) continue;
    
    $column = $column_result->fetch_assoc();
    $is_auto_increment = strpos($column['Extra'], 'auto_increment') !== false;
    $data_type = $column['Type'];
    
    // Check if it's an integer type without auto-increment
    if (!$is_auto_increment && (
        strpos($data_type, 'int') !== false || 
        strpos($data_type, 'bigint') !== false || 
        strpos($data_type, 'smallint') !== false || 
        strpos($data_type, 'tinyint') !== false
    )) {
        $found_issues = true;
        $fix_sql = "ALTER TABLE `$table` MODIFY `$primary_key` $data_type NOT NULL AUTO_INCREMENT;";
        
        echo "<tr>";
        echo "<td>" . htmlspecialchars($table) . "</td>";
        echo "<td>" . htmlspecialchars($primary_key) . "</td>";
        echo "<td>" . htmlspecialchars($data_type) . "</td>";
        echo "<td><code>" . htmlspecialchars($fix_sql) . "</code></td>";
        echo "</tr>";
    }
}

if (!$found_issues) {
    echo "<tr><td colspan='4'>No tables with integer primary keys missing auto-increment were found.</td></tr>";
}

echo "</table>";

// Close the connection
$connection->close();
?>
</body>
</html>
