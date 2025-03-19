<?php
/**
 * Test script for WebP-only image processing
 * 
 * This script tests the RecipeImageProcessor class to ensure that only WebP files
 * are created and the original image is deleted after processing.
 */

// Define base paths based on environment
$is_docker = file_exists('/.dockerenv');
$base_path = $is_docker ? '/var/www/html' : __DIR__ . '/..';
$private_path = $base_path . '/private';
$public_path = $base_path . '/public';

// Include initialization file
require_once($private_path . '/core/initialize.php');

// Set up test directory
$test_dir = $public_path . '/uploads/test-webp-only/';
if (!is_dir($test_dir)) {
    mkdir($test_dir, 0755, true);
}

// Function to download a test image if it doesn't exist
function download_test_image($url, $save_path) {
    if (!file_exists($save_path)) {
        $image_data = file_get_contents($url);
        if ($image_data !== false) {
            file_put_contents($save_path, $image_data);
            return true;
        }
        return false;
    }
    return true;
}

// Function to format file size
function format_file_size($size) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $i = 0;
    while ($size >= 1024 && $i < count($units) - 1) {
        $size /= 1024;
        $i++;
    }
    return round($size, 2) . ' ' . $units[$i];
}

// Function to output results in a format suitable for both CLI and web
function output($message, $is_cli) {
    if ($is_cli) {
        echo $message . PHP_EOL;
    } else {
        echo $message . '<br>';
    }
}

// Detect if running in CLI or web
$is_cli = (php_sapi_name() === 'cli');

// Set up output formatting
if (!$is_cli) {
    echo '<html><head><title>WebP-Only Test</title>';
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
    echo '<h1>WebP-Only Image Processing Test</h1>';
}

// Header
output("WebP-Only Image Processing Test", $is_cli);
output("==============================", $is_cli);

// Test images
$test_images = [
    [
        'url' => 'https://source.unsplash.com/random/1200x800?food',
        'filename' => 'test_food.jpg'
    ],
    [
        'url' => 'https://source.unsplash.com/random/1200x800?recipe',
        'filename' => 'test_recipe.jpg'
    ]
];

// Download test images
foreach ($test_images as $image) {
    $image_path = $test_dir . $image['filename'];
    if (download_test_image($image['url'], $image_path)) {
        output("Downloaded test image: " . $image['filename'], $is_cli);
    } else {
        output("Failed to download test image: " . $image['filename'], $is_cli);
    }
}

// Create RecipeImageProcessor instance
$processor = new RecipeImageProcessor();

// Process each test image
if (!$is_cli) {
    echo '<table>';
    echo '<tr><th>Original Image</th><th>Processed Files</th><th>Original Deleted</th></tr>';
}

foreach ($test_images as $image) {
    $image_path = $test_dir . $image['filename'];
    $filename = pathinfo($image['filename'], PATHINFO_FILENAME);
    
    // Process the image
    $result = $processor->processRecipeImage($image_path, $test_dir, $filename);
    
    // Check if original file exists (it should be deleted)
    $original_exists = file_exists($image_path);
    
    // Check if WebP files exist
    $thumb_path = $test_dir . $filename . '_thumb.webp';
    $optimized_path = $test_dir . $filename . '_optimized.webp';
    $banner_path = $test_dir . $filename . '_banner.webp';
    
    $thumb_exists = file_exists($thumb_path);
    $optimized_exists = file_exists($optimized_path);
    $banner_exists = file_exists($banner_path);
    
    // Delete the original file if it still exists (for testing purposes)
    if ($original_exists) {
        unlink($image_path);
    }
    
    if ($is_cli) {
        output("", $is_cli);
        output("Image: " . $image['filename'], $is_cli);
        output("Processing result: " . ($result ? "Success" : "Failed"), $is_cli);
        output("Original file deleted: " . (!$original_exists ? "Yes" : "No"), $is_cli);
        output("Thumbnail WebP created: " . ($thumb_exists ? "Yes (" . format_file_size(filesize($thumb_path)) . ")" : "No"), $is_cli);
        output("Optimized WebP created: " . ($optimized_exists ? "Yes (" . format_file_size(filesize($optimized_path)) . ")" : "No"), $is_cli);
        output("Banner WebP created: " . ($banner_exists ? "Yes (" . format_file_size(filesize($banner_path)) . ")" : "No"), $is_cli);
    } else {
        echo '<tr>';
        echo '<td>' . $image['filename'] . '</td>';
        echo '<td>';
        if ($thumb_exists) echo 'Thumbnail: ' . format_file_size(filesize($thumb_path)) . '<br>';
        if ($optimized_exists) echo 'Optimized: ' . format_file_size(filesize($optimized_path)) . '<br>';
        if ($banner_exists) echo 'Banner: ' . format_file_size(filesize($banner_path)) . '<br>';
        echo '</td>';
        echo '<td>' . (!$original_exists ? '<span class="success">Yes</span>' : '<span class="error">No</span>') . '</td>';
        echo '</tr>';
    }
}

if (!$is_cli) {
    echo '</table>';
    
    // Add a section to display any errors
    if ($processor->hasErrors()) {
        echo '<h2>Errors:</h2>';
        echo '<ul>';
        foreach ($processor->getErrors() as $error) {
            echo '<li class="error">' . htmlspecialchars($error) . '</li>';
        }
        echo '</ul>';
    }
    
    echo '</body></html>';
}
