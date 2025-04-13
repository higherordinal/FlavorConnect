<?php
/**
 * FlavorConnect Bluehost Deployment Script
 * 
 * This script performs all necessary updates for deploying FlavorConnect to Bluehost:
 * 1. Updates all relative paths to use absolute paths for the Bluehost environment
 * 2. Fixes directory structure issues (handles the fact that 'public' is the document root)
 * 3. Replaces key files with Bluehost-optimized versions:
 *    - RecipeImageProcessor.class.php
 *    - Recipe.class.php
 *    - .htaccess files
 * 4. Updates configuration files for the production environment:
 *    - Replaces config.php with bluehost_config.php
 *    - Replaces api_config.php with bluehost_api_config.php
 * 5. Updates the url_for() function to use WWW_ROOT in production mode
 *
 * This script should be run on the local development environment before uploading
 * files to Bluehost, or directly on the Bluehost server after uploading.
 */

// Configuration
$bluehost_absolute_path = '/home2/swbhdnmy/public_html/website_7135c1f5';
$private_path = $bluehost_absolute_path . '/private';
$public_path = $bluehost_absolute_path; // On Bluehost, the document root itself is the public directory

// Use the actual FlavorConnect_Live directory path
$root_dir = dirname(__DIR__) . '/'; // Get the parent directory of the current script
$public_dir = $root_dir . 'public';
$backup_dir = $root_dir . 'backup_' . date('Y-m-d_H-i-s');

// Create backup directory if it doesn't exist
if (!is_dir($backup_dir)) {
    mkdir($backup_dir, 0755, true);
    echo "Created backup directory: $backup_dir\n";
}

// Patterns to search for - more comprehensive to catch all variations
$patterns = [
    // Direct initialize.php references
    '~(require|include)(_once)?\\s*\\(\\s*[\\\'"]\\.\\.\/private\/core\/initialize\\.php[\\\'"]\\s*\\)\\s*;~',
    '~(require|include)(_once)?\\s*\\(\\s*[\\\'"]\\.\\.\\/\\.\\.\/private\/core\/initialize\\.php[\\\'"]\\s*\\)\\s*;~',
    '~(require|include)(_once)?\\s*\\(\\s*[\\\'"]\\.\\.\/\\.\\.\/\\.\\.\/private\/core\/initialize\\.php[\\\'"]\\s*\\)\\s*;~',
    '~(require|include)(_once)?\\s*\\(\\s*[\\\'"]\\.\\.\/\\.\\.\/\\.\\.\/\\.\\.\/private\/core\/initialize\\.php[\\\'"]\\s*\\)\\s*;~',
    
    // API config references
    '~(require|include)(_once)?\\s*\\(\\s*[\\\'"]\\.\\.\/private\/config\/api_config\\.php[\\\'"]\\s*\\)\\s*;~',
    '~(require|include)(_once)?\\s*\\(\\s*[\\\'"]\\.\\.\\/\\.\\.\/private\/config\/api_config\\.php[\\\'"]\\s*\\)\\s*;~',
    '~(require|include)(_once)?\\s*\\(\\s*[\\\'"]\\.\\.\/\\.\\.\/\\.\\.\/private\/config\/api_config\\.php[\\\'"]\\s*\\)\\s*;~',
    '~(require|include)(_once)?\\s*\\(\\s*[\\\'"]\\.\\.\/\\.\\.\/\\.\\.\/\\.\\.\/private\/config\/api_config\\.php[\\\'"]\\s*\\)\\s*;~',
    
    // Config references
    '~(require|include)(_once)?\\s*\\(\\s*[\\\'"]\\.\\.\/private\/config\/config\\.php[\\\'"]\\s*\\)\\s*;~',
    '~(require|include)(_once)?\\s*\\(\\s*[\\\'"]\\.\\.\\/\\.\\.\/private\/config\/config\\.php[\\\'"]\\s*\\)\\s*;~',
    
    // Component includes are now handled directly in the code with environment detection
    // No need to replace recipe-card.php includes anymore
];

// Replacements
$replacements = [
    // Direct initialize.php references
    "require_once('$private_path/core/initialize.php');",
    "require_once('$private_path/core/initialize.php');",
    "require_once('$private_path/core/initialize.php');",
    "require_once('$private_path/core/initialize.php');",
    
    // API config references
    "require_once('$private_path/config/api_config.php');",
    "require_once('$private_path/config/api_config.php');",
    "require_once('$private_path/config/api_config.php');",
    "require_once('$private_path/config/api_config.php');",
    
    // Config references
    "require_once('$private_path/config/config.php');",
    "require_once('$private_path/config/config.php');",
    
    // Component includes are now handled directly in the code with environment detection
    // No need for recipe-card.php replacements anymore
];

