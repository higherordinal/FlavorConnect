<?php

class RecipeImageProcessor {
    private $source_path;
    private $destination_path;
    private $is_windows;
    private $convert_path;
    private $errors = [];
    
    /**
     * Constructor for RecipeImageProcessor
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
     * @param string $path Path where processed image should be saved
     * @return RecipeImageProcessor Returns this instance for method chaining
     */
    public function setDestinationPath($path) {
        $this->destination_path = $path;
        return $this;
    }
    
    /**
     * Check if ImageMagick is available on the system
     * @return bool True if ImageMagick is available
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
            exec($command . ' -version', $output, $return_var);
            return $return_var === 0;
        }
        
        return false;
    }
    
    /**
     * Check if GD library is available
     * @return bool True if GD is available
     */
    public function isGDAvailable() {
        return extension_loaded('gd') && function_exists('imagecreatefromjpeg');
    }
    
    /**
     * Resize an image to the specified dimensions
     * 
     * @param string $source_path Path to the source image
     * @param string $destination_path Path where the resized image should be saved
     * @param int $width Target width
     * @param int $height Target height
     * @param bool $maintain_aspect_ratio Whether to maintain aspect ratio
     * @return bool True if successful, false otherwise
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
     * @param int $width Target width
     * @param int $height Target height
     * @param int $x X coordinate of the top-left corner
     * @param int $y Y coordinate of the top-left corner
     * @return bool True if successful, false otherwise
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
     * @param string $source_path Path to the source image
     * @param string $destination_path Path where the optimized image should be saved
     * @param int $quality JPEG quality (0-100)
     * @return bool True if successful, false otherwise
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
     * @param int $width Thumbnail width
     * @param int $height Thumbnail height
     * @param bool $crop Whether to crop the image to fit the dimensions
     * @return bool True if successful, false otherwise
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
     * Process a recipe image - create thumbnail and optimize for header
     * 
     * @param string $source_path Path to the source image
     * @param string $destination_dir Directory to save processed images
     * @param string $filename Original filename
     * @return bool True if successful, false otherwise
     */
    public function processRecipeImage($source_path, $destination_dir, $filename) {
        if (!file_exists($source_path)) {
            $this->errors[] = "Source file does not exist: {$source_path}";
            return false;
        }
        
        if (!is_dir($destination_dir)) {
            if (!mkdir($destination_dir, 0755, true)) {
                $this->errors[] = "Could not create destination directory: {$destination_dir}";
                return false;
            }
        }
        
        // Get file info
        $file_info = pathinfo($filename);
        $file_basename = $file_info['filename'];
        $file_extension = $file_info['extension'];
        
        // Define output paths
        $thumbnail_path = $destination_dir . '/' . $file_basename . '_thumb.' . $file_extension;
        $optimized_path = $destination_dir . '/' . $file_basename . '_optimized.' . $file_extension;
        
        // Try processing with ImageMagick first
        if ($this->isImageMagickAvailable()) {
            // Create thumbnail (300x200)
            $thumbnail_result = $this->resize($source_path, $thumbnail_path, 300, 200);
            
            // Create optimized version
            $optimized_result = $this->optimize($source_path, $optimized_path);
            
            // If both operations were successful, return true
            if ($thumbnail_result && $optimized_result) {
                return true;
            }
            
            // If ImageMagick failed, log the error but continue to try GD
            $this->errors[] = "ImageMagick processing failed, trying GD library as fallback.";
        } else {
            $this->errors[] = "ImageMagick is not available, trying GD library as fallback.";
        }
        
        // Try with GD library as fallback
        if ($this->isGDAvailable()) {
            $thumbnail_result = $this->processWithGD($source_path, $thumbnail_path, 300, 200);
            $optimized_result = $this->processWithGD($source_path, $optimized_path, 800, 600);
            
            return $thumbnail_result && $optimized_result;
        }
        
        // If we get here, both ImageMagick and GD failed or are not available
        $this->errors[] = "Both ImageMagick and GD library are not available. Cannot process images.";
        return false;
    }
    
    /**
     * Process an image using the GD library as a fallback when ImageMagick is not available
     * @param string $source_path Path to the source image
     * @param string $target_path Path to save the processed image
     * @param int $width Target width
     * @param int $height Target height
     * @return bool True if successful, false otherwise
     */
    public function processWithGD($source_path, $target_path, $width, $height) {
        // Check if GD is available
        if (!extension_loaded('gd') || !function_exists('imagecreatefromjpeg')) {
            $this->errors[] = "GD library is not available. Cannot process image.";
            return false;
        }
        
        try {
            if (!file_exists($source_path)) {
                $this->errors[] = "Source file does not exist: {$source_path}";
                return false;
            }
            
            // Get image info
            $image_info = getimagesize($source_path);
            if ($image_info === false) {
                $this->errors[] = "Could not get image information";
                return false;
            }
            
            // Create image resource based on file type
            $source_image = null;
            switch ($image_info[2]) {
                case IMAGETYPE_JPEG:
                    $source_image = imagecreatefromjpeg($source_path);
                    break;
                case IMAGETYPE_PNG:
                    $source_image = imagecreatefrompng($source_path);
                    break;
                default:
                    $this->errors[] = "Unsupported image type";
                    return false;
            }
            
            if (!$source_image) {
                $this->errors[] = "Failed to create image resource";
                return false;
            }
            
            // Get original dimensions
            $orig_width = imagesx($source_image);
            $orig_height = imagesy($source_image);
            
            // Calculate new dimensions while maintaining aspect ratio
            $ratio = min($width / $orig_width, $height / $orig_height);
            $new_width = round($orig_width * $ratio);
            $new_height = round($orig_height * $ratio);
            
            // Create new image
            $new_image = imagecreatetruecolor($new_width, $new_height);
            
            // Handle transparency for PNG
            if ($image_info[2] === IMAGETYPE_PNG) {
                imagealphablending($new_image, false);
                imagesavealpha($new_image, true);
                $transparent = imagecolorallocatealpha($new_image, 255, 255, 255, 127);
                imagefilledrectangle($new_image, 0, 0, $new_width, $new_height, $transparent);
            }
            
            // Resize
            imagecopyresampled(
                $new_image, $source_image,
                0, 0, 0, 0,
                $new_width, $new_height, $orig_width, $orig_height
            );
            
            // Save the image
            $result = false;
            switch ($image_info[2]) {
                case IMAGETYPE_JPEG:
                    $result = imagejpeg($new_image, $target_path, 90); // 90% quality
                    break;
                case IMAGETYPE_PNG:
                    $result = imagepng($new_image, $target_path, 9); // 0-9, 9 is highest compression
                    break;
            }
            
            // Free memory
            imagedestroy($source_image);
            imagedestroy($new_image);
            
            return $result;
        } catch (Exception $e) {
            $this->errors[] = "GD processing error: " . $e->getMessage();
            return false;
        }
    }
    
    /**
     * Get any errors that occurred during processing
     * 
     * @return array Array of error messages
     */
    public function getErrors() {
        return $this->errors;
    }
    
    /**
     * Check if there are any errors
     * 
     * @return bool True if there are errors, false otherwise
     */
    public function hasErrors() {
        return !empty($this->errors);
    }
    
    private function getImageMagickCommand() {
        if ($this->is_windows) {
            // For Windows, use the locally installed ImageMagick
            // Update this path to match your ImageMagick installation
            return 'magick convert';
        } else {
            // For Bluehost or other Linux servers
            return '/usr/bin/convert';
        }
    }
}
