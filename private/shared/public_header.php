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
    
    <!-- Core Scripts and Styles -->
    <?php
    // Ensure essential utility scripts are available sitewide
    // These scripts provide core functionality used across all pages
    $utility_scripts = array_merge(['common', 'back-link'], $utility_scripts);
    
    // Include essential component styles for the public interface
    // These styles are required for the basic page structure
    $component_styles = array_merge([
        'header',
        'public-header',
        'footer',
        'unified-navigation'
    ], $component_styles);
    ?>
    
    <!-- Dynamic Component Styles -->
    <?php 
    if(!empty($component_styles)) {
        foreach($component_styles as $component) {
            $css_file = '/assets/css/components/' . $component . '.css';
            $css_path = PUBLIC_PATH . $css_file;
            $css_version = file_exists($css_path) ? filemtime($css_path) : time();
            echo '<link rel="stylesheet" href="' . url_for($css_file) . '?v=' . $css_version . '">'; 
        }
    }
    ?>
    
    <!-- Page Specific Styles -->
    <?php if(isset($page_style)) { ?>
    <?php
$css_file = '/assets/css/pages/' . $page_style . '.css';
$css_path = PUBLIC_PATH . $css_file;
$css_version = file_exists($css_path) ? filemtime($css_path) : time();
?>
<link rel="stylesheet" href="<?php echo url_for($css_file) . '?v=' . $css_version; ?>">
    <?php } ?>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Global Configuration -->
    <script>
        // Initialize FlavorConnect namespace and structure
        window.FlavorConnect = window.FlavorConnect || {};
        window.FlavorConnect.utils = window.FlavorConnect.utils || {};
        window.FlavorConnect.components = window.FlavorConnect.components || {};
        window.FlavorConnect.pages = window.FlavorConnect.pages || {};
        
        // Global configuration
        window.FlavorConnect.config = {
            baseUrl: '<?php echo url_for('/'); ?>',
            isLoggedIn: <?php echo $session->is_logged_in() ? 'true' : 'false'; ?>,
            userId: <?php echo $session->is_logged_in() ? $session->get_user_id() : 'null'; ?>,
            csrfToken: '<?php echo $session->get_csrf_token(); ?>',
            apiBaseUrl: '<?php echo url_for('/api'); ?>',
            currentPage: '<?php echo $_SERVER['REQUEST_URI']; ?>',
            isFavoritesPage: false,
            debug: <?php echo defined('DEBUG_MODE') && DEBUG_MODE ? 'true' : 'false'; ?>
        };
    </script>
    
    <!-- JavaScript polyfills and compatibility fixes are loaded in the footer -->
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