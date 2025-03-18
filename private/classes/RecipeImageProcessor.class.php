<?php

/**
 * RecipeImageProcessor Class
 * 
 * A comprehensive image processing utility for recipe images in the FlavorConnect application.
 * This class handles various image operations including resizing, cropping, optimization, and format conversion.
 * It supports both ImageMagick and GD library as processing engines, with automatic fallback from ImageMagick to GD
 * if ImageMagick is not available on the server.
 * 
 * Features:
 * - Creates thumbnails, optimized images, and banner images for recipes
 * - Converts images to WebP format for better compression and quality
 * - Supports both Windows and Linux/Unix environments
 * - Provides comprehensive error handling and reporting
 * - Implements method chaining for fluent API usage
 * 
 * @author FlavorConnect Development Team
 */
class RecipeImageProcessor {
    /** @var string Path to the source image */
    private $source_path;
    
    /** @var string Path where processed image should be saved */
    private $destination_path;
    
    /** @var bool Whether the current environment is Windows */
    private $is_windows;
    
    /** @var string Path to the ImageMagick convert executable */
    private $convert_path;
    
    /** @var array Collection of error messages that occurred during processing */
    private $errors = [];
    
    /**
     * Constructor for RecipeImageProcessor
     * 
     * Initializes a new instance of the RecipeImageProcessor class with optional source and destination paths.
     * Automatically detects the operating system and configures the appropriate ImageMagick path.
     * 
     * @param string $source_path Path to the source image
     * @param string $destination_path Path where processed image should be saved
     */
    public function __construct($source_path = '', $destination_path = '') {
        $this->source_path = $source_path;
        $this->destination_path = $destination_path;
        $this->is_windows = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
        
        // Set the convert path based on the environment
        if ($this->is_windows) {
            // For Windows, use the locally installed ImageMagick
            // Update this path to match your ImageMagick installation
            $this->convert_path = 'magick convert';
        } else {
            // For Bluehost or other Linux servers
            $this->convert_path = '/usr/bin/convert';
        }
    }
    
    /**
     * Set the source image path
     * 
     * Updates the source path for the image to be processed.
     * 
     * @param string $path Path to the source image
     * @return RecipeImageProcessor Returns this instance for method chaining
     */
    public function setSourcePath($path) {
        $this->source_path = $path;
        return $this;
    }
    
    /**
     * Set the destination image path
     * 
     * Updates the destination path where the processed image will be saved.
     * 
     * @param string $path Path where processed image should be saved
     * @return RecipeImageProcessor Returns this instance for method chaining
     */
    public function setDestinationPath($path) {
        $this->destination_path = $path;
        return $this;
    }
    
    /**
     * Check if ImageMagick is available on the system
     * 
     * Tests if the ImageMagick convert command is accessible and functional
     * by attempting to run a simple version command.
     * 
     * @return bool True if ImageMagick is available and functional, false otherwise
     */
    public function isImageMagickAvailable() {
        // Check if the Imagick extension is loaded
        if (extension_loaded('imagick')) {
            return true;
        }
        
        // Check if the command-line ImageMagick is available
        $command = $this->getImageMagickCommand();
        if (!empty($command)) {
            // Try to execute a simple command to verify it works
            $output = [];
            $return_var = 0;
            
            // Check for Bluehost environment
            if ($this->isBluehostEnvironment()) {
                // Use the function from bluehost-compatibility.php if available
                if (function_exists('check_bluehost_imagemagick')) {
                    return check_bluehost_imagemagick();
                }
                
                // Fallback check for Bluehost
                exec($command . ' -version', $output, $return_var);
                if ($return_var !== 0) {
                    $this->errors[] = "ImageMagick command not found on Bluehost server";
                    return false;
                }
                return true;
            }
            // Check for Docker environment
            else if ($this->isDockerEnvironment()) {
                // In Docker, we should have 'convert' available
                exec('convert -version', $output, $return_var);
                if ($return_var !== 0) {
                    $this->errors[] = "ImageMagick 'convert' command not found in Docker container";
                    return false;
                }
                return true;
            } else if ($this->is_windows) {
                // On Windows, we need to check if the 'convert' command exists
                exec('where convert', $output, $return_var);
                if ($return_var !== 0) {
                    // Try with 'magick convert' as fallback for native Windows installations
                    exec('where magick', $output, $return_var);
                    if ($return_var !== 0) {
                        $this->errors[] = "ImageMagick command not found in PATH";
                        return false;
                    }
                    
                    // Use 'magick convert' instead
                    $this->convert_path = 'magick convert';
                    exec('magick -version', $output, $return_var);
                    return $return_var === 0;
                }
                
                // Now try to run a version check with 'convert'
                exec('convert -version', $output, $return_var);
                return $return_var === 0;
            } else {
                // For non-Windows systems
                exec($command . ' -version', $output, $return_var);
                return $return_var === 0;
            }
        }
        
        return false;
    }
    
