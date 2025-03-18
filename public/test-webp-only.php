<?php
require_once('../private/core/initialize.php');

// Set page title
$page_title = 'Test WebP Only Images';

// Get recipe ID from query string or use a default
$recipe_id = $_GET['id'] ?? 1;

// Find the recipe
$recipe = Recipe::find_by_id($recipe_id);

if (!$recipe) {
    echo "Recipe not found.";
    exit;
}

// Check if we should regenerate the image
$regenerate = isset($_GET['regenerate']) && $_GET['regenerate'] === 'true';

if ($regenerate && $recipe->img_file_path) {
    // Get the original image path
    $original_path = PRIVATE_PATH . '/uploads/recipes/' . $recipe->img_file_path;
    
    // Create image processor
    $processor = new RecipeImageProcessor();
    
    // Process the image
    $filename = pathinfo($recipe->img_file_path, PATHINFO_FILENAME);
    $destination_dir = PUBLIC_PATH . '/assets/uploads/recipes/';
    
    $result = $processor->processRecipeImage($original_path, $destination_dir, $filename);
    
    if ($result) {
        $message = "Image successfully regenerated in WebP format only.";
    } else {
        $message = "Failed to regenerate image: " . implode(", ", $processor->getErrors());
    }
} else {
    $message = "Click 'Regenerate Image' to create WebP-only versions of the recipe image.";
}

// Include header
include(SHARED_PATH . '/public_header.php');
?>

<div class="container mt-5">
    <h1>Test WebP-Only Images</h1>
    
    <?php if (isset($message)): ?>
        <div class="alert alert-info"><?php echo $message; ?></div>
    <?php endif; ?>
    
    <?php if ($recipe): ?>
        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h2><?php echo h($recipe->title); ?></h2>
                    </div>
                    <div class="card-body">
                        <?php if ($recipe->img_file_path): ?>
                            <h3>Optimized Image (WebP Format)</h3>
                            <img src="<?php echo url_for($recipe->get_image_path('optimized')); ?>" 
                                 alt="<?php echo h($recipe->alt_text ?? $recipe->title); ?>"
                                 class="img-fluid mb-3">
                            
                            <p>Image Path: <?php echo h($recipe->get_image_path('optimized')); ?></p>
                            
                            <a href="<?php echo url_for('/test-webp-only.php?id=' . h(u($recipe->id)) . '&regenerate=true'); ?>" 
                               class="btn btn-primary">Regenerate Image</a>
                            
                            <a href="<?php echo url_for('/recipes/show.php?id=' . h(u($recipe->id))); ?>" 
                               class="btn btn-secondary">View Recipe</a>
                        <?php else: ?>
                            <p>This recipe has no image.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3>Image Details</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($recipe->img_file_path): ?>
                            <p><strong>Original Filename:</strong> <?php echo h($recipe->img_file_path); ?></p>
                            
                            <?php
                            // Check for any non-WebP processed images
                            $base_filename = pathinfo($recipe->img_file_path, PATHINFO_FILENAME);
                            $image_dir = PUBLIC_PATH . '/assets/uploads/recipes/';
                            $non_webp_files = [];
                            
                            // Check for common image extensions
                            foreach (['jpg', 'jpeg', 'png', 'gif'] as $ext) {
                                $pattern = $image_dir . $base_filename . '_*.' . $ext;
                                $found_files = glob($pattern);
                                if (!empty($found_files)) {
                                    $non_webp_files = array_merge($non_webp_files, $found_files);
                                }
                            }
                            
                            // Get all WebP processed files
                            $webp_pattern = $image_dir . $base_filename . '_*.webp';
                            $webp_files = glob($webp_pattern);
                            
                            // Display information about processed files
                            echo "<h4>Processed Images:</h4>";
                            echo "<ul>";
                            
                            if (!empty($webp_files)) {
                                foreach ($webp_files as $file) {
                                    $file_info = pathinfo($file);
                                    $relative_path = '/assets/uploads/recipes/' . $file_info['basename'];
                                    $file_size = filesize($file);
                                    $image_info = getimagesize($file);
                                    
                                    echo "<li>";
                                    echo "<strong>" . basename($file) . "</strong><br>";
                                    echo "Format: WebP<br>";
                                    echo "Size: " . round($file_size / 1024, 2) . " KB<br>";
                                    echo "Dimensions: {$image_info[0]} x {$image_info[1]} pixels<br>";
                                    echo "</li>";
                                }
                            } else {
                                echo "<li>No WebP processed images found.</li>";
                            }
                            
                            echo "</ul>";
                            
                            // Display information about non-WebP files
                            if (!empty($non_webp_files)) {
                                echo "<h4>Non-WebP Processed Images (These should be eliminated):</h4>";
                                echo "<ul>";
                                
                                foreach ($non_webp_files as $file) {
                                    $file_info = pathinfo($file);
                                    $file_size = filesize($file);
                                    $image_info = getimagesize($file);
                                    
                                    echo "<li>";
                                    echo "<strong>" . basename($file) . "</strong><br>";
                                    echo "Format: " . strtoupper($file_info['extension']) . "<br>";
                                    echo "Size: " . round($file_size / 1024, 2) . " KB<br>";
                                    echo "Dimensions: {$image_info[0]} x {$image_info[1]} pixels<br>";
                                    echo "</li>";
                                }
                                
                                echo "</ul>";
                            } else {
                                echo "<div class='alert alert-success'>No non-WebP processed images found. Great!</div>";
                            }
                            ?>
                        <?php else: ?>
                            <p>No image information available.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <p>Recipe not found.</p>
    <?php endif; ?>
</div>

<?php include(SHARED_PATH . '/public_footer.php'); ?>
