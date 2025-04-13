<?php
if(!isset($page_title)) { $page_title = 'FlavorConnect'; }
if(!isset($page_style)) { $page_style = ''; }
if(!isset($component_styles)) { $component_styles = []; }
if(!isset($page_scripts)) { $page_scripts = []; }
if(!isset($component_scripts)) { $component_scripts = []; }
if(!isset($utility_scripts)) { $utility_scripts = []; }
?>

<!DOCTYPE html>
<html lang="en" class="no-js">
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
    <link rel="stylesheet" href="<?php echo url_for('/assets/css/main.css?v=' . time()); ?>">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Utility Scripts are now loaded in footer.php -->
    <?php 
    // Add form-validation.js to utility scripts
    $utility_scripts[] = 'form-validation';
    ?>
    
    <!-- JavaScript Detection -->
    <script>
        // Add 'js' class to html element if JavaScript is enabled
        document.documentElement.className = document.documentElement.className.replace('no-js', 'js');
    </script>
    
    <!-- Component Styles -->
    <link rel="stylesheet" href="<?php echo url_for('/assets/css/components/header.css?v=1.0'); ?>">
    <link rel="stylesheet" href="<?php echo url_for('/assets/css/components/member-header.css?v=1.0'); ?>">
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
    
    <!-- Global Configuration -->
    <script>
        // Initialize FlavorConnect namespace
        window.FlavorConnect = window.FlavorConnect || {};
        
        // Global configuration
        window.FlavorConnect.config = {
            baseUrl: '<?php echo url_for('/'); ?>',
            isLoggedIn: <?php echo $session->is_logged_in() ? 'true' : 'false'; ?>,
            userId: <?php echo $session->is_logged_in() ? $session->get_user_id() : 'null'; ?>,
            csrfToken: '<?php echo $session->get_csrf_token(); ?>',
            apiBaseUrl: '<?php echo url_for('/api'); ?>',
            currentPage: '<?php echo $_SERVER['REQUEST_URI']; ?>',
            isFavoritesPage: <?php echo (strpos($_SERVER['REQUEST_URI'], '/users/favorites.php') !== false) ? 'true' : 'false'; ?>,
            debug: <?php echo defined('DEBUG_MODE') && DEBUG_MODE ? 'true' : 'false'; ?>
        };
        
        <?php
        // Check if we're on a recipe page and capture the recipe ID for navigation
        $is_recipe_show_page = strpos($_SERVER['PHP_SELF'], '/recipes/show.php') !== false;
        $recipe_id = $is_recipe_show_page && isset($_GET['id']) ? $_GET['id'] : null;
        ?>
        // Add recipe context if we're on a recipe page
        <?php if($recipe_id): ?>
        window.FlavorConnect.config.recipeContext = {
            id: <?php echo $recipe_id; ?>,
            page: '<?php echo $_SERVER['REQUEST_URI']; ?>'
        };
        <?php endif; ?>
        
        // Log configuration in debug mode
        if (window.FlavorConnect.config.debug) {
            console.log('FlavorConnect Config:', window.FlavorConnect.config);
        }
    </script>
    
    <?php 
    // Add core utility scripts
    $utility_scripts[] = 'common';
    $utility_scripts[] = 'back-link';
    
    // Add component scripts
    $component_scripts[] = 'recipe-favorite';
    $component_scripts[] = 'member-header';
    
    // Add page-specific scripts
    if($page_style === 'recipe-form') {
        $page_scripts[] = 'recipe-form';
    }
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
                // Prepare recipe reference parameter if we're on a recipe page
                $recipe_ref = '';
                if($is_recipe_show_page && isset($_GET['id'])) {
                    $recipe_ref = '?ref=recipe&recipe_id=' . $_GET['id'];
                }
                ?>
                <?php
                // Get the ref parameter for consistent back navigation
                $ref_param = get_ref_parameter();
                ?>
                <ul>
                    <li><a href="<?php echo url_for('/index.php' . $ref_param); ?>" <?php echo $page_title === 'Home' ? 'class="active" aria-current="page"' : ''; ?>><i class="fas fa-home" aria-hidden="true"></i> Home</a></li>
                    <?php 
                    // Check if current page is in recipes section (but not new.php)
                    $is_recipes_page = (strpos($_SERVER['PHP_SELF'], '/recipes/') !== false && 
                                      strpos($_SERVER['PHP_SELF'], '/recipes/new.php') === false);
                    ?>
                    <li><a href="<?php echo url_for('/recipes/index.php' . $ref_param); ?>" <?php echo $is_recipes_page ? 'class="active" aria-current="page"' : ''; ?>><i class="fas fa-utensils" aria-hidden="true"></i> Recipes</a></li>
                    <?php 
                    // Check if current page is favorites
                    $is_favorites_page = (strtolower($page_title) === 'favorites' || strpos($_SERVER['PHP_SELF'], '/users/favorites.php') !== false);
                    ?>
                    <li><a href="<?php echo url_for('/users/favorites.php' . $ref_param); ?>" <?php echo $is_favorites_page ? 'class="active" aria-current="page"' : ''; ?>><i class="fas fa-heart" aria-hidden="true"></i> Favorites</a></li>
                    <?php
                    // Check if current page is create recipe (only new.php)
                    $is_create_recipe_page = (strpos($_SERVER['PHP_SELF'], '/recipes/new.php') !== false || 
                                            $page_title === 'Create Recipe');
                    ?>
                    <li><a href="<?php echo url_for('/recipes/new.php' . $ref_param); ?>" <?php echo $is_create_recipe_page ? 'class="active" aria-current="page"' : ''; ?>><i class="fas fa-plus-circle" aria-hidden="true"></i> Create Recipe</a></li>
                    <?php
                    // Check if current page is about page
                    $is_about_page = (strpos($_SERVER['PHP_SELF'], '/about.php') !== false || $page_title === 'About');
                    
                    // Get the ref parameter for consistent back navigation
                    $ref_param = get_ref_parameter();
                    ?>
                    <li><a href="<?php echo url_for('/about.php' . $ref_param); ?>" <?php echo $is_about_page ? 'class="active" aria-current="page"' : ''; ?>><i class="fas fa-info-circle" aria-hidden="true"></i> About</a></li>
                    <?php if($session->is_admin() || $session->is_super_admin()) { 
                        // Check if current page is in admin section
                        $is_admin_page = strpos($_SERVER['PHP_SELF'], '/admin/') !== false;
                    ?>
                    <li>
                        <a href="<?php echo url_for('/admin/index.php?ref_page=' . urlencode($_SERVER['REQUEST_URI']) . ($is_recipe_show_page ? '&ref=recipe&recipe_id=' . $_GET['id'] : '')); ?>" <?php echo $is_admin_page ? 'class="active" aria-current="page"' : ''; ?>>
                            <i class="fas fa-cog" aria-hidden="true"></i> Admin
                        </a>
                    </li>
                    <?php } ?>
                </ul>
            </nav>

            <!-- User Section -->
            <div class="user-section">
                <?php 
                $current_user = User::find_by_id($session->get_user_id());
                $username = $current_user ? h($current_user->username) : 'Guest';
                $show_dropdown = isset($_GET['menu']) && $_GET['menu'] === 'user';
                ?>
                
                <!-- JavaScript-enhanced dropdown (hidden with CSS if JS is disabled) -->
                <div class="user-menu js-enabled">
                    <button class="user-menu-button" aria-expanded="false" aria-haspopup="true">
                        <span class="username"><i class="fas fa-user-circle" aria-hidden="true"></i> <?php echo $username; ?></span>
                    </button>
                    <div class="dropdown-menu">
                        <a href="<?php echo url_for('/users/profile.php' . ($is_recipe_show_page ? $recipe_ref : '')); ?>"><i class="fas fa-user" aria-hidden="true"></i> Profile</a>
                        <?php if($session->is_admin() || $session->is_super_admin()) { ?>
                        <a href="<?php echo url_for('/admin/index.php?ref_page=' . urlencode($_SERVER['REQUEST_URI']) . ($is_recipe_show_page ? '&ref=recipe&recipe_id=' . $_GET['id'] : '')); ?>" class="dropdown-item">
                            <i class="fas fa-cog" aria-hidden="true"></i>
                            Admin (Dashboard)
                        </a>
                        <?php } ?>
                        <a href="<?php echo url_for('/auth/logout.php'); ?>"><i class="fas fa-sign-out-alt" aria-hidden="true"></i> Logout</a>
                    </div>
                </div>
                
                <!-- No-JS fallback (hidden with CSS if JS is enabled) -->
                <div class="user-menu-fallback js-disabled">
                    <a href="<?php echo url_for($_SERVER['REQUEST_URI'] . (strpos($_SERVER['REQUEST_URI'], '?') !== false ? '&' : '?') . 'menu=user'); ?>" class="user-menu-link">
                        <span class="username"><i class="fas fa-user-circle" aria-hidden="true"></i> <?php echo $username; ?></span>
                    </a>
                    <?php if($show_dropdown): ?>
                    <div class="dropdown-menu show">
                        <a href="<?php echo url_for('/users/profile.php'); ?>"><i class="fas fa-user" aria-hidden="true"></i> Profile</a>
                        <?php if($session->is_admin() || $session->is_super_admin()) { ?>
                        <a href="<?php echo url_for('/admin/index.php?ref_page=' . urlencode($_SERVER['REQUEST_URI'])); ?>" class="dropdown-item">
                            <i class="fas fa-cog" aria-hidden="true"></i>
                            Admin (Dashboard)
                        </a>
                        <?php } ?>
                        <a href="<?php echo url_for('/auth/logout.php'); ?>"><i class="fas fa-sign-out-alt" aria-hidden="true"></i> Logout</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>
    <main class="main-content">