// Counter for modified files
$modified_files = 0;
$processed_files = 0;

/**
 * Process a directory recursively to update paths
 * 
 * @param string $dir Directory to process
 * @param array $patterns Patterns to search for
 * @param array $replacements Replacements for patterns
 * @param string $backup_dir Directory to store backups
 * @param int &$modified_files Counter for modified files
 * @param int &$processed_files Counter for processed files
 */
function process_directory($dir, $patterns, $replacements, $backup_dir, &$modified_files, &$processed_files) {
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    
    foreach ($files as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $processed_files++;
            $file_path = $file->getRealPath();
            $file_content = file_get_contents($file_path);
            $original_content = $file_content;
            
            // Apply all patterns and replacements
            for ($i = 0; $i < count($patterns); $i++) {
                $file_content = preg_replace($patterns[$i], $replacements[$i], $file_content);
            }
            
            // If content changed, create backup and update file
            if ($file_content !== $original_content) {
                $modified_files++;
                
                // Create backup
                $relative_path = str_replace(dirname($dir) . '/', '', $file_path);
                $backup_path = $backup_dir . '/' . $relative_path;
                $backup_dir_path = dirname($backup_path);
                
                if (!is_dir($backup_dir_path)) {
                    mkdir($backup_dir_path, 0755, true);
                }
                
                file_put_contents($backup_path, $original_content);
                file_put_contents($file_path, $file_content);
                
                echo "Updated: $file_path\n";
            }
        }
    }
}

// Additional patterns to fix directory structure issues
$structure_patterns = [
    // Fix paths that assume a separate 'public' directory
    '~PUBLIC_PATH\\s*\\.\\s*[\\\'\"]\\/assets~' => '$public_path . "/assets',
    '~\\$upload_dir\\s*=\\s*PUBLIC_PATH\\s*\\.\\s*[\\\'\"]\\/assets\\/uploads\\/recipes[\\\'\"];~' => '$upload_dir = "$public_path/assets/uploads/recipes";',
];

// Additional patterns for fixing structure-specific issues
// These patterns are not used currently but kept for reference

// Start the update process
echo "Starting comprehensive path update process...\n";
echo "Scanning for PHP files in: $public_dir\n";

// Check if the directory exists
if (!is_dir($public_dir)) {
    die("Error: Public directory not found at: $public_dir\nPlease make sure you're running this script from the production folder.\n");
}

// Process the directory for path updates
process_directory($public_dir, $patterns, $replacements, $backup_dir, $modified_files, $processed_files);

// Replace RecipeImageProcessor class with the Bluehost-optimized version
echo "\nReplacing RecipeImageProcessor class with Bluehost-optimized version...\n";
$production_file = __DIR__ . '/RecipeImageProcessor.live.class.php';
$target_file = $root_dir . 'private/classes/RecipeImageProcessor.class.php';

if (file_exists($production_file)) {
    // Create backup of original file
    $backup_file = $backup_dir . '/private/classes/RecipeImageProcessor.class.php';
    $backup_dir_path = dirname($backup_file);
    
    if (!is_dir($backup_dir_path)) {
        mkdir($backup_dir_path, 0755, true);
    }
    
    if (file_exists($target_file)) {
        // Backup the original file
        copy($target_file, $backup_file);
        echo "Original RecipeImageProcessor.class.php backed up to: $backup_file\n";
    }
    
    // Copy the production-ready version to replace the original
    if (copy($production_file, $target_file)) {
        echo "Successfully replaced RecipeImageProcessor.class.php with Bluehost-optimized version!\n";
    } else {
        echo "Failed to replace RecipeImageProcessor.class.php.\n";
    }
} else {
    echo "Error: Production RecipeImageProcessor.class.php file not found at: $production_file\n";
}

// Replace Recipe class with the Bluehost-optimized version
echo "\nReplacing Recipe class with Bluehost-optimized version...\n";
$production_file = __DIR__ . '/Recipe.live.class.php';
$target_file = $root_dir . 'private/classes/Recipe.class.php';

