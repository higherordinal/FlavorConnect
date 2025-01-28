<?php
if(!isset($page_title)) { $page_title = 'FlavorConnect'; }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FlavorConnect - <?php echo h($page_title); ?></title>
    
    <!-- Base Styles -->
    <link rel="stylesheet" href="<?php echo url_for('/assets/css/style.css'); ?>">
    <link rel="stylesheet" href="<?php echo url_for('/assets/css/layout.css'); ?>">
    
    <!-- Component Styles -->
    <link rel="stylesheet" href="<?php echo url_for('/assets/css/components/header.css'); ?>">
    <link rel="stylesheet" href="<?php echo url_for('/assets/css/components/member-header.css'); ?>">
    <link rel="stylesheet" href="<?php echo url_for('/assets/css/components/footer-grid.css'); ?>">
    
    <?php if($page_title === 'Home') { ?>
    <link rel="stylesheet" href="<?php echo url_for('/assets/css/pages/home-grid.css'); ?>">
    <?php } ?>
</head>
<body>
    <header class="main-header">
        <div class="header-grid">
            <!-- Logo and Site Name -->
            <div class="logo">
                <a href="<?php echo url_for('/dashboard.php'); ?>">
                    <h1>FlavorConnect</h1>
                </a>
            </div>

            <!-- Main Navigation -->
            <nav class="main-nav">
                <ul>
                    <li><a href="<?php echo url_for('/dashboard.php'); ?>">Dashboard</a></li>
                    <li><a href="<?php echo url_for('/recipes/my-recipes.php'); ?>">My Recipes</a></li>
                    <li><a href="<?php echo url_for('/recipes/favorites.php'); ?>">Favorites</a></li>
                    <li><a href="<?php echo url_for('/recipes/create.php'); ?>">Create Recipe</a></li>
                    <?php 
                    // Using cached admin status from session for performance
                    if($session->is_admin()) { 
                    ?>
                    <li><a href="<?php echo url_for('/admin/index.php'); ?>">Admin</a></li>
                    <?php } ?>
                </ul>
            </nav>

            <!-- User Section -->
            <div class="user-section">
                <div class="dropdown">
                    <button class="profile-button">
                        <?php 
                        $user = User::find_by_id($session->user_id);
                        echo h($user->first_name);
                        ?>
                        <span class="arrow-down">▼</span>
                    </button>
                    <div class="dropdown-content">
                        <a href="<?php echo url_for('/profile.php'); ?>">Profile</a>
                        <a href="<?php echo url_for('/settings.php'); ?>">Settings</a>
                        <a href="<?php echo url_for('/logout.php'); ?>">Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <main class="main-content">
