<?php
/**
 * Bluehost Compatibility Script
 * 
 * This script provides compatibility functions and environment detection
 * for running the application on Bluehost shared hosting.
 */

/**
 * Detect if running on Bluehost
 * 
 * @return bool True if running on Bluehost, false otherwise
 */
function is_bluehost_environment() {
    // Check for Bluehost-specific environment variables or paths
    if (strpos($_SERVER['SERVER_NAME'] ?? '', 'bluehost') !== false) {
        return true;
    }
    
    // Check for Bluehost in the server signature
    if (strpos($_SERVER['SERVER_SOFTWARE'] ?? '', 'bluehost') !== false) {
        return true;
    }
    
    // Check for common Bluehost paths
    if (file_exists('/usr/local/cpanel') && file_exists('/home/content')) {
        return true;
    }
    
    return false;
}

/**
 * Get the appropriate ImageMagick path for Bluehost
 * 
 * @return string The path to ImageMagick on Bluehost
 */
function get_bluehost_imagemagick_path() {
    // Common paths for ImageMagick on Bluehost
    $possible_paths = [
        '/usr/bin/convert',
        '/usr/local/bin/convert',
        '/opt/ImageMagick/bin/convert'
    ];
    
    foreach ($possible_paths as $path) {
        if (file_exists($path) && is_executable($path)) {
            return $path;
        }
    }
    
    // Default path
    return '/usr/bin/convert';
}

/**
 * Check if ImageMagick is available on Bluehost
 * 
 * @return bool True if ImageMagick is available, false otherwise
 */
function check_bluehost_imagemagick() {
    $convert_path = get_bluehost_imagemagick_path();
    
    // Try to execute a simple command
    $output = [];
    $return_var = 0;
    
    @exec($convert_path . ' -version', $output, $return_var);
    
    return $return_var === 0;
}

/**
 * Set up the environment for Bluehost
 */
function setup_bluehost_environment() {
    // Increase memory limit if possible
    @ini_set('memory_limit', '256M');
    
    // Increase max execution time for image processing
    @ini_set('max_execution_time', 300);
    
    // Set appropriate file permissions
    @umask(0022);
}

// Automatically set up the environment if running on Bluehost
if (is_bluehost_environment()) {
    setup_bluehost_environment();
}
