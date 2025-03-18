<?php
require_once('../private/core/initialize.php');

// Set page title
$page_title = 'Test Image Quality';

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
        $message = "Image successfully regenerated with higher quality.";
    } else {
        $message = "Failed to regenerate image: " . implode(", ", $processor->getErrors());
    }
} else {
    $message = "Click 'Regenerate Image' to create a higher quality version of the recipe image.";
}

// Include header
include(SHARED_PATH . '/public_header.php');
?>

<div class="container mt-5">
    <h1>Test Image Quality</h1>
    
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
                            <h3>Optimized Image (New Quality)</h3>
                            <img src="<?php echo url_for($recipe->get_image_path('optimized')); ?>" 
                                 alt="<?php echo h($recipe->alt_text ?? $recipe->title); ?>"
                                 class="img-fluid mb-3">
                            
                            <p>Image Path: <?php echo h($recipe->get_image_path('optimized')); ?></p>
                            
                            <a href="<?php echo url_for('/test-image-quality.php?id=' . h(u($recipe->id)) . '&regenerate=true'); ?>" 
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
                            // Get image details for the optimized image
                            $optimized_path = PUBLIC_PATH . $recipe->get_image_path('optimized');
                            if (file_exists($optimized_path)) {
                                $image_info = getimagesize($optimized_path);
                                $file_size = filesize($optimized_path);
                                
                                echo "<p><strong>Dimensions:</strong> {$image_info[0]} x {$image_info[1]} pixels</p>";
                                echo "<p><strong>File Size:</strong> " . round($file_size / 1024, 2) . " KB</p>";
                                echo "<p><strong>MIME Type:</strong> {$image_info['mime']}</p>";
                            } else {
                                echo "<p>Optimized image file not found.</p>";
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
