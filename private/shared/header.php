<?php
if(!isset($page_title)) { $page_title = 'FlavorConnect'; }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FlavorConnect - <?php echo h($page_title); ?></title>
    <link rel="stylesheet" href="<?php echo url_for('/stylesheets/style.css'); ?>">
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
                    <li><a href="<?php echo url_for('/recipes/index.php'); ?>">Recipes</a></li>
                    <li><a href="<?php echo url_for('/about.php'); ?>">About</a></li>
                </ul>
            </nav>

            <!-- User Authentication Section -->
            <div class="auth-section">
                <?php if(is_logged_in()) { ?>
                    <div class="dropdown">
                        <button class="profile-button">My Profile</button>
                        <div class="dropdown-content">
                            <a href="<?php echo url_for('/users/dashboard.php'); ?>">Dashboard</a>
                            <?php if(is_admin()) { ?>
                                <a href="<?php echo url_for('/admin/dashboard.php'); ?>">Admin Dashboard</a>
                            <?php } ?>
                            <a href="<?php echo url_for('/logout.php'); ?>">Logout</a>
                        </div>
                    </div>
                <?php } else { ?>
                    <div class="auth-buttons">
                        <a href="<?php echo url_for('/login.php'); ?>" class="login-button">Login</a>
                        <a href="<?php echo url_for('/signup.php'); ?>" class="signup-button">Sign Up</a>
                    </div>
                <?php } ?>
            </div>
        </div>
    </header>

    <main class="main-content">
        <!-- Page-specific content will be inserted here -->
