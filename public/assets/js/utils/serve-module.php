<?php
// Set the correct MIME type for JavaScript modules
header('Content-Type: application/javascript');

// Get the requested file from the query string
$file = $_GET['file'] ?? '';

// Define the base directory for JavaScript files
$base_dir = __DIR__; // Current directory

// Only allow access to .js files in the current directory
if (empty($file) || !preg_match('/^[a-zA-Z0-9_-]+\.js$/', $file)) {
    http_response_code(404);
    echo "console.error('File not found or invalid filename');";
    exit;
}

// Construct the full path to the file
$full_path = $base_dir . '/' . $file;

// Check if the file exists
if (!file_exists($full_path)) {
    http_response_code(404);
    echo "console.error('File not found: " . htmlspecialchars($file) . "');";
    exit;
}

// Read and output the file content
readfile($full_path);
?>
