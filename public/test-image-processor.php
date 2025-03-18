<?php
require_once('../private/core/initialize.php');
require_login();

// Only allow admin access
if (!$session->is_admin()) {
    $session->message('You do not have permission to access this page.', 'error');
    redirect_to(url_for('/index.php'));
}

// Set page title
$page_title = 'Test Image Processor';
include(SHARED_PATH . '/member_header.php');

// Process form submission
if (is_post_request() && isset($_POST['recipe_id'])) {
    $recipe_id = $_POST['recipe_id'];
    
    // Validate recipe ID
    if (!empty($recipe_id)) {
        try {
            $recipe = Recipe::find_by_id($_POST['recipe_id']);
            
            if ($recipe && !empty($recipe->img_file_path)) {
                $source_path = PUBLIC_PATH . '/assets/uploads/recipes/' . $recipe->img_file_path;
                $destination_dir = PUBLIC_PATH . '/assets/uploads/recipes';
                
                // Create image processor
                $processor = new RecipeImageProcessor();
                
                // Process the image
                $result = $processor->processRecipeImage($source_path, $destination_dir, pathinfo($recipe->img_file_path, PATHINFO_FILENAME));
                
                if ($result) {
                    $success_message = "Image processed successfully!";
                } else {
                    $errors = $processor->getErrors();
                    $error_message = "Image processing failed: " . implode(", ", $errors);
                }
            } else {
                $errors[] = "Recipe not found or has no image.";
            }
        } catch (Exception $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    } else {
        $errors[] = "Please enter a recipe ID.";
    }
}

// Get all recipes with images
$recipes_with_images = [];
try {
    $recipes_with_images = Recipe::find_by_sql("SELECT recipe_id, title, img_file_path FROM recipes WHERE img_file_path IS NOT NULL AND img_file_path != '' LIMIT 20");
} catch (Exception $e) {
    // Ignore database errors for testing purposes
    echo "<div class='alert alert-warning'>Database query failed: " . h($e->getMessage()) . "</div>";
}

?>

<div class="container mt-4">
    <h1>Test Image Processor</h1>
    
    <?php if (!empty($errors)) { ?>
        <div class="alert alert-danger">
            <h3>Errors:</h3>
            <ul>
                <?php foreach ($errors as $error) { ?>
                    <li><?php echo h($error); ?></li>
                <?php } ?>
            </ul>
        </div>
    <?php } ?>
    
    <?php if (isset($success_message)) { ?>
        <div class="alert alert-success">
            <h3>Result:</h3>
            <?php echo $success_message; ?>
        </div>
    <?php } ?>
    
    <?php if (isset($error_message)) { ?>
        <div class="alert alert-danger">
            <h3>Error:</h3>
            <?php echo $error_message; ?>
        </div>
    <?php } ?>
    
    <h2>Environment Check</h2>
    <p>PHP Version: <?php echo phpversion(); ?></p>

    <h3>ImageMagick</h3>
    <p>ImageMagick extension loaded: <?php echo (extension_loaded('imagick') ? 'Yes' : 'No'); ?></p>

    <h3>ImageMagick Command-line Test</h3>
    <?php
    // Test direct command execution
    $output = [];
    $return_var = 0;
    
    echo "<p>Testing 'convert -version' command:</p>";
    exec('convert -version', $output, $return_var);
    
    echo "<p>Return code: " . $return_var . "</p>";
    echo "<p>Output:</p><pre>";
    print_r($output);
    echo "</pre>";
    
    if ($return_var !== 0) {
        echo "<p>Testing alternative 'magick -version' command:</p>";
        $output = [];
        exec('magick -version', $output, $return_var);
        
        echo "<p>Return code: " . $return_var . "</p>";
        echo "<p>Output:</p><pre>";
        print_r($output);
        echo "</pre>";
    }
    
    // Test processor's detection method
    echo "<h3>RecipeImageProcessor Detection Test</h3>";
    $processor = new RecipeImageProcessor();
    echo "<p>ImageMagick available according to processor: " . ($processor->isImageMagickAvailable() ? 'Yes' : 'No') . "</p>";
    
    if (!$processor->isImageMagickAvailable()) {
        echo "<p>Errors:</p><ul>";
        foreach ($processor->getErrors() as $error) {
            echo "<li>" . h($error) . "</li>";
        }
        echo "</ul>";
    }
    ?>
    
    <h3>Docker ImageMagick Test</h3>
    <?php
    // Test if we're in a Docker environment
    $in_docker = false;
    if (file_exists('/.dockerenv')) {
        $in_docker = true;
    } else {
        // Check for Docker cgroup
        $cgroup_content = @file_get_contents('/proc/self/cgroup');
        if ($cgroup_content !== false && strpos($cgroup_content, 'docker') !== false) {
            $in_docker = true;
        }
    }
    
    echo "<p>Running in Docker: " . ($in_docker ? 'Yes' : 'No') . "</p>";
    
    // Test Docker-specific ImageMagick command
    echo "<p>Testing Docker-specific ImageMagick command:</p>";
    $output = [];
    $return_var = 0;
    exec('convert -version', $output, $return_var);
    
    echo "<p>Return code: " . $return_var . "</p>";
    echo "<p>Output:</p><pre>";
    print_r($output);
    echo "</pre>";
    
    // Check PHP's open_basedir restrictions
    echo "<h3>PHP Configuration</h3>";
    echo "<p>open_basedir: " . (ini_get('open_basedir') ?: 'Not set') . "</p>";
    echo "<p>disable_functions: " . (ini_get('disable_functions') ?: 'Not set') . "</p>";
    
    // Check if exec is available
    echo "<p>exec function available: " . (function_exists('exec') && !in_array('exec', explode(',', ini_get('disable_functions'))) ? 'Yes' : 'No') . "</p>";
    ?>
    
    <h3>Docker Environment Test</h3>
    <?php
    // Create processor to test Docker detection
    $processor = new RecipeImageProcessor();
    
    // Use reflection to access the private method
    $reflection = new ReflectionClass($processor);
    $method = $reflection->getMethod('isDockerEnvironment');
    $method->setAccessible(true);
    
    $in_docker = $method->invoke($processor);
    echo "<p>Running in Docker according to processor: " . ($in_docker ? 'Yes' : 'No') . "</p>";
    
    // Get ImageMagick command
    $method = $reflection->getMethod('getImageMagickCommand');
    $method->setAccessible(true);
    
    $command = $method->invoke($processor);
    echo "<p>ImageMagick command for this environment: " . h($command) . "</p>";
    
    // Test the command
    echo "<p>Testing command execution:</p>";
    $output = [];
    $return_var = 0;
    
    $test_command = $command . ' -version';
    if ($command === 'magick convert') {
        $test_command = 'magick -version';
    }
    
    echo "<p>Running: " . h($test_command) . "</p>";
    exec($test_command, $output, $return_var);
    
    echo "<p>Return code: " . $return_var . "</p>";
    echo "<p>Output:</p><pre>";
    print_r($output);
    echo "</pre>";
    
    // Test file permissions
    echo "<h3>File Permissions Test</h3>";
    $upload_dir = PUBLIC_PATH . '/assets/uploads/recipes';
    
    echo "<p>Upload directory: " . h($upload_dir) . "</p>";
    echo "<p>Directory exists: " . (is_dir($upload_dir) ? 'Yes' : 'No') . "</p>";
    
    if (is_dir($upload_dir)) {
        echo "<p>Directory is writable: " . (is_writable($upload_dir) ? 'Yes' : 'No') . "</p>";
        
        // Try to create a test file
        $test_file = $upload_dir . '/test_' . time() . '.txt';
        $write_result = @file_put_contents($test_file, 'Test file');
        
        echo "<p>Test file creation: " . ($write_result !== false ? 'Success' : 'Failed') . "</p>";
        
        if ($write_result !== false) {
            echo "<p>Test file permissions: " . substr(sprintf('%o', fileperms($test_file)), -4) . "</p>";
            
            // Clean up
            @unlink($test_file);
            echo "<p>Test file removed: " . (!file_exists($test_file) ? 'Yes' : 'No') . "</p>";
        }
    }
    ?>
    
    <h3>GD Library</h3>
    <p>GD extension loaded: <?php echo (extension_loaded('gd') ? 'Yes' : 'No'); ?></p>
    <?php if (extension_loaded('gd')) { 
        $gd_info = gd_info();
        echo "<p>WebP Support: " . (isset($gd_info['WebP Support']) && $gd_info['WebP Support'] ? 'Yes' : 'No') . "</p>";
    } ?>
    
    <h2>Test Image Upload Handling</h2>
    
    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['test_image'])) { ?>
        <h3>Upload Test Results</h3>
        <?php
        $processor = new RecipeImageProcessor();
        $upload_dir = PUBLIC_PATH . '/assets/uploads/recipes';
        
        // Handle the upload
        $result = $processor->handleImageUpload($_FILES['test_image'], $upload_dir);
        
        echo "<p>Upload success: " . ($result['success'] ? 'Yes' : 'No') . "</p>";
        
        if ($result['success']) {
            echo "<p>New filename: " . h($result['filename']) . "</p>";
            
            // Display the uploaded image
            echo "<p>Original image:</p>";
            echo "<img src='" . url_for('/assets/uploads/recipes/' . $result['filename']) . "' style='max-width: 300px;'>";
            
            // Display processed versions if they exist
            $filename_without_ext = pathinfo($result['filename'], PATHINFO_FILENAME);
            
            echo "<p>Thumbnail version:</p>";
            if (file_exists(PUBLIC_PATH . '/assets/uploads/recipes/' . $filename_without_ext . '_thumb.webp')) {
                echo "<img src='" . url_for('/assets/uploads/recipes/' . $filename_without_ext . '_thumb.webp') . "'>";
            } else {
                echo "<p>Thumbnail not created</p>";
            }
            
            echo "<p>Optimized version:</p>";
            if (file_exists(PUBLIC_PATH . '/assets/uploads/recipes/' . $filename_without_ext . '_optimized.webp')) {
                echo "<img src='" . url_for('/assets/uploads/recipes/' . $filename_without_ext . '_optimized.webp') . "' style='max-width: 500px;'>";
            } else {
                echo "<p>Optimized version not created</p>";
            }
            
            echo "<p>Banner version:</p>";
            if (file_exists(PUBLIC_PATH . '/assets/uploads/recipes/' . $filename_without_ext . '_banner.webp')) {
                echo "<img src='" . url_for('/assets/uploads/recipes/' . $filename_without_ext . '_banner.webp') . "' style='max-width: 800px;'>";
            } else {
                echo "<p>Banner version not created</p>";
            }
        }
        
        if (!empty($result['errors'])) {
            echo "<p>Errors:</p><ul>";
            foreach ($result['errors'] as $error) {
                echo "<li>" . h($error) . "</li>";
            }
            echo "</ul>";
        }
        ?>
    <?php } ?>
    
    <h3>Upload Test Form</h3>
    <form action="<?php echo url_for('/test-image-processor.php'); ?>" method="post" enctype="multipart/form-data">
        <div>
            <label for="test_image">Select an image to upload:</label>
            <input type="file" name="test_image" id="test_image">
        </div>
        <div style="margin-top: 10px;">
            <button type="submit">Upload and Test</button>
        </div>
    </form>
    
    <h2>Test Image Processing</h2>

    <?php 
    // Create RecipeImageProcessor instance
    require_once(PRIVATE_PATH . '/classes/RecipeImageProcessor.class.php');
    $processor = new RecipeImageProcessor();

    // Get test image path
    $test_image = PUBLIC_PATH . '/assets/images/recipe-placeholder.jpg';
    if (!file_exists($test_image)) {
        echo "<p class='error'>Test image not found: {$test_image}</p>";
    } else {
        echo "<p>Test image found: {$test_image}</p>";
        
        // Process test image
        $filename = 'test_' . uniqid();
        echo "<p>Processing image with filename: {$filename}</p>";
        
        $upload_dir = PUBLIC_PATH . '/assets/uploads/recipes';
        $result = $processor->processRecipeImage($test_image, $upload_dir, $filename);
        
        echo "<p>Processing result: " . ($result ? 'Success' : 'Failed') . "</p>";
        
        if (!$result) {
            echo "<h3>Errors:</h3>";
            echo "<ul>";
            foreach ($processor->getErrors() as $error) {
                echo "<li>{$error}</li>";
            }
            echo "</ul>";
        }
    }
    ?>
    
    <div class="card">
        <div class="card-header">
            <h2>Process Recipe Image</h2>
        </div>
        <div class="card-body">
            <form method="post">
                <div class="form-group">
                    <label for="recipe_id">Recipe ID:</label>
                    <select name="recipe_id" id="recipe_id" class="form-control">
                        <option value="">Select a recipe</option>
                        <?php foreach ($recipes_with_images as $recipe) { ?>
                            <option value="<?php echo $recipe->recipe_id; ?>">
                                <?php echo h($recipe->recipe_id . ': ' . $recipe->title . ' (' . $recipe->img_file_path . ')'); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Process Image</button>
            </form>
        </div>
    </div>
</div>

<?php include(SHARED_PATH . '/footer.php'); ?>
