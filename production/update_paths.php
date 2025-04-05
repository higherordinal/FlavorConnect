<?php
/**
 * FlavorConnect Path Update Script
 * 
 * This script updates all relative paths to initialize.php with absolute paths for Bluehost deployment.
 * Run this script once during the deployment process.
 */

// Configuration
$bluehost_absolute_path = '/home2/swbhdnmy/public_html/website_7135c1f5/private/core/initialize.php';

// Use the actual FlavorConnect_Live directory path
$root_dir = dirname(__DIR__) . '/'; // Get the parent directory of the current script
$public_dir = $root_dir . 'public';
$backup_dir = $root_dir . 'backup_' . date('Y-m-d_H-i-s');

// Create backup directory
if (!is_dir($backup_dir)) {
    mkdir($backup_dir, 0755, true);
    echo "Created backup directory: $backup_dir\n";
}

// Patterns to search for - more comprehensive to catch all variations
$patterns = [
    '~require(_once)?\\s*\\(\\s*[\\\'"]\\.\\.\/private\/core\/initialize\\.php[\\\'"]\\s*\\)\\s*;~', // Root level
    '~require(_once)?\\s*\\(\\s*[\\\'"]\\.\\.\\/\\.\\.\/private\/core\/initialize\\.php[\\\'"]\\s*\\)\\s*;~', // One level deep
    '~require(_once)?\\s*\\(\\s*[\\\'"]\\.\\.\/\\.\\.\/\\.\\.\/private\/core\/initialize\\.php[\\\'"]\\s*\\)\\s*;~', // Two levels deep
    '~require(_once)?\\s*\\(\\s*[\\\'"]\\.\\.\/\\.\\.\/\\.\\.\/\\.\\.\/private\/core\/initialize\\.php[\\\'"]\\s*\\)\\s*;~', // Three levels deep
    '~include(_once)?\\s*\\(\\s*[\\\'"]\\.\\.\/private\/core\/initialize\\.php[\\\'"]\\s*\\)\\s*;~', // Root level with include
    '~include(_once)?\\s*\\(\\s*[\\\'"]\\.\\.\\/\\.\\.\/private\/core\/initialize\\.php[\\\'"]\\s*\\)\\s*;~', // One level with include
    '~include(_once)?\\s*\\(\\s*[\\\'"]\\.\\.\/\\.\\.\/\\.\\.\/private\/core\/initialize\\.php[\\\'"]\\s*\\)\\s*;~', // Two levels with include
    '~include(_once)?\\s*\\(\\s*[\\\'"]\\.\\.\/\\.\\.\/\\.\\.\/\\.\\.\/private\/core\/initialize\\.php[\\\'"]\\s*\\)\\s*;~' // Three levels with include
];

// Replacement
$replacement = "require_once('$bluehost_absolute_path');";

// Counter for modified files
$modified_files = 0;
$processed_files = 0;

/**
 * Process a directory recursively
 * 
 * @param string $dir Directory to process
 * @return void
 */
function process_directory($dir, $patterns, $replacement, $backup_dir, &$modified_files, &$processed_files) {
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($files as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $processed_files++;
            $filepath = $file->getPathname();
            $content = file_get_contents($filepath);
            $original_content = $content;
            
            // Apply all patterns
            foreach ($patterns as $pattern) {
                $content = preg_replace($pattern, $replacement, $content);
            }
            
            // If content was modified, backup and save
            if ($content !== $original_content) {
                $modified_files++;
                
                // Create backup path
                $relative_path = str_replace(dirname($dir) . '/', '', $filepath);
                $backup_path = $backup_dir . '/' . $relative_path;
                $backup_dir_path = dirname($backup_path);
                
                // Create backup directory structure if it doesn't exist
                if (!is_dir($backup_dir_path)) {
                    mkdir($backup_dir_path, 0755, true);
                }
                
                // Backup original file
                file_put_contents($backup_path, $original_content);
                
                // Save modified content
                file_put_contents($filepath, $content);
                
                echo "Updated: $filepath\n";
            }
        }
    }
}

// Process the public directory
echo "Starting path update process...\n";
echo "Scanning for PHP files in: $public_dir\n";

// Check if the directory exists
if (!is_dir($public_dir)) {
    die("Error: Public directory not found at: $public_dir\nPlease make sure you're running this script from the production folder.\n");
}

// Process the directory
process_directory($public_dir, $patterns, $replacement, $backup_dir, $modified_files, $processed_files);

// Display the current directory for debugging
echo "\nCurrent directory: " . __DIR__ . "\n";
echo "Root directory: $root_dir\n";
echo "Public directory: $public_dir\n";

echo "Path update complete!\n";
echo "Processed $processed_files PHP files\n";
echo "Modified $modified_files files\n";
echo "Backups saved to: $backup_dir\n";

if ($modified_files > 0) {
    echo "SUCCESS: All paths have been updated to use the Bluehost absolute path.\n";
} else {
    echo "No files were modified. Please check your configuration.\n";
}
?>