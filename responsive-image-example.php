<?php
/**
 * Responsive Image Example Template
 * 
 * This file demonstrates how to implement responsive images in FlavorConnect
 * to address Lighthouse performance recommendations.
 */

// Helper function to generate responsive image markup
function responsive_image($base_path, $alt_text, $class = '', $lazy = true) {
    // Extract filename and extension
    $path_parts = pathinfo($base_path);
    $filename = $path_parts['filename'];
    $extension = isset($path_parts['extension']) ? $path_parts['extension'] : 'webp';
    $dir = $path_parts['dirname'];
    
    // Responsive directory path
    $responsive_dir = '/assets/images/responsive';
    
    // Construct responsive image paths
    $large_src = "$responsive_dir/$filename-large.$extension";
    $medium_src = "$responsive_dir/$filename-medium.$extension";
    $small_src = "$responsive_dir/$filename-small.$extension";
    $thumb_src = "$responsive_dir/$filename-thumb.$extension";
    
    // Fallback to original if responsive images don't exist
    $original_src = $base_path;
    
    // Lazy loading attribute
    $lazy_attr = $lazy ? 'loading="lazy"' : '';
    
    // Generate srcset and sizes attributes
    $srcset = "$large_src 1200w, $medium_src 768w, $small_src 480w, $thumb_src 240w";
    $sizes = "(max-width: 480px) 100vw, (max-width: 768px) 768px, 1200px";
    
    // Class attribute
    $class_attr = !empty($class) ? "class=\"$class\"" : '';
    
    // Return responsive image markup
    return "<img src=\"$medium_src\" srcset=\"$srcset\" sizes=\"$sizes\" alt=\"$alt_text\" $lazy_attr $class_attr>";
}

// Example usage for hero image
$hero_image_markup = responsive_image('/assets/images/hero-img.webp', 'Delicious food on a table', 'hero-image', false);

// Example usage for recipe card image
$recipe_image_markup = responsive_image('/assets/images/recipes/pasta.webp', 'Pasta dish with tomato sauce', 'recipe-image', true);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Responsive Images Example - FlavorConnect</title>
    <style>
        /* Example styling */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .hero-section {
            position: relative;
            width: 100%;
            margin-bottom: 30px;
        }
        
        .hero-image {
            width: 100%;
            height: auto;
            border-radius: 8px;
        }
        
        .recipe-card {
            width: 300px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .recipe-image {
            width: 100%;
            height: auto;
        }
        
        .code-example {
            background-color: #f5f5f5;
            padding: 20px;
            border-radius: 8px;
            margin: 30px 0;
            overflow: auto;
        }
        
        pre {
            margin: 0;
            white-space: pre-wrap;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Responsive Images Implementation</h1>
        <p>This page demonstrates how to implement responsive images in FlavorConnect to address Lighthouse performance recommendations.</p>
        
        <h2>Hero Image Example</h2>
        <p>The hero image below uses responsive srcset to load the appropriate image size based on the viewport:</p>
        
        <div class="hero-section">
            <?php echo $hero_image_markup; ?>
        </div>
        
        <h2>Recipe Card Image Example</h2>
        <p>Recipe images also use responsive srcset and are lazy-loaded when they're below the fold:</p>
        
        <div class="recipe-card">
            <?php echo $recipe_image_markup; ?>
            <div style="padding: 15px;">
                <h3>Pasta Dish</h3>
                <p>Delicious pasta with tomato sauce</p>
            </div>
        </div>
        
        <h2>Implementation Code</h2>
        <div class="code-example">
            <pre><code>// Helper function to generate responsive image markup
function responsive_image($base_path, $alt_text, $class = '', $lazy = true) {
    // Extract filename and extension
    $path_parts = pathinfo($base_path);
    $filename = $path_parts['filename'];
    $extension = isset($path_parts['extension']) ? $path_parts['extension'] : 'webp';
    
    // Responsive directory path
    $responsive_dir = '/assets/images/responsive';
    
    // Construct responsive image paths
    $large_src = "$responsive_dir/$filename-large.$extension";
    $medium_src = "$responsive_dir/$filename-medium.$extension";
    $small_src = "$responsive_dir/$filename-small.$extension";
    $thumb_src = "$responsive_dir/$filename-thumb.$extension";
    
    // Lazy loading attribute
    $lazy_attr = $lazy ? 'loading="lazy"' : '';
    
    // Generate srcset and sizes attributes
    $srcset = "$large_src 1200w, $medium_src 768w, $small_src 480w, $thumb_src 240w";
    $sizes = "(max-width: 480px) 100vw, (max-width: 768px) 768px, 1200px";
    
    // Class attribute
    $class_attr = !empty($class) ? "class=\"$class\"" : '';
    
    // Return responsive image markup
    return "&lt;img src=\"$medium_src\" srcset=\"$srcset\" sizes=\"$sizes\" alt=\"$alt_text\" $lazy_attr $class_attr&gt;";
}</code></pre>
        </div>
        
        <h2>Usage Examples</h2>
        <div class="code-example">
            <pre><code>// For hero images (above the fold, no lazy loading)
$hero_image = responsive_image('/assets/images/hero-img.webp', 'Hero image description', 'hero-image', false);

// For recipe card images (potentially below the fold, use lazy loading)
$recipe_image = responsive_image('/assets/images/recipes/pasta.webp', 'Pasta dish', 'recipe-image', true);</code></pre>
        </div>
        
        <h2>Implementation Steps</h2>
        <ol>
            <li>Run the <code>optimize-images.ps1</code> script to generate optimized responsive image variants</li>
            <li>Add the <code>responsive_image()</code> function to your utilities or helper functions</li>
            <li>Replace standard <code>&lt;img&gt;</code> tags with calls to the <code>responsive_image()</code> function</li>
            <li>Use <code>loading="lazy"</code> for images below the fold</li>
        </ol>
        
        <h2>Performance Benefits</h2>
        <ul>
            <li>Smaller image downloads for mobile devices</li>
            <li>Faster page load times across all devices</li>
            <li>Reduced bandwidth usage</li>
            <li>Better Lighthouse performance scores</li>
        </ul>
    </div>
</body>
</html>