    /**
     * Check if GD library is available
     * 
     * @return bool True if GD is available, false otherwise
     */
    public function isGDAvailable() {
        // Check if GD extension is loaded
        if (!extension_loaded('gd')) {
            $this->errors[] = "GD library is not available";
            return false;
        }
        
        // Check if required GD functions exist
        $required_functions = ['imagecreatefromjpeg', 'imagecreatefrompng', 'imagecreatefromgif', 'imagecreatetruecolor', 'imagecopyresampled', 'imagedestroy'];
        $missing_functions = [];
        
        foreach ($required_functions as $function) {
            if (!function_exists($function)) {
                $missing_functions[] = $function;
            }
        }
        
        if (!empty($missing_functions)) {
            $this->errors[] = "Required GD functions are missing: " . implode(', ', $missing_functions);
            return false;
        }
        
        // Check if WebP support is available
        if (!function_exists('imagewebp')) {
            $this->errors[] = "GD library does not support WebP format";
            // We'll still return true since we can fall back to other formats
        }
        
        return true;
    }
    
    /**
     * Resize an image to the specified dimensions
     * 
     * Resizes the source image to match the target dimensions using ImageMagick.
     * Can maintain aspect ratio if specified.
     * 
     * @param string $source_path Path to the source image
     * @param string $destination_path Path where the resized image should be saved
     * @param int $width Target width in pixels
     * @param int $height Target height in pixels
     * @param bool $maintain_aspect_ratio Whether to maintain aspect ratio (true) or force exact dimensions (false)
     * @return bool True if resizing was successful, false otherwise
     */
    public function resize($source_path, $destination_path, $width, $height, $maintain_aspect_ratio = true) {
        if (empty($source_path) || empty($destination_path)) {
            $this->errors[] = "Source or destination path not set";
            return false;
        }
        
        if (!file_exists($source_path)) {
            $this->errors[] = "Source file does not exist: {$source_path}";
            return false;
        }
        
        // Create destination directory if it doesn't exist
        $destination_dir = dirname($destination_path);
        if (!is_dir($destination_dir)) {
            mkdir($destination_dir, 0755, true);
        }
        
        $resize_option = $maintain_aspect_ratio ? "\"{$width}x{$height}\"" : "\"{$width}x{$height}!\"";
        
        $command = "{$this->convert_path} \"{$source_path}\" -resize {$resize_option} \"{$destination_path}\"";
        
        exec($command, $output, $return_var);
        
        if ($return_var !== 0) {
            $this->errors[] = "Image resize failed with command: {$command}";
            return false;
        }
        
        return true;
    }
    
    /**
     * Crop an image to the specified dimensions
     * 
     * Crops the image to the exact dimensions specified, starting from the given coordinates.
     * If no coordinates are provided, cropping starts from the top-left corner (0,0).
     * 
     * @param int $width Target width in pixels
     * @param int $height Target height in pixels
     * @param int $x X coordinate of the top-left corner to start cropping from
     * @param int $y Y coordinate of the top-left corner to start cropping from
     * @return bool True if cropping was successful, false otherwise
     */
    public function crop($width, $height, $x = 0, $y = 0) {
        if (empty($this->source_path) || empty($this->destination_path)) {
            $this->errors[] = "Source or destination path not set";
            return false;
        }
        
        if (!file_exists($this->source_path)) {
            $this->errors[] = "Source file does not exist: {$this->source_path}";
            return false;
        }
        
        // Create destination directory if it doesn't exist
        $destination_dir = dirname($this->destination_path);
        if (!is_dir($destination_dir)) {
            mkdir($destination_dir, 0755, true);
        }
        
        $command = "{$this->convert_path} \"{$this->source_path}\" -crop {$width}x{$height}+{$x}+{$y} \"{$this->destination_path}\"";
        
        exec($command, $output, $return_var);
        
        if ($return_var !== 0) {
            $this->errors[] = "Image crop failed with command: {$command}";
            return false;
        }
        
        return true;
    }
    