if (file_exists($production_file)) {
    // Create backup of original file
    $backup_file = $backup_dir . '/private/classes/Recipe.class.php';
    $backup_dir_path = dirname($backup_file);
    
    if (!is_dir($backup_dir_path)) {
        mkdir($backup_dir_path, 0755, true);
    }
    
    if (file_exists($target_file)) {
        // Backup the original file
        copy($target_file, $backup_file);
        echo "Original Recipe.class.php backed up to: $backup_file\n";
    }
    
    // Copy the production-ready version to replace the original
    if (copy($production_file, $target_file)) {
        echo "Successfully replaced Recipe.class.php with Bluehost-optimized version!\n";
    } else {
        echo "Failed to replace Recipe.class.php.\n";
    }
} else {
    echo "Error: Production Recipe.class.php file not found at: $production_file\n";
}

// Replace .htaccess files with the Bluehost-optimized versions
echo "\nReplacing .htaccess files with Bluehost-optimized versions...\n";

// Main .htaccess file
$production_htaccess = __DIR__ . '/.bluehost-main-htaccess';
$target_htaccess = $public_dir . '/.htaccess';

if (file_exists($production_htaccess)) {
    // Create backup of original file
    $backup_htaccess = $backup_dir . '/public/.htaccess';
    $backup_dir_path = dirname($backup_htaccess);
    
    if (!is_dir($backup_dir_path)) {
        mkdir($backup_dir_path, 0755, true);
    }
    
    if (file_exists($target_htaccess)) {
        // Backup the original file
        copy($target_htaccess, $backup_htaccess);
        echo "Original main .htaccess backed up to: $backup_htaccess\n";
    }
    
    // Copy the production-ready version to replace the original
    if (copy($production_htaccess, $target_htaccess)) {
        echo "Successfully replaced main .htaccess with Bluehost-optimized version!\n";
    } else {
        echo "Failed to replace main .htaccess.\n";
    }
} else {
    echo "Error: Production main .htaccess file not found at: $production_htaccess\n";
}

// API .htaccess file
$production_api_htaccess = __DIR__ . '/.bluehost-api-htaccess';
$target_api_htaccess = $public_dir . '/api/.htaccess';

if (file_exists($production_api_htaccess)) {
    // Create backup of original file if it exists
    if (file_exists($target_api_htaccess)) {
        $backup_api_htaccess = $backup_dir . '/public/api/.htaccess';
        $backup_dir_path = dirname($backup_api_htaccess);
        
        if (!is_dir($backup_dir_path)) {
            mkdir($backup_dir_path, 0755, true);
        }
        
        copy($target_api_htaccess, $backup_api_htaccess);
        echo "Original API .htaccess backed up to: $backup_api_htaccess\n";
    }
    
    // Make sure the api directory exists
    $api_dir = $public_dir . '/api';
    if (!is_dir($api_dir)) {
        mkdir($api_dir, 0755, true);
        echo "Created API directory: $api_dir\n";
    }
    
    // Copy the production-ready version to replace the original
    if (copy($production_api_htaccess, $target_api_htaccess)) {
        echo "Successfully replaced API .htaccess with Bluehost-optimized version!\n";
    } else {
        echo "Failed to replace API .htaccess.\n";
    }
} else {
    echo "Error: Production API .htaccess file not found at: $production_api_htaccess\n";
}

// Replace api_config.php with the Bluehost-optimized version
echo "\nReplacing api_config.php with Bluehost-optimized version...\n";
$production_api_config = __DIR__ . '/bluehost_api_config.php';
$target_api_config = $root_dir . 'private/config/api_config.php';

if (file_exists($production_api_config)) {
    // Create backup of original file
    $backup_api_config = $backup_dir . '/private/config/api_config.php';
    $backup_dir_path = dirname($backup_api_config);
    
    if (!is_dir($backup_dir_path)) {
        mkdir($backup_dir_path, 0755, true);
    }
    
    if (file_exists($target_api_config)) {
        // Backup the original file
        copy($target_api_config, $backup_api_config);
        echo "Original api_config.php backed up to: $backup_api_config\n";
    }
    
    // Copy the production-ready version to replace the original
    if (copy($production_api_config, $target_api_config)) {
        echo "Successfully replaced api_config.php with Bluehost-optimized version!\n";
    } else {
        echo "Failed to replace api_config.php.\n";
    }
} else {
    echo "Error: Production api_config.php file not found at: $production_api_config\n";
}

