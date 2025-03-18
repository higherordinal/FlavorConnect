<?php
require_once('../private/initialize.php');
$page_title = 'Image Credits';
$page_style = 'credits';
include(SHARED_PATH . '/member_header.php');
?>

<main class="container credits-page">
    <div class="content-wrapper">
        <h1>Image Credits</h1>
        
        <p>FlavorConnect uses high-quality images from various sources. We're grateful to the talented photographers who make their work available under generous licenses.</p>
        
        <section class="credits-section">
            <h2>Hero Images</h2>
            
            <div class="credit-item">
                <h3>Homepage Hero Image</h3>
                <!-- Replace with actual information -->
                <p>Photo by <a href="#photographer-link">Photographer Name</a> on <a href="https://unsplash.com">Unsplash</a></p>
            </div>
            
            <div class="credit-item">
                <h3>About Page Hero Image</h3>
                <!-- Replace with actual information -->
                <p>Photo by <a href="#photographer-link">Photographer Name</a> on <a href="https://www.pexels.com">Pexels</a></p>
            </div>
            
            <div class="credit-item">
                <h3>Recipe Form Header</h3>
                <!-- Replace with actual information -->
                <p>Photo by <a href="#photographer-link">Photographer Name</a> on <a href="#source-link">Source</a></p>
            </div>
        </section>
        
        <section class="credits-section">
            <h2>Default Images</h2>
            
            <div class="credit-item">
                <h3>Recipe Placeholder</h3>
                <!-- Replace with actual information -->
                <p>Photo by <a href="#photographer-link">Photographer Name</a> on <a href="#source-link">Source</a></p>
            </div>
        </section>
        
        <section class="credits-section">
            <h2>License Information</h2>
            
            <div class="license-info">
                <h3>Unsplash License</h3>
                <p>All photos published on Unsplash are licensed under the <a href="https://unsplash.com/license">Unsplash License</a>:</p>
                <ul>
                    <li>Free to use for commercial and noncommercial purposes</li>
                    <li>No permission needed (though attribution is appreciated)</li>
                </ul>
            </div>
            
            <div class="license-info">
                <h3>Pexels License</h3>
                <p>All photos and videos on Pexels are free to use under the <a href="https://www.pexels.com/license/">Pexels License</a>:</p>
                <ul>
                    <li>Free for commercial and noncommercial use</li>
                    <li>Attribution is not required (but appreciated)</li>
                </ul>
            </div>
        </section>
    </div>
</main>

<?php include(SHARED_PATH . '/member_footer.php'); ?>