    /**
     * Optimize an image for web (reduce file size while maintaining quality)
     * 
     * Processes the image to reduce file size while maintaining acceptable visual quality.
     * Useful for creating web-optimized versions of images.
     * 
     * @param string $source_path Path to the source image
     * @param string $destination_path Path where the optimized image should be saved
     * @param int $quality JPEG quality (0-100), where higher values mean better quality but larger file size
     * @return bool True if optimization was successful, false otherwise
     */
    public function optimize($source_path, $destination_path, $quality = 85) {
        if (empty($source_path) || empty($destination_path)) {
            $this->errors[] = "Source or destination path not set";
            return false;
        }
        
        if (!file_exists($source_path)) {
            $this->errors[] = "Source file does not exist: {$source_path}";
            return false;
        }
        
        // Create destination directory if it doesn't exist
        $destination_dir = dirname($destination_path);
        if (!is_dir($destination_dir)) {
            mkdir($destination_dir, 0755, true);
        }
        
        $command = "{$this->convert_path} \"{$source_path}\" -strip -interlace Plane -quality {$quality} \"{$destination_path}\"";
        
        exec($command, $output, $return_var);
        
        if ($return_var !== 0) {
            $this->errors[] = "Image optimization failed with command: {$command}";
            return false;
        }
        
        return true;
    }
    
    /**
     * Create a thumbnail from the source image
     * 
     * Generates a thumbnail version of the source image with the specified dimensions.
     * Can either crop to exact dimensions or fit within dimensions while maintaining aspect ratio.
     * 
     * @param int $width Thumbnail width in pixels
     * @param int $height Thumbnail height in pixels
     * @param bool $crop Whether to crop the image to fit the exact dimensions (true) or just resize to fit within dimensions (false)
     * @return bool True if thumbnail creation was successful, false otherwise
     */
    public function createThumbnail($width, $height, $crop = true) {
        if (empty($this->source_path) || empty($this->destination_path)) {
            $this->errors[] = "Source or destination path not set";
            return false;
        }
        
        if (!file_exists($this->source_path)) {
            $this->errors[] = "Source file does not exist: {$this->source_path}";
            return false;
        }
        
        // Create destination directory if it doesn't exist
        $destination_dir = dirname($this->destination_path);
        if (!is_dir($destination_dir)) {
            mkdir($destination_dir, 0755, true);
        }
        
        if ($crop) {
            // First resize to fill the dimensions, then crop to exact size
            $command = "{$this->convert_path} \"{$this->source_path}\" -resize {$width}x{$height}^ -gravity center -extent {$width}x{$height} \"{$this->destination_path}\"";
        } else {
            // Just resize to fit within the dimensions
            $command = "{$this->convert_path} \"{$this->source_path}\" -resize {$width}x{$height} \"{$this->destination_path}\"";
        }
        
        exec($command, $output, $return_var);
        
        if ($return_var !== 0) {
            $this->errors[] = "Thumbnail creation failed with command: {$command}";
            return false;
        }
        
        return true;
    }
    
