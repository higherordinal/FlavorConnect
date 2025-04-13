<?php
if(!isset($page_title)) { $page_title = 'FlavorConnect'; }
if(!isset($page_style)) { $page_style = ''; }
if(!isset($component_styles)) { $component_styles = []; }
if(!isset($page_scripts)) { $page_scripts = []; }
if(!isset($component_scripts)) { $component_scripts = []; }
if(!isset($utility_scripts)) { $utility_scripts = []; }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FlavorConnect - <?php echo h($page_title); ?></title>
    
    <!-- SEO Meta Tags -->
    <?php include(SHARED_PATH . '/seo_meta.php'); ?>
    
    <!-- Favicon -->
    <link rel="icon" href="<?php echo url_for('/assets/images/flavorconnect_favicon.ico'); ?>" type="image/x-icon">
    <link rel="shortcut icon" href="<?php echo url_for('/assets/images/flavorconnect_favicon.ico'); ?>" type="image/x-icon">
    
    <!-- Base Styles -->
    <link rel="stylesheet" href="<?php echo url_for('/assets/css/main.css'); ?>">
    
    <!-- Component Styles -->
    <link rel="stylesheet" href="<?php echo url_for('/assets/css/components/header.css?v=1.0'); ?>">
    <link rel="stylesheet" href="<?php echo url_for('/assets/css/components/public-header.css?v=1.0'); ?>">
    <link rel="stylesheet" href="<?php echo url_for('/assets/css/components/footer.css?v=1.0'); ?>">
    <link rel="stylesheet" href="<?php echo url_for('/assets/css/components/recipe-card.css?v=1.0'); ?>">
    <link rel="stylesheet" href="<?php echo url_for('/assets/css/components/unified-navigation.css?v=1.0'); ?>">
    
    <!-- Component Styles (Dynamically Included) -->
    <?php 
    if(!empty($component_styles)) {
        foreach($component_styles as $component) {
            echo '<link rel="stylesheet" href="' . url_for('/assets/css/components/' . $component . '.css?v=1.0') . '">'; 
        }
    }
    ?>
    
    <!-- Page Specific Styles -->
    <?php if(isset($page_style)) { ?>
    <link rel="stylesheet" href="<?php echo url_for('/assets/css/pages/' . $page_style . '.css?v=1.0'); ?>">
    <?php } ?>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Utility Scripts are now loaded in footer.php -->
    <?php 
    // Add form-validation.js to utility scripts
    $utility_scripts[] = 'form-validation';
    ?>
    
    <!-- Global Configuration -->
    <script>
        // Initialize FlavorConnect namespace
        window.FlavorConnect = window.FlavorConnect || {};
        
        // Global configuration
        window.FlavorConnect.config = {
            baseUrl: '<?php echo url_for('/'); ?>',
            isLoggedIn: <?php echo $session->is_logged_in() ? 'true' : 'false'; ?>,
            userId: <?php echo $session->is_logged_in() ? $session->get_user_id() : 'null'; ?>,
            csrfToken: '<?php echo $session->get_csrf_token(); ?>'
        };
    </script>
    
    <?php 
    // Add core utility scripts
    $utility_scripts[] = 'common';
    $utility_scripts[] = 'back-link';
    ?>
</head>
<body>
    <header class="header" role="banner">
        <div class="header-grid">
            <!-- Logo and Site Name -->
            <div class="logo">
                <a href="<?php echo url_for('/index.php'); ?>" aria-label="FlavorConnect Home">
                    <span class="logo-the"><i class="fas fa-utensils"></i>The</span>
                    <h1>FlavorConnect</h1>
                </a>
            </div>

            <!-- Main Navigation -->
            <nav class="main-nav" role="navigation" aria-label="Main navigation">
                <input type="checkbox" id="nav-toggle" class="nav-toggle" aria-hidden="true">
                <label for="nav-toggle" class="nav-toggle-label" aria-label="Toggle menu">
                    <span class="hamburger"></span>
                    <span class="hamburger"></span>
                    <span class="hamburger"></span>
                </label>
                <?php
                // Get the ref parameter for consistent back navigation
                $ref_param = get_ref_parameter();
                ?>
                <ul>
                    <li><a href="<?php echo url_for('/index.php' . $ref_param); ?>" <?php echo $page_title === 'Home' ? 'class="active" aria-current="page"' : ''; ?>><i class="fas fa-home" aria-hidden="true"></i> Home</a></li>
                    <?php 
                    // Check if current page is in recipes section
                    $is_recipes_page = (strpos($_SERVER['PHP_SELF'], '/recipes/') !== false);
                    ?>
                    <li><a href="<?php echo url_for('/recipes/index.php' . $ref_param); ?>" <?php echo $is_recipes_page ? 'class="active" aria-current="page"' : ''; ?>><i class="fas fa-utensils" aria-hidden="true"></i> Recipes</a></li>
                    <li><a href="<?php echo url_for('/about.php' . $ref_param); ?>" <?php echo $page_title === 'About' ? 'class="active" aria-current="page"' : ''; ?>><i class="fas fa-info-circle" aria-hidden="true"></i> About</a></li>
                </ul>
            </nav>

            <!-- Auth Links -->
            <div class="auth-links">
                <a href="<?php echo url_for('/auth/login.php'); ?>" class="login"><i class="fas fa-sign-in-alt" aria-hidden="true"></i> Log In</a>
                <a href="<?php echo url_for('/auth/register.php'); ?>" class="sign-up"><i class="fas fa-user-plus" aria-hidden="true"></i> Sign Up</a>
            </div>
        </div>
    </header>
    <main class="main-content">