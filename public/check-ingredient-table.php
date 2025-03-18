<?php
require_once('../private/core/initialize.php');

// Direct database query to see the structure
$sql = "DESCRIBE ingredient";
$result = Recipe::$database->query($sql);

echo "<h1>Ingredient Table Structure</h1>";
echo "<pre>";

if($result) {
    while($row = $result->fetch_assoc()) {
        print_r($row);
    }
} else {
    echo "Error: " . Recipe::$database->error;
}

echo "</pre>";

// Check if recipe_id column exists
$sql = "SHOW COLUMNS FROM ingredient LIKE 'recipe_id'";
$result = Recipe::$database->query($sql);

echo "<h2>Does recipe_id column exist?</h2>";
echo "<pre>";
if($result && $result->num_rows > 0) {
    echo "YES - recipe_id column exists in the ingredient table";
    
    // Show create table statement
    $sql = "SHOW CREATE TABLE ingredient";
    $result = Recipe::$database->query($sql);
    if($result) {
        $row = $result->fetch_array();
        echo "\n\nTable creation SQL:\n";
        echo $row[1];
    }
} else {
    echo "NO - recipe_id column does not exist in the ingredient table";
}
echo "</pre>";

// Show some sample data
$sql = "SELECT * FROM ingredient LIMIT 5";
$result = Recipe::$database->query($sql);

echo "<h2>Sample Data</h2>";
echo "<pre>";
if($result) {
    while($row = $result->fetch_assoc()) {
        print_r($row);
    }
} else {
    echo "Error: " . Recipe::$database->error;
}
echo "</pre>";
?>