    /**
     * Process a recipe image to create thumbnail, optimized, and banner versions
     * 
     * Creates three versions of the image:
     * 1. Thumbnail (300x200px)
     * 2. Optimized (1000x750px)
     * 3. Banner (1200x400px)
     * 
     * @param string $source_path Path to the source image
     * @param string $destination_dir Directory to save processed images to
     * @param string $filename Filename without extension
     * @return bool True if processing was successful, false otherwise
     */
    public function processRecipeImage($source_path, $destination_dir, $filename) {
        $this->errors = []; // Reset errors
        
        // Check if source file exists
        if (!file_exists($source_path)) {
            $this->errors[] = "Source file does not exist: {$source_path}";
            return false;
        }
        
        // Ensure destination directory exists and is writable
        if (!is_dir($destination_dir)) {
            if (!mkdir($destination_dir, 0755, true)) {
                $this->errors[] = "Failed to create destination directory: {$destination_dir}";
                return false;
            }
        }
        
        if (!is_writable($destination_dir)) {
            $this->errors[] = "Destination directory is not writable: {$destination_dir}";
            return false;
        }
        
        // Ensure destination directory ends with a slash
        if (substr($destination_dir, -1) !== '/' && substr($destination_dir, -1) !== '\\') {
            $destination_dir .= '/';
        }
        
        // Define paths for processed images
        $thumbnail_path = $destination_dir . $filename . '_thumb.webp';
        $optimized_path = $destination_dir . $filename . '_optimized.webp';
        $banner_path = $destination_dir . $filename . '_banner.webp';
        
        // Check if ImageMagick is available
        $imagemagick_available = $this->isImageMagickAvailable();
        
        // Check if GD is available
        $gd_available = $this->isGDAvailable();
        
        // If neither ImageMagick nor GD is available, create simple copies
        if (!$imagemagick_available && !$gd_available) {
            $this->errors[] = "Warning: Neither ImageMagick nor GD is available. Using simple file copy instead of image processing.";
            
            // Just copy the original file for each version
            if (!copy($source_path, $thumbnail_path)) {
                $this->errors[] = "Failed to create thumbnail copy";
            }
            
            if (!copy($source_path, $optimized_path)) {
                $this->errors[] = "Failed to create optimized copy";
            }
            
            if (!copy($source_path, $banner_path)) {
                $this->errors[] = "Failed to create banner copy";
            }
            
            // Return true even though we didn't process the images
            // This allows the application to continue working with the original images
            return true;
        }
        
        // Process with ImageMagick if available
        if ($imagemagick_available) {
            $this->destination_path = $thumbnail_path;
            $thumbnail_result = $this->resizeToWebP($source_path, $thumbnail_path, 300, 200, true);
            
            $this->destination_path = $optimized_path;
            $optimized_result = $this->resizeToWebP($source_path, $optimized_path, 1000, 750, true);
            
            $this->destination_path = $banner_path;
            $banner_result = $this->resizeToWebP($source_path, $banner_path, 1200, 400, true);
            
            // Return true if all operations were successful
            return $thumbnail_result && $optimized_result && $banner_result;
        }
        
        // Fall back to GD if ImageMagick is not available
        if ($gd_available) {
            $thumbnail_result = $this->resizeWithGD($source_path, $thumbnail_path, 300, 200, true);
            $optimized_result = $this->resizeWithGD($source_path, $optimized_path, 1000, 750, true);
            $banner_result = $this->resizeWithGD($source_path, $banner_path, 1200, 400, true);
            
            // Return true if all operations were successful
            return $thumbnail_result && $optimized_result && $banner_result;
        }
        
        // This should never be reached, but just in case
        $this->errors[] = "No image processing method available";
        return false;
    }
    
