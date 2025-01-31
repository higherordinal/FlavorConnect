<?php
require_once('../private/core/initialize.php');
$page_title = 'About';

include(SHARED_PATH . '/public_header.php');
?>

<main class="container">
    <section class="about-section">
        <h1>About FlavorConnect</h1>
        <p>FlavorConnect is your ultimate destination for discovering, sharing, and connecting through the joy of cooking. Our platform brings together food enthusiasts from all walks of life, creating a vibrant community where culinary creativity knows no bounds.</p>
        
        <h2>Our Mission</h2>
        <p>To create a welcoming space where home cooks and food lovers can share their passion, learn from each other, and explore the diverse world of cooking.</p>
        
        <h2>Features</h2>
        <ul>
            <li>Share your favorite recipes with our community</li>
            <li>Discover new recipes from cooks around the world</li>
            <li>Save recipes to your favorites for easy access</li>
            <li>Connect with other food enthusiasts</li>
            <li>Get personalized recipe recommendations</li>
        </ul>
        
        <h2>Join Our Community</h2>
        <p>Whether you're a seasoned chef or just starting your culinary journey, FlavorConnect welcomes you. Sign up today to start sharing your recipes and connecting with fellow food lovers!</p>
        
        <div class="cta-buttons">
            <a href="<?php echo url_for('/signup.php'); ?>" class="btn btn-primary">Join Now</a>
            <a href="<?php echo url_for('/recipes/index.php'); ?>" class="btn btn-secondary">Browse Recipes</a>
        </div>
    </section>
</main>

<?php include(SHARED_PATH . '/footer.php'); ?>
