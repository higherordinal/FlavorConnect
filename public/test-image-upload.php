<?php
require_once('../private/core/initialize.php');
require_login();

// Only allow admin access
if (!$session->is_admin()) {
    $session->message('You do not have permission to access this page.', 'error');
    redirect_to(url_for('/index.php'));
}

// Set page title
$page_title = 'Test Image Upload';
include(SHARED_PATH . '/member_header.php');

// Process form submission
$result = '';
$errors = [];
$debug_info = [];

if (is_post_request() && isset($_FILES['test_image'])) {
    // Create image processor
    require_once(PRIVATE_PATH . '/classes/RecipeImageProcessor.class.php');
    $processor = new RecipeImageProcessor();
    
    // Define upload directory
    $upload_dir = PUBLIC_PATH . '/assets/uploads/recipes/';
    
    // Handle image upload and processing
    $new_filename = $processor->handleImageUpload($_FILES['test_image'], $upload_dir);
    
    if ($new_filename) {
        $result = "Image uploaded and processed successfully!";
        $debug_info[] = "New filename: " . $new_filename;
        
        // Get filename without extension for processed images
        $path_parts = pathinfo($new_filename);
        $filename_without_ext = $path_parts['filename'];
        
        // Define image paths
        $thumb_path = '/assets/uploads/recipes/' . $filename_without_ext . '_thumb.webp';
        $optimized_path = '/assets/uploads/recipes/' . $filename_without_ext . '_optimized.webp';
        $banner_path = '/assets/uploads/recipes/' . $filename_without_ext . '_banner.webp';
        $original_path = '/assets/uploads/recipes/' . $new_filename;
        
        // Check if processed files exist
        $debug_info[] = "Thumbnail file exists: " . (file_exists(PUBLIC_PATH . $thumb_path) ? "YES" : "NO");
        $debug_info[] = "Optimized file exists: " . (file_exists(PUBLIC_PATH . $optimized_path) ? "YES" : "NO");
        $debug_info[] = "Banner file exists: " . (file_exists(PUBLIC_PATH . $banner_path) ? "YES" : "NO");
        
        // Display image paths
        $result .= "<p>Original: <a href='" . url_for($original_path) . "' target='_blank'>" . url_for($original_path) . "</a></p>";
        $result .= "<p>Thumbnail: <a href='" . url_for($thumb_path) . "' target='_blank'>" . url_for($thumb_path) . "</a></p>";
        $result .= "<p>Optimized: <a href='" . url_for($optimized_path) . "' target='_blank'>" . url_for($optimized_path) . "</a></p>";
        $result .= "<p>Banner: <a href='" . url_for($banner_path) . "' target='_blank'>" . url_for($banner_path) . "</a></p>";
        
        // Display images
        $result .= "<h3>Original</h3>";
        $result .= "<img src='" . url_for($original_path) . "' style='max-width: 300px;'>";
        
        $result .= "<h3>Thumbnail</h3>";
        $result .= "<img src='" . url_for($thumb_path) . "' style='max-width: 300px;'>";
        
        $result .= "<h3>Optimized</h3>";
        $result .= "<img src='" . url_for($optimized_path) . "' style='max-width: 300px;'>";
        
        $result .= "<h3>Banner</h3>";
        $result .= "<img src='" . url_for($banner_path) . "' style='max-width: 600px;'>";
    } else {
        $errors = $processor->getErrors();
        $debug_info[] = "Image upload or processing failed";
    }
}
?>

<div class="container mt-4">
    <h1>Test Image Upload</h1>
    
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
    
    <?php if (!empty($debug_info)) { ?>
        <div class="alert alert-info">
            <h3>Debug Information:</h3>
            <ul>
                <?php foreach ($debug_info as $info) { ?>
                    <li><?php echo h($info); ?></li>
                <?php } ?>
            </ul>
        </div>
    <?php } ?>
    
    <?php if (!empty($result)) { ?>
        <div class="alert alert-success">
            <h3>Result:</h3>
            <?php echo $result; ?>
        </div>
    <?php } ?>
    
    <div class="card">
        <div class="card-header">
            <h2>Upload Test Image</h2>
        </div>
        <div class="card-body">
            <form method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="test_image">Select Image:</label>
                    <input type="file" name="test_image" id="test_image" class="form-control-file">
                    <small class="form-text text-muted">Allowed formats: JPG, PNG, WebP</small>
                </div>
                <button type="submit" class="btn btn-primary">Upload & Process</button>
            </form>
        </div>
    </div>
    
    <div class="mt-4">
        <a href="<?php echo url_for('/test-image-processor.php'); ?>" class="btn btn-secondary">
            Go to Image Processor Test
        </a>
    </div>
</div>

<?php include(SHARED_PATH . '/footer.php'); ?>
