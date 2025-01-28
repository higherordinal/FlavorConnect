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
    <link rel="stylesheet" href="<?php echo url_for('/assets/css/components/public-header.css'); ?>">
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
                <a href="<?php echo url_for('/index.php'); ?>">
                    <h1>FlavorConnect</h1>
                </a>
            </div>

            <!-- Main Navigation -->
            <nav class="main-nav">
                <ul>
                    <li><a href="<?php echo url_for('/index.php'); ?>">Home</a></li>
                    <li><a href="<?php echo url_for('/recipes/browse.php'); ?>">Browse Recipes</a></li>
                    <li><a href="<?php echo url_for('/about.php'); ?>">About</a></li>
                    <li><a href="<?php echo url_for('/contact.php'); ?>">Contact</a></li>
                </ul>
            </nav>

            <!-- Authentication Section -->
            <div class="auth-section">
                <div class="auth-buttons">
                    <a href="<?php echo url_for('/login.php'); ?>" class="login-button">Login</a>
                    <a href="<?php echo url_for('/signup.php'); ?>" class="signup-button">Sign Up</a>
                </div>
            </div>
        </div>
    </header>
    <main class="main-content">
