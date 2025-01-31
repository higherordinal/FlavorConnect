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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Base Styles -->
    <link rel="stylesheet" href="<?php echo url_for('/assets/css/style.css?v=' . time()); ?>">
    <link rel="stylesheet" href="<?php echo url_for('/assets/css/layout.css?v=' . time()); ?>">
    
    <!-- Component Styles -->
    <link rel="stylesheet" href="<?php echo url_for('/assets/css/components/header.css?v=' . time()); ?>">
    <link rel="stylesheet" href="<?php echo url_for('/assets/css/components/member-header.css?v=' . time()); ?>">
    <link rel="stylesheet" href="<?php echo url_for('/assets/css/components/footer.css?v=' . time()); ?>">
    
    <?php if($page_title === 'Home') { ?>
    <link rel="stylesheet" href="<?php echo url_for('/assets/css/pages/home-grid.css?v=' . time()); ?>">
    <?php } ?>
</head>
<body>
    <header class="header">
        <div class="header-grid">
            <!-- Logo and Site Name -->
            <div class="logo">
                <a href="<?php echo url_for('/dashboard.php'); ?>">
                    <span class="logo-the">The</span>
                    <h1>FlavorConnect</h1>
                </a>
            </div>

            <!-- Main Navigation -->
            <nav class="main-nav">
                <ul>
                    <li><a href="<?php echo url_for('/dashboard.php'); ?>" <?php echo $page_title === 'Dashboard' ? 'class="active"' : ''; ?>>Dashboard</a></li>
                    <li><a href="<?php echo url_for('/recipes/my-recipes.php'); ?>" <?php echo $page_title === 'My Recipes' ? 'class="active"' : ''; ?>>My Recipes</a></li>
                    <li><a href="<?php echo url_for('/recipes/favorites.php'); ?>" <?php echo $page_title === 'Favorites' ? 'class="active"' : ''; ?>>Favorites</a></li>
                    <li><a href="<?php echo url_for('/recipes/new.php'); ?>" <?php echo $page_title === 'Create Recipe' ? 'class="active"' : ''; ?>>Create Recipe</a></li>
                    <?php 
                    // Using cached admin status from session for performance
                    if($session->is_admin()) { 
                    ?>
                    <li><a href="<?php echo url_for('/admin/index.php'); ?>" <?php echo strpos($page_title, 'Admin') !== false ? 'class="active"' : ''; ?>>Admin</a></li>
                    <?php } ?>
                </ul>
            </nav>

            <!-- User Section -->
            <div class="user-section">
                <button class="user-menu-button">
                    <?php 
                    $user = User::find_by_id($session->get_user_id());
                    echo '<img src="' . url_for('/assets/images/avatars/default.png') . '" alt="Profile">';
                    ?>
                    <span><?php echo h($user->first_name); ?></span>
                </button>
                <div class="dropdown-menu">
                    <a href="<?php echo url_for('/profile.php'); ?>">Profile</a>
                    <a href="<?php echo url_for('/auth/logout.php'); ?>">Logout</a>
                </div>
            </div>
        </div>
    </header>
    <main class="main-content">