    /**
     * Process an image using GD library and save as WebP
     * 
     * This is a fallback method when ImageMagick is not available.
     * 
     * @param string $source_path Path to source image
     * @param string $destination_path Path to save WebP image
     * @param int $width Target width in pixels
     * @param int $height Target height in pixels
     * @return bool True if processing was successful
     */
    protected function processWithGDToWebP($source_path, $destination_path, $width = 800, $height = 600) {
        if (!function_exists('imagecreatefromjpeg') || !function_exists('imagecreatefrompng')) {
            $this->errors[] = "GD library is not available or missing required functions";
            return false;
        }
        
        // Get image info
        $image_info = getimagesize($source_path);
        if ($image_info === false) {
            $this->errors[] = "Could not get image information from source file";
            return false;
        }
        
        $mime_type = $image_info['mime'];
        
        // Create source image based on type
        switch ($mime_type) {
            case 'image/jpeg':
                $source_image = imagecreatefromjpeg($source_path);
                break;
            case 'image/png':
                $source_image = imagecreatefrompng($source_path);
                break;
            case 'image/webp':
                if (function_exists('imagecreatefromwebp')) {
                    $source_image = imagecreatefromwebp($source_path);
                } else {
                    $this->errors[] = "WebP support is not available in GD library";
                    return false;
                }
                break;
            default:
                $this->errors[] = "Unsupported image type: {$mime_type}";
                return false;
        }
        
        if (!$source_image) {
            $this->errors[] = "Failed to create source image from file";
            return false;
        }
        
        // Get original dimensions
        $original_width = imagesx($source_image);
        $original_height = imagesy($source_image);
        
        // Calculate new dimensions while maintaining aspect ratio
        $ratio_orig = $original_width / $original_height;
        
        if ($width / $height > $ratio_orig) {
            $new_width = $height * $ratio_orig;
            $new_height = $height;
        } else {
            $new_width = $width;
            $new_height = $width / $ratio_orig;
        }
        
        // Create a new true color image
        $new_image = imagecreatetruecolor($new_width, $new_height);
        
        // Preserve transparency for PNG images
        if ($mime_type === 'image/png') {
            imagecolortransparent($new_image, imagecolorallocate($new_image, 0, 0, 0));
            imagealphablending($new_image, false);
            imagesavealpha($new_image, true);
        }
        
        // Resize the image
        imagecopyresampled($new_image, $source_image, 0, 0, 0, 0, $new_width, $new_height, $original_width, $original_height);
        
        // Save as WebP
        $result = false;
        if (function_exists('imagewebp')) {
            // Create directory if it doesn't exist
            $destination_dir = dirname($destination_path);
            if (!is_dir($destination_dir)) {
                if (!mkdir($destination_dir, 0755, true)) {
                    $this->errors[] = "Failed to create destination directory: {$destination_dir}";
                    imagedestroy($source_image);
                    imagedestroy($new_image);
                    return false;
                }
            }
            
            // Check if directory is writable
            if (!is_writable($destination_dir)) {
                $this->errors[] = "Destination directory is not writable: {$destination_dir}";
                imagedestroy($source_image);
                imagedestroy($new_image);
                return false;
            }
            
            // Save with 80% quality
            $result = imagewebp($new_image, $destination_path, 80);
            
            if (!$result) {
                $this->errors[] = "Failed to save WebP image to {$destination_path}";
            }
        } else {
            $this->errors[] = "WebP support is not available in GD library";
        }
        
        // Free up memory
        imagedestroy($source_image);
        imagedestroy($new_image);
        
        return $result;
    }
    
    /**
     * Resize an image to the specified dimensions and convert to WebP format
     * 
     * Resizes the source image and converts it to WebP format in one operation.
     * WebP provides better compression and quality compared to JPEG and PNG.
     * 
     * @param string $source_path Path to the source image
     * @param string $destination_path Path to save the resized WebP image
     * @param int $width Width of the resized image in pixels
     * @param int $height Height of the resized image in pixels
     * @param bool $crop Whether to crop the image to fit the exact dimensions (true) or just resize (false)
     * @return bool True if resizing and conversion were successful, false otherwise
     */
    private function resizeToWebP($source_path, $destination_path, $width, $height, $crop = false) {
        $quality = 90; // WebP quality (0-100)
        
        if ($crop) {
            $command = $this->getImageMagickCommand() . " \"{$source_path}\" -resize {$width}x{$height}^ -gravity center -extent {$width}x{$height} -quality {$quality} -define webp:lossless=false \"{$destination_path}\"";
        } else {
            $command = $this->getImageMagickCommand() . " \"{$source_path}\" -resize {$width}x{$height} -quality {$quality} -define webp:lossless=false \"{$destination_path}\"";
        }
        
        exec($command, $output, $return_var);
        
        if ($return_var !== 0) {
            $this->errors[] = "Error resizing image to WebP: " . implode("\n", $output);
            return false;
        }
        
        return true;
    }
    
    /**
     * Optimize an image and convert to WebP format
     * 
     * Creates an optimized version of the source image in WebP format.
     * Maintains a reasonable quality while reducing file size.
     * 
     * @param string $source_path Path to the source image
     * @param string $destination_path Path to save the optimized WebP image
     * @return bool True if optimization and conversion were successful, false otherwise
     */
    private function optimizeToWebP($source_path, $destination_path) {
        $quality = 90; // WebP quality (0-100)
        $width = 1000; // Max width for optimized image
        
        $command = $this->getImageMagickCommand() . " \"{$source_path}\" -resize {$width}x -quality {$quality} -define webp:lossless=false \"{$destination_path}\"";
        
        exec($command, $output, $return_var);
        
        if ($return_var !== 0) {
            $this->errors[] = "Error optimizing image to WebP: " . implode("\n", $output);
            return false;
        }
        
        return true;
    }
    
