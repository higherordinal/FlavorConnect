<?php

/**
 * RecipeImageProcessor Class (Bluehost Optimized Version)
 * 
 * A comprehensive image processing utility for recipe images in the FlavorConnect application.
 * This class is specifically optimized for the Bluehost production environment.
 * It handles various image operations including resizing, cropping, optimization, and format conversion.
 * It supports both ImageMagick and GD library as processing engines, with automatic fallback from ImageMagick to GD
 * if ImageMagick is not available on the server.
 * 
 * Features:
 * - Creates thumbnails, optimized images, and banner images for recipes
 * - Converts images to WebP format for better compression and quality
 * - Optimized specifically for Bluehost Linux environment
 * - Provides comprehensive error handling and reporting
 * - Implements method chaining for fluent API usage
 * 
 * @author Henry Vaughn
 */
class RecipeImageProcessor {
    /** @var string Path to the source image */
    private $source_path;
    
    /** @var string Path where processed image should be saved */
    private $destination_path;
    
    /** @var string Path to the ImageMagick convert executable, hardcoded for Bluehost */
    private $convert_path = '/usr/bin/convert';
    
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
        
        // Bluehost environment uses fixed paths
        // No need to detect environment as this class is Bluehost-specific
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
        // Cache the result to avoid repeated checks
        static $result = null;
        
        if ($result !== null) {
            return $result;
        }
        
        // Check if the Imagick extension is loaded
        if (extension_loaded('imagick')) {
            return $result = true;
        }
        
        // Check if the command-line ImageMagick is available on Bluehost
        $output = [];
        $return_var = 0;
        
        // On Bluehost, we use the standard path
        exec($this->convert_path . ' -version', $output, $return_var);
        
        if ($return_var !== 0) {
            $this->errors[] = "ImageMagick command not found on Bluehost server";
            return $result = false;
        }
        
