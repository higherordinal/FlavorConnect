<?php
/**
 * Test script to verify recipe images
 * 
 * This script tests the Recipe class's get_image_path method to ensure
 * it correctly returns WebP versions of images.
 */

// Define base paths based on environment
$is_docker = file_exists('/.dockerenv');
$base_path = $is_docker ? '/var/www/html' : __DIR__ . '/..';
$private_path = $base_path . '/private';
$public_path = $base_path . '/public';

// Include initialization file
require_once($private_path . '/core/initialize.php');

// Detect if running in CLI or web
$is_cli = (php_sapi_name() === 'cli');

// Function to output results in a format suitable for both CLI and web
function output($message, $is_cli) {
    if ($is_cli) {
        echo $message . PHP_EOL;
    } else {
        echo $message . '<br>';
    }
}

// Set up output formatting
if (!$is_cli) {
    echo '<html><head><title>Recipe Image Test</title>';
    echo '<style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { color: #333; }
        .success { color: green; }
        .error { color: red; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        tr:nth-child(even) { background-color: #f9f9f9; }
    </style>';
    echo '</head><body>';
    echo '<h1>Recipe Image Test</h1>';
}

// Header
output("Recipe Image Test", $is_cli);
output("================", $is_cli);

// Get all recipes
$recipes = Recipe::find_all();

if (!$is_cli) {
    echo '<table>';
    echo '<tr><th>Recipe ID</th><th>Title</th><th>Original Path</th><th>Thumb Path</th><th>Optimized Path</th><th>Banner Path</th><th>Files Exist</th></tr>';
}

foreach ($recipes as $recipe) {
    $original_path = $recipe->get_image_path('original');
    $thumb_path = $recipe->get_image_path('thumb');
    $optimized_path = $recipe->get_image_path('optimized');
    $banner_path = $recipe->get_image_path('banner');
    
    // Check if files exist
    $original_exists = !empty($original_path) && file_exists(PUBLIC_PATH . $original_path);
    $thumb_exists = !empty($thumb_path) && file_exists(PUBLIC_PATH . $thumb_path);
    $optimized_exists = !empty($optimized_path) && file_exists(PUBLIC_PATH . $optimized_path);
    $banner_exists = !empty($banner_path) && file_exists(PUBLIC_PATH . $banner_path);
    
    if ($is_cli) {
        output("", $is_cli);
        output("Recipe ID: " . $recipe->recipe_id, $is_cli);
        output("Title: " . $recipe->title, $is_cli);
        output("Original Path: " . $original_path, $is_cli);
        output("Thumb Path: " . $thumb_path, $is_cli);
        output("Optimized Path: " . $optimized_path, $is_cli);
        output("Banner Path: " . $banner_path, $is_cli);
        output("Original Exists: " . ($original_exists ? "Yes" : "No"), $is_cli);
        output("Thumb Exists: " . ($thumb_exists ? "Yes" : "No"), $is_cli);
        output("Optimized Exists: " . ($optimized_exists ? "Yes" : "No"), $is_cli);
        output("Banner Exists: " . ($banner_exists ? "Yes" : "No"), $is_cli);
    } else {
        echo '<tr>';
        echo '<td>' . h($recipe->recipe_id) . '</td>';
        echo '<td>' . h($recipe->title) . '</td>';
        echo '<td>' . h($original_path) . '</td>';
        echo '<td>' . h($thumb_path) . '</td>';
        echo '<td>' . h($optimized_path) . '</td>';
        echo '<td>' . h($banner_path) . '</td>';
        echo '<td>';
        echo 'Original: ' . ($original_exists ? '<span class="success">Yes</span>' : '<span class="error">No</span>') . '<br>';
        echo 'Thumb: ' . ($thumb_exists ? '<span class="success">Yes</span>' : '<span class="error">No</span>') . '<br>';
        echo 'Optimized: ' . ($optimized_exists ? '<span class="success">Yes</span>' : '<span class="error">No</span>') . '<br>';
        echo 'Banner: ' . ($banner_exists ? '<span class="success">Yes</span>' : '<span class="error">No</span>');
        echo '</td>';
        echo '</tr>';
    }
}

if (!$is_cli) {
    echo '</table>';
    echo '</body></html>';
}
?>