    /**
     * Delete recipe images and their processed versions
     * 
     * Removes the original image and all processed versions (thumbnail, optimized, banner)
     * 
     * @param string $directory Directory containing the images
     * @param string $filename Filename of the original image
     * @return bool True if all files were deleted or didn't exist, false if any deletion failed
     */
    public function deleteRecipeImages($directory, $filename) {
        // Ensure directory ends with a slash
        if (substr($directory, -1) !== '/' && substr($directory, -1) !== '\\') {
            $directory .= '/';
        }
        
        // Get the base filename without extension
        $path_parts = pathinfo($filename);
        $base_filename = $path_parts['filename'];
        
        // Define paths to all versions
        $original_path = $directory . $filename;
        $thumb_path = $directory . $base_filename . '_thumb.webp';
        $optimized_path = $directory . $base_filename . '_optimized.webp';
        $banner_path = $directory . $base_filename . '_banner.webp';
        
        // Delete original file
        $success = true;
        if (file_exists($original_path)) {
            if (!unlink($original_path)) {
                $this->errors[] = "Failed to delete original image: {$original_path}";
                $success = false;
            }
        }
        
        // Delete thumbnail
        if (file_exists($thumb_path)) {
            if (!unlink($thumb_path)) {
                $this->errors[] = "Failed to delete thumbnail image: {$thumb_path}";
                $success = false;
            }
        }
        
        // Delete optimized version
        if (file_exists($optimized_path)) {
            if (!unlink($optimized_path)) {
                $this->errors[] = "Failed to delete optimized image: {$optimized_path}";
                $success = false;
            }
        }
        
        // Delete banner version
        if (file_exists($banner_path)) {
            if (!unlink($banner_path)) {
                $this->errors[] = "Failed to delete banner image: {$banner_path}";
                $success = false;
            }
        }
        
        return $success;
    }
    
    /**
     * Handle image upload from a form and process it
     * 
     * Comprehensive method that handles the entire image upload and processing workflow:
     * - Validates the uploaded file
     * - Creates the target directory if it doesn't exist
     * - Deletes old images if a replacement is provided
     * - Moves the uploaded file to the target directory
     * - Processes the image to create thumbnail, optimized, and banner versions
     * 
     * @param array $file_data The $_FILES array element for the uploaded file
     * @param string $target_dir The directory where the file should be stored
     * @param string|null $old_filename The name of the old file to replace (if any)
     * @return array Structured response with keys: 'success' (bool), 'filename' (string|null), 'errors' (array)
     */
    public function handleImageUpload($file_data, $target_dir, $old_filename = null) {
        $this->errors = []; // Reset errors
        $result = [
            'success' => false,
            'filename' => null,
            'errors' => []
        ];
        
        // Validate file
        if (!isset($file_data) || empty($file_data) || $file_data['error'] !== UPLOAD_ERR_OK) {
            $error_message = $this->getUploadErrorMessage($file_data['error'] ?? UPLOAD_ERR_NO_FILE);
            $this->errors[] = "File upload error: " . $error_message;
            $result['errors'] = $this->errors;
            return $result;
        }
        
        // Validate file type
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $file_type = $file_data['type'];
        
        if (!in_array($file_type, $allowed_types)) {
            $this->errors[] = "Invalid file type. Allowed types: JPG, PNG, GIF, WebP";
            $result['errors'] = $this->errors;
            return $result;
        }
        
        // Validate file size (max 10MB)
        $max_size = 10 * 1024 * 1024; // 10MB in bytes
        if ($file_data['size'] > $max_size) {
            $this->errors[] = "File is too large. Maximum size is 10MB";
            $result['errors'] = $this->errors;
            return $result;
        }
        
        // Ensure target directory exists
        if (!is_dir($target_dir)) {
            if (!mkdir($target_dir, 0755, true)) {
                $this->errors[] = "Failed to create target directory: {$target_dir}";
                $result['errors'] = $this->errors;
                return $result;
            }
        }
        
        // Generate unique filename - always store original file with its original extension
        $file_extension = pathinfo($file_data['name'], PATHINFO_EXTENSION);
        $new_filename = uniqid('recipe_') . '.' . $file_extension;
        $target_path = $target_dir . '/' . $new_filename;
        
        // Delete old file if it exists
        if (!empty($old_filename)) {
            $this->deleteRecipeImages($target_dir, $old_filename);
        }
        
        // Move uploaded file to target directory
        if (!move_uploaded_file($file_data['tmp_name'], $target_path)) {
            $this->errors[] = "Failed to move uploaded file to target directory";
            $result['errors'] = $this->errors;
            return $result;
        }
        
        // Process the image - all processed versions will be WebP regardless of original format
        $process_result = $this->processRecipeImage($target_path, $target_dir, pathinfo($new_filename, PATHINFO_FILENAME));
        
        // If processing failed, add errors but don't fail the upload
        if (!$process_result) {
            $result['errors'] = $this->errors;
        }
        
        // Return success with the new filename
        $result['success'] = true;
        $result['filename'] = $new_filename;
        
        return $result;
    }
    
