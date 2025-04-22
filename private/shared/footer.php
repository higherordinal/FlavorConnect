    </main> <!-- Close .main-content from header.php -->
    
<footer role="contentinfo">
    <!-- Add visually hidden h2 to fix heading hierarchy -->
    <h2 class="visually-hidden">Footer Information</h2>
    
    <div class="footer-info">
        <div class="footer-logo">
            <a href="<?php echo url_for('/index.php'); ?>" aria-label="FlavorConnect Home">
                <span class="logo-the"><i class="fas fa-utensils"></i>The</span>
                <span class="logo-name">FlavorConnect</span>
            </a>
        </div>
        <p>Connecting food enthusiasts with their perfect dining experiences.</p>
        <address aria-label="Contact information">
            123 Foodie Lane<br>
            Cuisine City, FC 12345<br>
        </address>
    </div>

    <div class="footer-links">
        <h3 id="quick-links">Quick Links</h3>
        <nav aria-labelledby="quick-links">
            <ul>
                <li><a href="<?php echo url_for('/index.php'); ?>">Home</a></li>
                <li><a href="<?php echo url_for('/about.php'); ?>">About Us</a></li>
            </ul>
        </nav>
    </div>

    <div class="footer-social">
        <h3 id="social-links">Connect With Us</h3>
        <nav aria-labelledby="social-links">
            <ul>
                <li><a href="https://www.facebook.com" aria-label="Visit our Facebook page">Facebook</a></li>
                <li><a href="https://www.instagram.com" aria-label="Follow us on Instagram">Instagram</a></li>
                <li><a href="https://www.linkedin.com" aria-label="Connect with us on LinkedIn">LinkedIn</a></li>
                <li><a href="https://www.x.com" aria-label="Follow us on X">X</a></li>
            </ul>
        </nav>
    </div>

    <div class="footer-copyright">
        <p>&copy; <?php echo date('Y'); ?> FlavorConnect. All rights reserved.</p>
    </div>
</footer>

    <!-- Load JavaScript files -->
    <?php
    // Initialize script arrays if not set
    if(!isset($page_scripts)) { $page_scripts = []; }
    if(!isset($component_scripts)) { $component_scripts = []; }
    if(!isset($utility_scripts)) { $utility_scripts = []; }
    
    // Load utility scripts
    if(!empty($utility_scripts)) {
        foreach($utility_scripts as $script) {
            $js_file = '/assets/js/utils/' . $script . '.js';
            $js_path = PUBLIC_PATH . $js_file;
            $js_version = file_exists($js_path) ? filemtime($js_path) : time();
            echo '<script src="' . url_for($js_file) . '?v=' . $js_version . '" defer></script>';
        }
    }
    
    // Load component scripts
    if(!empty($component_scripts)) {
        foreach($component_scripts as $script) {
            $js_file = '/assets/js/components/' . $script . '.js';
            $js_path = PUBLIC_PATH . $js_file;
            $js_version = file_exists($js_path) ? filemtime($js_path) : time();
            echo '<script src="' . url_for($js_file) . '?v=' . $js_version . '" defer></script>';
        }
    }
    
    // Load page-specific scripts
    if(!empty($page_scripts)) {
        foreach($page_scripts as $script) {
            $js_file = '/assets/js/pages/' . $script . '.js';
            $js_path = PUBLIC_PATH . $js_file;
            $js_version = file_exists($js_path) ? filemtime($js_path) : time();
            echo '<script src="' . url_for($js_file) . '?v=' . $js_version . '" defer></script>';
        }
    }
    ?>
</body>
</html>
