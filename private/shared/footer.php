    </main> <!-- Close .main-content from header.php -->
    
<footer role="contentinfo">
    <!-- Add visually hidden h2 to fix heading hierarchy -->
    <h2 class="visually-hidden">Footer Information</h2>
    
    <div class="footer-info">
        <h3>About FlavorConnect</h3>
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
    if(isset($scripts) && is_array($scripts)) {
        foreach($scripts as $script) {
            echo '<script src="' . url_for('/assets/js/pages/' . $script . '.js?v=' . time()) . '"></script>';
        }
    }
    ?>
</body>
</html>