    /**
     * Get any errors that occurred during processing
     * 
     * Retrieves the collection of error messages that were recorded during image processing operations.
     * Useful for debugging and providing feedback to users.
     * 
     * @return array Array of error messages, empty if no errors occurred
     */
    public function getErrors() {
        return $this->errors;
    }
    
    /**
     * Check if there are any errors
     * 
     * Determines if any errors occurred during image processing operations.
     * 
     * @return bool True if there are errors, false if processing was error-free
     */
    public function hasErrors() {
        return !empty($this->errors);
    }
    
    /**
     * Check if running in a Docker environment
     * 
     * @return bool True if running in Docker, false otherwise
     */
    private function isDockerEnvironment() {
        // Check for Docker environment indicators
        if (file_exists('/.dockerenv')) {
            return true;
        }
        
        // Check for Docker cgroup
        $cgroup_content = @file_get_contents('/proc/self/cgroup');
        if ($cgroup_content !== false && strpos($cgroup_content, 'docker') !== false) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Check if running on Bluehost
     * 
     * @return bool True if running on Bluehost, false otherwise
     */
    private function isBluehostEnvironment() {
        // Use the function from bluehost-compatibility.php if available
        if (function_exists('is_bluehost_environment')) {
            return is_bluehost_environment();
        }
        
        // Fallback detection if the function is not available
        if (strpos($_SERVER['SERVER_NAME'] ?? '', 'bluehost') !== false) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Get the appropriate ImageMagick command for the current environment
     * 
     * @return string The ImageMagick command appropriate for the current environment
     */
    private function getImageMagickCommand() {
        // Check if running in Docker
        if ($this->isDockerEnvironment()) {
            // For Docker environments, use the Docker container's ImageMagick
            return 'convert';
        } else if ($this->isBluehostEnvironment()) {
            // For Bluehost, use the function from bluehost-compatibility.php if available
            if (function_exists('get_bluehost_imagemagick_path')) {
                return get_bluehost_imagemagick_path();
            }
            // Default Bluehost path
            return '/usr/bin/convert';
        } else if ($this->is_windows) {
            // For Windows, try both 'magick convert' and 'convert'
            $output = [];
            $return_var = 0;
            
            // First try 'magick'
            exec('where magick', $output, $return_var);
            if ($return_var === 0) {
                return 'magick convert';
            }
            
            // Then try 'convert'
            $output = [];
            exec('where convert', $output, $return_var);
            if ($return_var === 0) {
                return 'convert';
            }
            
            // Default to 'magick convert' as a fallback
            return 'magick convert';
        } else {
            // For Linux/Unix servers
            return '/usr/bin/convert';
        }
    }
    
    /**
     * Get a human-readable error message for PHP file upload error codes
     * 
     * @param int $error_code PHP file upload error code
     * @return string Human-readable error message
     */
    private function getUploadErrorMessage($error_code) {
        switch ($error_code) {
            case UPLOAD_ERR_INI_SIZE:
                return "The uploaded file exceeds the upload_max_filesize directive in php.ini";
            case UPLOAD_ERR_FORM_SIZE:
                return "The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form";
            case UPLOAD_ERR_PARTIAL:
                return "The uploaded file was only partially uploaded";
            case UPLOAD_ERR_NO_FILE:
                return "No file was uploaded";
            case UPLOAD_ERR_NO_TMP_DIR:
                return "Missing a temporary folder";
            case UPLOAD_ERR_CANT_WRITE:
                return "Failed to write file to disk";
            case UPLOAD_ERR_EXTENSION:
                return "File upload stopped by extension";
            default:
                return "Unknown upload error";
        }
    }
    
    /**
     * Resize an image using GD library
     * 
     * @param string $source_path Path to the source image
     * @param string $destination_path Path to save the resized image
     * @param int $width Target width in pixels
     * @param int $height Target height in pixels
     * @param bool $crop Whether to crop the image to fit the dimensions
     * @return bool True if successful, false otherwise
     */
    public function resizeWithGD($source_path, $destination_path, $width, $height, $crop = false) {
        // Check if source file exists
        if (!file_exists($source_path)) {
            $this->errors[] = "Source file does not exist: {$source_path}";
            return false;
        }
        
        // Get image info
        $info = getimagesize($source_path);
        if ($info === false) {
            $this->errors[] = "Failed to get image information for {$source_path}";
            return false;
        }
        
        // Create image resource based on file type
        $source_image = null;
        $mime = $info['mime'];
        
        switch ($mime) {
            case 'image/jpeg':
                $source_image = imagecreatefromjpeg($source_path);
                break;
            case 'image/png':
                $source_image = imagecreatefrompng($source_path);
                break;
            case 'image/gif':
                $source_image = imagecreatefromgif($source_path);
                break;
            case 'image/webp':
                $source_image = imagecreatefromwebp($source_path);
                break;
            default:
                $this->errors[] = "Unsupported image type: {$mime}";
                return false;
        }
        
        if (!$source_image) {
            $this->errors[] = "Failed to create image resource from {$source_path}";
            return false;
        }
        
        // Get original dimensions
        $original_width = imagesx($source_image);
        $original_height = imagesy($source_image);
        
        // Calculate new dimensions
        $new_width = $width;
        $new_height = $height;
        
        if (!$crop) {
            // Maintain aspect ratio
            $ratio_orig = $original_width / $original_height;
            
            if ($width / $height > $ratio_orig) {
                $new_width = $height * $ratio_orig;
            } else {
                $new_height = $width / $ratio_orig;
            }
        }
        
        // Create a new true color image
        $new_image = imagecreatetruecolor($new_width, $new_height);
        
        // Preserve transparency for PNG images
        if ($mime == 'image/png') {
            // Turn off alpha blending and set alpha flag
            imagealphablending($new_image, false);
            imagesavealpha($new_image, true);
        }
        
        // Resize the image
        imagecopyresampled(
            $new_image,
            $source_image,
            0, 0, 0, 0,
            $new_width, $new_height,
            $original_width, $original_height
        );
        
        // If cropping is needed and dimensions don't match the target
        if ($crop && ($new_width != $width || $new_height != $height)) {
            $cropped_image = imagecreatetruecolor($width, $height);
            
            // Preserve transparency for PNG
            if ($mime == 'image/png') {
                imagealphablending($cropped_image, false);
                imagesavealpha($cropped_image, true);
            }
            
            // Calculate crop position (center)
            $x_offset = ($new_width - $width) / 2;
            $y_offset = ($new_height - $height) / 2;
            
            // Crop the image
            imagecopy(
                $cropped_image,
                $new_image,
                0, 0, $x_offset, $y_offset,
                $width, $height
            );
            
            // Free the temporary image
            imagedestroy($new_image);
            $new_image = $cropped_image;
        }
        
        // Save the image
        $result = false;
        
        // Check if WebP is supported
        if (function_exists('imagewebp')) {
            // Ensure the destination path has a .webp extension
            $destination_path = preg_replace('/\.[^.]+$/', '.webp', $destination_path);
            
            // Save as WebP with higher quality
            $result = imagewebp($new_image, $destination_path, 90);
        } else {
            $this->errors[] = "WebP format is not supported by your PHP installation";
            
            // Free up memory
            imagedestroy($source_image);
            imagedestroy($new_image);
            
            return false;
        }
        
        // Free up memory
        imagedestroy($source_image);
        imagedestroy($new_image);
        
        if (!$result) {
            $this->errors[] = "Failed to save image to {$destination_path}";
            return false;
        }
        
        return true;
    }
}
