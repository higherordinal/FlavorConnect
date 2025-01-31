    </main> <!-- Close .main-content from header.php -->
    
<footer>
    <div class="footer-info">
        <h3>About FlavorConnect</h3>
        <p>Connecting food enthusiasts with their perfect dining experiences.</p>
        <address>
            123 Foodie Lane<br>
            Cuisine City, FC 12345<br>
            Phone: (555) 123-4567
        </address>
    </div>

    <div class="footer-links">
        <h3>Quick Links</h3>
        <nav>
            <ul>
                <li><a href="<?php echo url_for('/index.php'); ?>">Home</a></li>
                <li><a href="<?php echo url_for('/about.php'); ?>">About Us</a></li>
            </ul>
        </nav>
    </div>

    <div class="footer-social">
        <h3>Connect With Us</h3>
        <nav>
            <ul>
                <li><a href="#">Facebook</a></li>
                <li><a href="#">Twitter</a></li>
                <li><a href="#">Instagram</a></li>
                <li><a href="#">LinkedIn</a></li>
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
