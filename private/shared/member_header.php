<?php
if(!isset($page_title)) { $page_title = 'FlavorConnect'; }
if(!isset($page_style)) { $page_style = ''; }
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
    
    <link rel="stylesheet" href="<?php echo url_for('/assets/css/main.css'); ?>">
    <?php if($page_title === '404 - Page Not Found'): ?>
    <link rel="stylesheet" href="<?php echo url_for('/assets/css/pages/404.css'); ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Component Styles -->
    <link rel="stylesheet" href="<?php echo url_for('/assets/css/components/header.css?v=' . time()); ?>">
    <link rel="stylesheet" href="<?php echo url_for('/assets/css/components/member-header.css?v=' . time()); ?>">
    <link rel="stylesheet" href="<?php echo url_for('/assets/css/components/footer.css?v=' . time()); ?>">
    <link rel="stylesheet" href="<?php echo url_for('/assets/css/components/forms.css?v=' . time()); ?>">
    <link rel="stylesheet" href="<?php echo url_for('/assets/css/components/recipe-favorite.css?v=' . time()); ?>">
    
    <!-- Page Specific Styles -->
    <?php if(isset($page_style)) { ?>
    <link rel="stylesheet" href="<?php echo url_for('/assets/css/pages/' . $page_style . '.css?v=' . time()); ?>">
    <?php } ?>
    
    <!-- Global Configuration -->
    <script>
        // Initialize FlavorConnect namespace
        window.FlavorConnect = window.FlavorConnect || {};
        
        // Global configuration
        window.FlavorConnect.config = {
            baseUrl: '<?php echo url_for('/'); ?>',
            apiBaseUrl: '<?php echo url_for('/api/'); ?>',
            isLoggedIn: <?php echo $session->is_logged_in() ? 'true' : 'false'; ?>,
            userId: <?php echo $session->is_logged_in() ? $session->get_user_id() : 'null'; ?>,
            csrfToken: '<?php echo $session->get_csrf_token(); ?>'
        };
        
        // For backward compatibility
        window.initialUserData = {
            isLoggedIn: window.FlavorConnect.config.isLoggedIn,
            userId: window.FlavorConnect.config.userId,
            apiBaseUrl: window.FlavorConnect.config.apiBaseUrl
        };
        
        // Debug configuration to console
        console.log('FlavorConnect Config:', window.FlavorConnect.config);
    </script>
    
    <!-- Core Utility Scripts -->
    <script src="<?php echo url_for('/assets/js/utils/api.js?v=' . time()); ?>"></script>
    <script src="<?php echo url_for('/assets/js/utils/common.js?v=' . time()); ?>"></script>
    <script src="<?php echo url_for('/assets/js/utils/favorites.js?v=' . time()); ?>"></script>
    
    <!-- Component Scripts -->
    <script src="<?php echo url_for('/assets/js/components/header.js?v=' . time()); ?>" defer></script>
    <script src="<?php echo url_for('/assets/js/components/member-header.js?v=' . time()); ?>" defer></script>
    <?php if($page_style === 'recipe-form') { ?>
    <script src="<?php echo url_for('/assets/js/pages/recipe-form.js?v=' . time()); ?>" defer></script>
    <?php } ?>
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
                <ul>
                    <li><a href="<?php echo url_for('/index.php'); ?>" <?php echo $page_title === 'Home' ? 'class="active" aria-current="page"' : ''; ?>>Home</a></li>
                    <li><a href="<?php echo url_for('/recipes/index.php'); ?>" <?php echo $page_title === 'Recipes' ? 'class="active" aria-current="page"' : ''; ?>>Recipes</a></li>
                    <li><a href="<?php echo url_for('/users/favorites.php'); ?>" <?php echo $page_title === 'favorites' ? 'class="active" aria-current="page"' : ''; ?>>Favorites</a></li>
                    <li><a href="<?php echo url_for('/recipes/new.php'); ?>" <?php echo $page_title === 'Create Recipe' ? 'class="active" aria-current="page"' : ''; ?>>Create Recipe</a></li>
                    <?php if($session->is_admin() || $session->is_super_admin()) { ?>
                    <li class="nav-item">
                        <a href="<?php echo url_for('/admin/index.php'); ?>" class="nav-link">
                            <i class="fas fa-cog"></i> Admin
                        </a>
                    </li>
                    <?php } ?>
                </ul>
            </nav>

            <!-- User Section -->
            <div class="user-section">
                <div class="user-menu">
                    <button class="user-menu-button" aria-expanded="false" aria-haspopup="true">
                        <?php 
                        $current_user = User::find_by_id($session->get_user_id());
                        $username = $current_user ? h($current_user->username) : 'Guest';
                        ?>
                        <span class="username"><?php echo $username; ?></span>
                    </button>
                    <div class="dropdown-menu">
                        <a href="<?php echo url_for('/users/profile.php'); ?>">Profile</a>
                        <?php if($session->is_admin() || $session->is_super_admin()) { ?>
                        <a href="<?php echo url_for('/admin/index.php'); ?>" class="dropdown-item">
                            <i class="fas fa-cog"></i>
                            Admin (Dashboard)
                        </a>
                        <?php } ?>
                        <a href="<?php echo url_for('/auth/logout.php'); ?>">Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <main class="main-content">
