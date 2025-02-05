<?php
if(!isset($page_title)) { $page_title = 'FlavorConnect'; }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FlavorConnect - <?php echo h($page_title); ?></title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <!-- Base Styles -->
    <link rel="stylesheet" href="<?php echo url_for('/assets/css/main.css?v=' . time()); ?>">
    
    <!-- Component Styles -->
    <link rel="stylesheet" href="<?php echo url_for('/assets/css/components/header.css?v=' . time()); ?>">
    <link rel="stylesheet" href="<?php echo url_for('/assets/css/components/member-header.css?v=' . time()); ?>">
    <link rel="stylesheet" href="<?php echo url_for('/assets/css/components/footer.css?v=' . time()); ?>">
    
    <?php if($page_title === 'Home') { ?>
    <link rel="stylesheet" href="<?php echo url_for('/assets/css/pages/home.css?v=' . time()); ?>">
    <?php } else if($page_title === 'Recipes') { ?>
    <link rel="stylesheet" href="<?php echo url_for('/assets/css/pages/recipe-gallery.css?v=' . time()); ?>">
    <?php } else if($page_title === 'recipe-form') { ?>
    <link rel="stylesheet" href="<?php echo url_for('/assets/css/components/recipe-form.css?v=' . time()); ?>">
    <?php } else { ?>
    <link rel="stylesheet" href="<?php echo url_for('/assets/css/pages/' . strtolower($page_title) . '.css?v=' . time()); ?>">
    <?php } ?>
    
    <!-- JavaScript -->
    <script src="<?php echo url_for('/assets/js/components/header.js'); ?>" defer></script>
    <script src="<?php echo url_for('/assets/js/components/member-header.js'); ?>" defer></script>
    <?php if($page_title === 'recipe-form') { ?>
    <script src="<?php echo url_for('/assets/js/pages/recipe-form.js'); ?>" defer></script>
    <?php } ?>
</head>
<body>
    <header class="header" role="banner">
        <div class="header-grid">
            <!-- Logo and Site Name -->
            <div class="logo">
                <a href="<?php echo url_for('/index.php'); ?>" aria-label="FlavorConnect Home">
                    <span class="logo-the">The</span>
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
                    <li><a href="<?php echo url_for('/recipes/favorites.php'); ?>" <?php echo $page_title === 'Favorites' ? 'class="active" aria-current="page"' : ''; ?>>Favorites</a></li>
                    <li><a href="<?php echo private_url_for('/recipes/new.php'); ?>" <?php echo $page_title === 'Create Recipe' ? 'class="active" aria-current="page"' : ''; ?>>Create Recipe</a></li>
                </ul>
            </nav>

            <!-- User Section -->
            <div class="user-section">
                <div class="user-menu">
                    <button class="user-menu-button" aria-expanded="false" aria-haspopup="true">
                        <?php 
                        $user = User::find_by_id($session->get_user_id());
                        $username = $user ? h($user->username) : 'Guest';
                        ?>
                        <span class="username"><?php echo $username; ?></span>
                    </button>
                    <div class="dropdown-menu">
                        <a href="<?php echo url_for('/profile/index.php'); ?>">Profile</a>
                        <a href="<?php echo url_for('/auth/logout.php'); ?>">Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <main class="main-content">
