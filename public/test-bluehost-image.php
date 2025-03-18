<?php
require_once('../private/core/initialize.php');

$page_title = 'Bluehost Image Test';
include(SHARED_PATH . '/public_header.php');

// Create image processor
$processor = new RecipeImageProcessor();
?>

<div class="container mt-4">
    <h1>Bluehost Image Processing Test</h1>
    
    <h2>Environment Information</h2>
    <div class="card mb-4">
        <div class="card-body">
            <h3>PHP Information</h3>
            <p>PHP Version: <?php echo phpversion(); ?></p>
            <p>Server Software: <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></p>
            <p>Document Root: <?php echo $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown'; ?></p>
            
            <h3>Bluehost Detection</h3>
            <?php
            // Use reflection to access the private method
            $reflection = new ReflectionClass($processor);
            $method = $reflection->getMethod('isBluehostEnvironment');
            $method->setAccessible(true);
            
            $on_bluehost = $method->invoke($processor);
            echo "<p>Running on Bluehost: " . ($on_bluehost ? 'Yes' : 'No') . "</p>";
            
            // Check for Bluehost environment indicators
            echo "<p>Bluehost in server name: " . (strpos($_SERVER['SERVER_NAME'] ?? '', 'bluehost') !== false ? 'Yes' : 'No') . "</p>";
            
            // Check if the compatibility function exists
            echo "<p>Bluehost compatibility functions loaded: " . (function_exists('is_bluehost_environment') ? 'Yes' : 'No') . "</p>";
            
            if (function_exists('is_bluehost_environment')) {
                echo "<p>is_bluehost_environment() result: " . (is_bluehost_environment() ? 'Yes' : 'No') . "</p>";
            }
            ?>
            
            <h3>ImageMagick Command</h3>
            <?php
            // Get ImageMagick command
            $method = $reflection->getMethod('getImageMagickCommand');
            $method->setAccessible(true);
            
            $command = $method->invoke($processor);
            echo "<p>ImageMagick command: " . h($command) . "</p>";
            
            // Test the command
            $output = [];
            $return_var = 0;
            
            $test_command = $command . ' -version';
            if ($command === 'magick convert') {
                $test_command = 'magick -version';
            }
            
            echo "<p>Running: " . h($test_command) . "</p>";
            @exec($test_command, $output, $return_var);
            
            echo "<p>Return code: " . $return_var . "</p>";
            echo "<p>Output:</p><pre>";
            print_r($output);
            echo "</pre>";
            ?>
            
            <h3>PHP Extensions</h3>
            <p>Imagick extension loaded: <?php echo extension_loaded('imagick') ? 'Yes' : 'No'; ?></p>
            <p>GD extension loaded: <?php echo extension_loaded('gd') ? 'Yes' : 'No'; ?></p>
            
            <?php if (extension_loaded('gd')): ?>
                <h4>GD Capabilities</h4>
                <p>GD Version: <?php echo gd_info()['GD Version'] ?? 'Unknown'; ?></p>
                <p>WebP Support: <?php echo function_exists('imagewebp') ? 'Yes' : 'No'; ?></p>
                <p>JPEG Support: <?php echo function_exists('imagejpeg') ? 'Yes' : 'No'; ?></p>
                <p>PNG Support: <?php echo function_exists('imagepng') ? 'Yes' : 'No'; ?></p>
            <?php endif; ?>
        </div>
    </div>
    
    <h2>Image Processing Test</h2>
    <div class="card mb-4">
        <div class="card-body">
            <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['test_image'])) { ?>
                <h3>Upload Results</h3>
                <?php
                $upload_dir = PUBLIC_PATH . '/assets/uploads/recipes';
                
                // Handle the upload
                $result = $processor->handleImageUpload($_FILES['test_image'], $upload_dir);
                
                echo "<p>Upload success: " . ($result['success'] ? 'Yes' : 'No') . "</p>";
                
                if ($result['success']) {
                    echo "<p>New filename: " . h($result['filename']) . "</p>";
                    
                    // Display the uploaded image
                    echo "<p>Original image:</p>";
                    echo "<img src='" . url_for('/assets/uploads/recipes/' . $result['filename']) . "' class='img-fluid' style='max-width: 300px;'>";
                    
                    // Display processed versions if they exist
                    $filename_without_ext = pathinfo($result['filename'], PATHINFO_FILENAME);
                    
                    echo "<p>Thumbnail version:</p>";
                    if (file_exists(PUBLIC_PATH . '/assets/uploads/recipes/' . $filename_without_ext . '_thumb.webp')) {
                        echo "<img src='" . url_for('/assets/uploads/recipes/' . $filename_without_ext . '_thumb.webp') . "' class='img-fluid'>";
                    } else {
                        echo "<p class='text-danger'>Thumbnail not created</p>";
                    }
                    
                    echo "<p>Optimized version:</p>";
                    if (file_exists(PUBLIC_PATH . '/assets/uploads/recipes/' . $filename_without_ext . '_optimized.webp')) {
                        echo "<img src='" . url_for('/assets/uploads/recipes/' . $filename_without_ext . '_optimized.webp') . "' class='img-fluid' style='max-width: 500px;'>";
                    } else {
                        echo "<p class='text-danger'>Optimized version not created</p>";
                    }
                    
                    echo "<p>Banner version:</p>";
                    if (file_exists(PUBLIC_PATH . '/assets/uploads/recipes/' . $filename_without_ext . '_banner.webp')) {
                        echo "<img src='" . url_for('/assets/uploads/recipes/' . $filename_without_ext . '_banner.webp') . "' class='img-fluid' style='max-width: 800px;'>";
                    } else {
                        echo "<p class='text-danger'>Banner version not created</p>";
                    }
                }
                
                if (!empty($result['errors'])) {
                    echo "<div class='alert alert-danger'>";
                    echo "<p>Errors:</p><ul>";
                    foreach ($result['errors'] as $error) {
                        echo "<li>" . h($error) . "</li>";
                    }
                    echo "</ul></div>";
                }
                ?>
            <?php } ?>
            
            <h3>Upload Test Form</h3>
            <form action="<?php echo url_for('/test-bluehost-image.php'); ?>" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="test_image">Select an image to upload:</label>
                    <input type="file" name="test_image" id="test_image" class="form-control-file">
                </div>
                <div class="form-group mt-3">
                    <button type="submit" class="btn btn-primary">Upload and Test</button>
                </div>
            </form>
        </div>
    </div>
    
    <h2>File System Tests</h2>
    <div class="card mb-4">
        <div class="card-body">
            <?php
            $upload_dir = PUBLIC_PATH . '/assets/uploads/recipes';
            
            echo "<h3>Upload Directory</h3>";
            echo "<p>Path: " . h($upload_dir) . "</p>";
            echo "<p>Directory exists: " . (is_dir($upload_dir) ? 'Yes' : 'No') . "</p>";
            
            if (is_dir($upload_dir)) {
                echo "<p>Directory is writable: " . (is_writable($upload_dir) ? 'Yes' : 'No') . "</p>";
                echo "<p>Directory permissions: " . substr(sprintf('%o', fileperms($upload_dir)), -4) . "</p>";
                
                // Try to create a test file
                $test_file = $upload_dir . '/bluehost_test_' . time() . '.txt';
                $write_result = @file_put_contents($test_file, 'Test file for Bluehost environment');
                
                echo "<h3>Test File Creation</h3>";
                echo "<p>Test file: " . h($test_file) . "</p>";
                echo "<p>Creation result: " . ($write_result !== false ? 'Success' : 'Failed') . "</p>";
                
                if ($write_result !== false) {
                    echo "<p>File exists: " . (file_exists($test_file) ? 'Yes' : 'No') . "</p>";
                    echo "<p>File permissions: " . substr(sprintf('%o', fileperms($test_file)), -4) . "</p>";
                    echo "<p>File size: " . filesize($test_file) . " bytes</p>";
                    
                    // Clean up
                    $delete_result = @unlink($test_file);
                    echo "<p>File deletion: " . ($delete_result ? 'Success' : 'Failed') . "</p>";
                    echo "<p>File exists after deletion: " . (file_exists($test_file) ? 'Yes' : 'No') . "</p>";
                }
            }
            ?>
        </div>
    </div>
</div>

<?php include(SHARED_PATH . '/public_footer.php'); ?>
