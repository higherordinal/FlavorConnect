<?php
require_once('../private/core/initialize.php');
$page_title = 'About';

include(SHARED_PATH . '/public_header.php');
?>

<main>
    <section class="about-section">
        <div class="about-hero">
            <div class="about-hero-content">
                <h1>About FlavorConnect</h1>
                <p>FlavorConnect is your ultimate destination for discovering, sharing, and connecting through the joy of cooking. Our platform brings together food enthusiasts from all walks of life, creating a vibrant community where culinary creativity knows no bounds.</p>
            </div>
            <div class="about-hero-image">
                <img src="<?php echo url_for('/assets/images/about-hero-img.jpg'); ?>" alt="Cooking together" class="about-hero-img">
            </div>
        </div>

        <div class="mission-section">
            <h2>Our Mission</h2>
            <p>To create a welcoming space where home cooks and food lovers can share their passion, learn from each other, and explore the diverse world of cooking.</p>
        </div>

        <div class="features-section">
            <h2>Features</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <i class="fas fa-share-alt feature-icon"></i>
                    <h3>Share Recipes</h3>
                    <p>Share your favorite recipes with our growing community of food enthusiasts.</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-globe feature-icon"></i>
                    <h3>Global Discovery</h3>
                    <p>Discover new recipes from talented cooks around the world.</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-bookmark feature-icon"></i>
                    <h3>Save Favorites</h3>
                    <p>Save recipes to your favorites for quick and easy access.</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-users feature-icon"></i>
                    <h3>Connect</h3>
                    <p>Connect with other food enthusiasts and share your culinary journey.</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-lightbulb feature-icon"></i>
                    <h3>Get Inspired</h3>
                    <p>Get personalized recipe recommendations based on your preferences.</p>
                </div>
            </div>
        </div>

        <div class="join-section">
            <h2>Join Our Community</h2>
            <p>Whether you're a seasoned chef or just starting your culinary journey, FlavorConnect welcomes you. Sign up today to start sharing your recipes and connecting with fellow food lovers!</p>
            <div class="cta-buttons">
                <a href="<?php echo url_for('/auth/register.php'); ?>" class="btn btn-primary">Join Now</a>
                <a href="<?php echo url_for('/recipes/index.php'); ?>" class="btn btn-secondary">Browse Recipes</a>
            </div>
        </div>
    </section>
</main>

<?php include(SHARED_PATH . '/footer.php'); ?>
