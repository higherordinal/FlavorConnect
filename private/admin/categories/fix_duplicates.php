<?php
// Connect to database
$database = new mysqli('localhost', 'root', '', 'flavorconnect');

if ($database->connect_error) {
    die('Connection failed: ' . $database->connect_error);
}

// List of actual recipe types to keep
$actual_types = [
    'Breakfast',
    'Lunch',
    'Dinner',
    'Appetizer',
    'Dessert',
    'Snack',
    'Beverage',
    'Soup',
    'Salad',
    'Main Course'
];

// Delete duplicated styles from recipe_type table
$sql = "DELETE FROM recipe_type WHERE name IN (
    SELECT name FROM recipe_style
)";
$database->query($sql);

// Delete duplicated diets from recipe_type table
$sql = "DELETE FROM recipe_type WHERE name IN (
    SELECT name FROM recipe_diet
)";
$database->query($sql);

// Keep only actual recipe types
$sql = "DELETE FROM recipe_type WHERE name NOT IN ('" . implode("','", $actual_types) . "')";
$database->query($sql);

echo "Migration completed successfully!\n";
echo "Please verify the tables:\n\n";

// Show the results
$sql = "SELECT * FROM recipe_type ORDER BY name";
$result = $database->query($sql);
echo "Recipe Types:\n";
while($row = $result->fetch_assoc()) {
    echo "- " . $row['name'] . "\n";
}

$database->close();