        return $result = true;
    }
    
    /**
     * Check if GD library is available
     * 
     * @return bool True if GD is available, false otherwise
     */
    public function isGDAvailable() {
        // Cache the result to avoid repeated checks
        static $result = null;
        
        if ($result !== null) {
            return $result;
        }
        
        // Check if GD extension is loaded
        if (!extension_loaded('gd')) {
            $this->errors[] = "GD library is not available";
            return $result = false;
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
        // Register shutdown function to catch fatal errors like memory exhaustion
        register_shutdown_function(function() {
            $error = error_get_last();
            if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
                error_log("FATAL ERROR in RecipeImageProcessor: " . print_r($error, true));
                
                // Check if it's a memory exhaustion error
                if (strpos($error['message'], 'Allowed memory size') !== false) {
                    error_log("MEMORY EXHAUSTION DETECTED: The script ran out of memory while processing images");
                    error_log("Current memory_limit: " . ini_get('memory_limit'));
                    error_log("Peak memory usage: " . $this->formatBytes(memory_get_peak_usage(true)));
                }
            }
        });
        
        // Memory debugging - log initial memory usage
        $initial_memory = memory_get_usage(true);
        error_log("MEMORY DEBUG: Initial memory usage: " . $this->formatBytes($initial_memory));
        error_log("MEMORY DEBUG: Current memory_limit: " . ini_get('memory_limit'));
        
        $this->errors = []; // Reset errors
        
        // Set Bluehost-specific permissions for better security
        umask(0022);
        
        // Check if source file exists
        if (!file_exists($source_path)) {
            $this->errors[] = "Source file does not exist: {$source_path}";
            return false;
        }
        
        // Log file size for debugging
        $file_size = filesize($source_path);
        error_log("MEMORY DEBUG: Processing image of size: " . $this->formatBytes($file_size));
        
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
        
        // Ensure destination directory ends with a slash (Bluehost uses forward slashes)
        $destination_dir = rtrim($destination_dir, '/') . '/';
        
        // Define paths for processed images - only WebP versions
        $thumbnail_path = $destination_dir . $filename . '_thumb.webp';
        $optimized_path = $destination_dir . $filename . '_optimized.webp';
        $banner_path = $destination_dir . $filename . '_banner.webp';
        
        // Check if ImageMagick is available
        $imagemagick_available = $this->isImageMagickAvailable();
        error_log("MEMORY DEBUG: ImageMagick available: " . ($imagemagick_available ? 'Yes' : 'No'));
        
        // Check if GD is available
        $gd_available = $this->isGDAvailable();
        error_log("MEMORY DEBUG: GD available: " . ($gd_available ? 'Yes' : 'No'));
        
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
            // Process thumbnail (smallest first to minimize memory usage)
            error_log("MEMORY DEBUG: Before thumbnail processing: " . $this->formatBytes(memory_get_usage(true)));
            $this->destination_path = $thumbnail_path;
            $thumbnail_result = $this->resizeToWebP($source_path, $thumbnail_path, 300, 200, true);
            
            // Force garbage collection between operations
            if (function_exists('gc_collect_cycles')) {
                gc_collect_cycles();
            }
            error_log("MEMORY DEBUG: After thumbnail processing: " . $this->formatBytes(memory_get_usage(true)));
            
            // Process optimized version
            $this->destination_path = $optimized_path;
            $optimized_result = $this->resizeToWebP($source_path, $optimized_path, 1000, 750, true);
            
            // Force garbage collection between operations
            if (function_exists('gc_collect_cycles')) {
                gc_collect_cycles();
            }
            error_log("MEMORY DEBUG: After optimized processing: " . $this->formatBytes(memory_get_usage(true)));
            
            // Process banner version
            $this->destination_path = $banner_path;
            $banner_result = $this->resizeToWebP($source_path, $banner_path, 1200, 400, true);
            
            // Final memory usage
            error_log("MEMORY DEBUG: After banner processing: " . $this->formatBytes(memory_get_usage(true)));
            error_log("MEMORY DEBUG: Peak memory usage: " . $this->formatBytes(memory_get_peak_usage(true)));
            
            // Return true if all operations were successful
            return $thumbnail_result && $optimized_result && $banner_result;
        }
        
        // Fall back to GD if ImageMagick is not available
        if ($gd_available) {
            // Process thumbnail (smallest first to minimize memory usage)
            error_log("MEMORY DEBUG: Before thumbnail processing: " . $this->formatBytes(memory_get_usage(true)));
            $thumbnail_result = $this->processWithGDToWebP($source_path, $thumbnail_path, 300, 200);
            
            // Force garbage collection between operations
            if (function_exists('gc_collect_cycles')) {
                gc_collect_cycles();
            }
            error_log("MEMORY DEBUG: After thumbnail processing: " . $this->formatBytes(memory_get_usage(true)));
            
            // Process optimized version
            $optimized_result = $this->processWithGDToWebP($source_path, $optimized_path, 1000, 750);
            
            // Force garbage collection between operations
            if (function_exists('gc_collect_cycles')) {
                gc_collect_cycles();
            }
            error_log("MEMORY DEBUG: After optimized processing: " . $this->formatBytes(memory_get_usage(true)));
            
            // Process banner version
            $banner_result = $this->processWithGDToWebP($source_path, $banner_path, 1200, 400);
            
            // Final memory usage
            error_log("MEMORY DEBUG: After banner processing: " . $this->formatBytes(memory_get_usage(true)));
            error_log("MEMORY DEBUG: Peak memory usage: " . $this->formatBytes(memory_get_peak_usage(true)));
            
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
        
        $convert_cmd = $this->getImageMagickCommand();
        
        if (empty($convert_cmd)) {
            $this->errors[] = "ImageMagick command not found";
            return false;
        }
        
        // Increased quality for WebP (85 allows for better quality while still maintaining reasonable file size)
        $quality = 85;
        
        if ($crop) {
            // First resize to fill the dimensions, then crop to exact size
            $cmd = "{$convert_cmd} \"{$source_path}\" -resize {$width}x{$height}^ -gravity center -extent {$width}x{$height} ";
        } else {
            // Just resize to fit within the dimensions
            $cmd = "{$convert_cmd} \"{$source_path}\" -resize {$width}x{$height} ";
        }
        
        // Add optimization flags for WebP
        // -strip: Remove all metadata (EXIF, etc.)
        // -define webp:lossless=false: Use lossy compression for smaller files
        // -define webp:method=5: Use a slightly faster compression method (0-6, where 6 is slowest but best compression)
        // -define webp:low-memory=true: Use less memory during conversion
        // -define webp:alpha-compression=1: Compress alpha channel (if present)
        // -define webp:auto-filter=true: Use more advanced filtering for better compression
        $cmd .= "-strip -quality {$quality} -define webp:lossless=false -define webp:method=5 -define webp:auto-filter=true ";
        
        // Add output file
        $cmd .= "\"{$destination_path}\"";
        
        exec($cmd, $output, $return_var);
        
        if ($return_var !== 0) {
            $this->errors[] = "WebP conversion failed with command: {$cmd}";
            return false;
        }
        
        // Verify the file exists and is smaller than the original
        if (!file_exists($destination_path)) {
            $this->errors[] = "WebP conversion failed: output file not created";
            return false;
        }
        
        // Check if the WebP file is significantly larger than the original
        $original_size = filesize($source_path);
        $webp_size = filesize($destination_path);
        
        // Only try to reduce size if it's over 300KB (allowing for larger, higher quality images)
        if ($webp_size > 300 * 1024) {
            // If WebP is too large, try again with lower quality
            $lower_quality = max(70, $quality - 10);
            
            if ($crop) {
                $cmd = "{$convert_cmd} \"{$source_path}\" -resize {$width}x{$height}^ -gravity center -extent {$width}x{$height} ";
            } else {
                $cmd = "{$convert_cmd} \"{$source_path}\" -resize {$width}x{$height} ";
            }
            
            $cmd .= "-strip -quality {$lower_quality} -define webp:lossless=false -define webp:method=5 -define webp:auto-filter=true ";
            $cmd .= "\"{$destination_path}\"";
            
            exec($cmd, $output, $return_var);
            
            if ($return_var !== 0) {
                $this->errors[] = "WebP re-optimization failed with command: {$cmd}";
                return false;
            }
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
        $quality = 85; // Increased WebP quality (0-100)
        $width = 1000; // Max width for optimized image
        
        $command = $this->getImageMagickCommand() . " \"{$source_path}\" -resize {$width}x -quality {$quality} -define webp:lossless=false -define webp:method=5 -define webp:auto-filter=true \"{$destination_path}\"";
        
        exec($command, $output, $return_var);
        
        if ($return_var !== 0) {
            $this->errors[] = "Error optimizing image to WebP: " . implode("\n", $output);
            return false;
        }
        
        return true;
    }
    
    /**
     * Delete recipe images processed versions
     * 
     * Removes all processed WebP versions (thumbnail, optimized, banner)
     * 
     * @param string $directory Directory containing the images
     * @param string $filename Filename of the original image (with or without extension)
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
        
        // Define paths to WebP versions
        $thumb_path = $directory . $base_filename . '_thumb.webp';
        $optimized_path = $directory . $base_filename . '_optimized.webp';
        $banner_path = $directory . $base_filename . '_banner.webp';
        
        $success = true;
        
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
     * - Deletes the original file after processing to only keep WebP versions
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
        
        // Fix target directory path for Bluehost - remove '/public' if it exists in the path
        // This handles the case where the server structure doesn't have a separate public directory
        if (defined('ENVIRONMENT') && ENVIRONMENT === 'production') {
            // For Bluehost, the correct path is /home2/swbhdnmy/public_html/website_7135c1f5/assets/...
            // Instead of /home2/swbhdnmy/public_html/website_7135c1f5/public/assets/...
            $target_dir = str_replace('/public_html/website_7135c1f5/public/', '/public_html/website_7135c1f5/', $target_dir);
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
            $this->errors[] = "Failed to move uploaded file to target directory: {$target_path}";
            $result['errors'] = $this->errors;
            return $result;
        }
        
        // Process the image - all processed versions will be WebP regardless of original format
        $process_result = $this->processRecipeImage($target_path, $target_dir, pathinfo($new_filename, PATHINFO_FILENAME));
        
        // If processing failed, add errors but don't fail the upload
        if (!$process_result) {
            $result['errors'] = $this->errors;
        }
        
        // Delete the original file after processing to only keep WebP versions
        if (file_exists($target_path)) {
            if (!unlink($target_path)) {
                $this->errors[] = "Warning: Failed to delete original image file after processing";
                // Don't fail the upload if we can't delete the original
            }
        }
        
        // Return success with the new filename (without extension since we'll only use WebP versions)
        $result['success'] = true;
        $result['filename'] = pathinfo($new_filename, PATHINFO_FILENAME); // Return only the filename without extension
        
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
     * Get the ImageMagick command for Bluehost
     * 
     * @return string The ImageMagick command path for Bluehost
     */
    private function getImageMagickCommand() {
        // On Bluehost, the convert command is always at the same location
        return $this->convert_path;
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
    protected function resizeWithGD($source_path, $destination_path, $width, $height, $crop = false) {
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
            case 'image/gif':
                $source_image = imagecreatefromgif($source_path);
                break;
            case 'image/webp':
                if (function_exists('imagecreatefromwebp')) {
                    $source_image = imagecreatefromwebp($source_path);
                } else {
                    $this->errors[] = "WebP support not available in GD library";
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
        
        // Get source dimensions
        $source_width = imagesx($source_image);
        $source_height = imagesy($source_image);
        
        // Calculate target dimensions
        $target_width = $width;
        $target_height = $height;
        
        if ($crop) {
            // Calculate dimensions for cropping
            $source_ratio = $source_width / $source_height;
            $target_ratio = $width / $height;
            
            if ($source_ratio > $target_ratio) {
                // Source image is wider than target ratio
                $temp_width = intval($source_height * $target_ratio);
                $temp_height = $source_height;
                $source_x = intval(($source_width - $temp_width) / 2);
                $source_y = 0;
            } else {
                // Source image is taller than target ratio
                $temp_width = $source_width;
                $temp_height = intval($source_width / $target_ratio);
                $source_x = 0;
                $source_y = intval(($source_height - $temp_height) / 2);
            }
            
            $target_image = imagecreatetruecolor($target_width, $target_height);
            
            // Preserve transparency for PNG images
            if ($mime_type === 'image/png') {
                imagealphablending($target_image, false);
                imagesavealpha($target_image, true);
                $transparent = imagecolorallocatealpha($target_image, 255, 255, 255, 127);
                imagefilledrectangle($target_image, 0, 0, $target_width, $target_height, $transparent);
            }
            
            // Crop and resize in one step
            imagecopyresampled(
                $target_image, $source_image,
                0, 0, $source_x, $source_y,
                $target_width, $target_height, $temp_width, $temp_height
            );
        } else {
            // Resize to fit within dimensions while maintaining aspect ratio
            if ($source_width / $source_height > $width / $height) {
                // Source image is wider than target ratio
                $target_width = $width;
                $target_height = intval($source_height * ($width / $source_width));
            } else {
                // Source image is taller than target ratio
                $target_height = $height;
                $target_width = intval($source_width * ($height / $source_height));
            }
            
            $target_image = imagecreatetruecolor($target_width, $target_height);
            
            // Preserve transparency for PNG images
            if ($mime_type === 'image/png') {
                imagealphablending($target_image, false);
                imagesavealpha($target_image, true);
                $transparent = imagecolorallocatealpha($target_image, 255, 255, 255, 127);
                imagefilledrectangle($target_image, 0, 0, $target_width, $target_height, $transparent);
            }
            
            // Resize the image
            imagecopyresampled(
                $target_image, $source_image,
                0, 0, 0, 0,
                $target_width, $target_height, $source_width, $source_height
            );
        }
        
        // Save the image as WebP with higher quality (85 is a good balance for better quality)
        $webp_quality = 85;
        $result = false;
        
        if (function_exists('imagewebp')) {
            $result = imagewebp($target_image, $destination_path, $webp_quality);
            
            // Check if the WebP file is too large (over 300KB)
            if (file_exists($destination_path) && filesize($destination_path) > 300 * 1024) {
                // If WebP is too large, try again with lower quality
                $lower_quality = max(70, $webp_quality - 10);
                $result = imagewebp($target_image, $destination_path, $lower_quality);
            }
        } else {
            $this->errors[] = "WebP support not available in GD library";
            
            // Fall back to JPEG if WebP is not supported
            $result = imagejpeg($target_image, $destination_path, 90);
        }
        
        // Free memory
        imagedestroy($source_image);
        imagedestroy($target_image);
        
        if (!$result) {
            $this->errors[] = "Failed to save resized image";
            return false;
        }
        
        return true;
    }
    
    /**
     * Format bytes to human-readable format for debugging
     * 
     * @param int $bytes The number of bytes
     * @param int $precision The number of decimal places
     * @return string Formatted string
     */
    private function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= (1 << (10 * $pow));
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