// Replace config.php with the Bluehost-optimized version
echo "\nReplacing config.php with Bluehost-optimized version...\n";
$production_bluehost_config = __DIR__ . '/bluehost_config.php';
$target_config = $root_dir . 'private/config/config.php';
$target_bluehost_config = $root_dir . 'private/config/bluehost_config.php';

if (file_exists($production_bluehost_config)) {
    // Create backup of original config.php
    if (file_exists($target_config)) {
        $backup_config = $backup_dir . '/private/config/config.php';
        $backup_dir_path = dirname($backup_config);
        
        if (!is_dir($backup_dir_path)) {
            mkdir($backup_dir_path, 0755, true);
        }
        
        copy($target_config, $backup_config);
        echo "Original config.php backed up to: $backup_config\n";
    }
    
    // Copy the production-ready version to replace the original
    if (copy($production_bluehost_config, $target_config)) {
        echo "Successfully replaced config.php with bluehost_config.php!\n";
        
        // Also copy to bluehost_config.php for reference
        if (copy($production_bluehost_config, $target_bluehost_config)) {
            echo "Successfully copied bluehost_config.php to private/config/bluehost_config.php for reference\n";
        } else {
            echo "Note: Could not create a reference copy at private/config/bluehost_config.php\n";
        }
    } else {
        echo "Failed to replace config.php.\n";
    }
} else {
    echo "Error: Production bluehost_config.php file not found at: $production_bluehost_config\n";
}

// No need to update initialize.php or config.php references since we're directly replacing the config files
// and keeping the same filenames (config.php). This maintains compatibility with existing code.

// Update url_for function to use WWW_ROOT in production mode
echo "\nUpdating url_for function to use WWW_ROOT in production mode...\n";
$core_utilities_file = $root_dir . 'private/core/core_utilities.php';

if (file_exists($core_utilities_file)) {
    // Create backup of original file
    $backup_core_utilities = $backup_dir . '/private/core/core_utilities.php';
    $backup_dir_path = dirname($backup_core_utilities);
    
    if (!is_dir($backup_dir_path)) {
        mkdir($backup_dir_path, 0755, true);
    }
    
    copy($core_utilities_file, $backup_core_utilities);
    echo "Original core_utilities.php backed up to: $backup_core_utilities\n";
    
    // Read the file content
    $content = file_get_contents($core_utilities_file);
    
    // Check if the production environment condition is already present
    if (strpos($content, "ENVIRONMENT === 'production' && defined('WWW_ROOT')") === false) {
        // Find the position to insert the new code
        $search = "  // For Docker and other environments\n  return \$base_url . \$script_path;";
        $replace = "  // For production environment (Bluehost)\n  if (ENVIRONMENT === 'production' && defined('WWW_ROOT')) {\n    return WWW_ROOT . \$script_path;\n  }\n\n  // For Docker and other environments\n  return \$base_url . \$script_path;";
        
        // Replace the content
        $new_content = str_replace($search, $replace, $content);
        
        // Only update if changes were made
        if ($new_content !== $content) {
            // Write the updated content back to the file
            if (file_put_contents($core_utilities_file, $new_content)) {
                echo "Successfully updated url_for function to use WWW_ROOT in production mode!\n";
                $modified_files++;
            } else {
                echo "Failed to update url_for function.\n";
            }
        } else {
            echo "No changes needed for url_for function.\n";
        }
    } else {
        echo "url_for function already uses WWW_ROOT in production mode.\n";
    }
} else {
    echo "Error: core_utilities.php not found at: $core_utilities_file\n";
}

// Display the current directory for debugging
echo "\nCurrent directory: " . __DIR__ . "\n";
echo "Root directory: $root_dir\n";
echo "Public directory: $public_dir\n";
echo "Server public path: $public_path\n";

echo "\nPath update complete!\n";
echo "Processed $processed_files PHP files\n";
echo "Modified $modified_files files\n";
echo "Backups saved to: $backup_dir\n";

echo "\nDEPLOYMENT INSTRUCTIONS:\n";
echo "1. Upload all files to the server\n";
echo "2. Run this script on the server to prepare the application for Bluehost\n";
echo "3. Verify that the site is working correctly, including image uploads and display\n";
echo "4. If you encounter any issues, check the error logs in private/logs/\n";

if ($modified_files > 0) {
    echo "\nSUCCESS: All paths have been updated to use the Bluehost absolute path.\n";
} else {
    echo "\nNo files were modified. Please check your configuration.\n";
}
?>
