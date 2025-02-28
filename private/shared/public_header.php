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
    
    <!-- Base Styles -->
    <link rel="stylesheet" href="<?php echo url_for('/assets/css/main.css'); ?>">
    
    <!-- Component Styles -->
    <link rel="stylesheet" href="<?php echo url_for('/assets/css/components/header.css'); ?>">
    <link rel="stylesheet" href="<?php echo url_for('/assets/css/components/public-header.css'); ?>">
    <link rel="stylesheet" href="<?php echo url_for('/assets/css/components/footer.css'); ?>">
    <link rel="stylesheet" href="<?php echo url_for('/assets/css/components/forms.css'); ?>">
    
    <!-- Page Specific Styles -->
    <?php if($page_style): ?>
        <link rel="stylesheet" href="<?php echo url_for('/assets/css/pages/' . $page_style . '.css'); ?>">
        <?php if(file_exists(PUBLIC_PATH . '/assets/css/components/' . $page_style . '.css')): ?>
            <link rel="stylesheet" href="<?php echo url_for('/assets/css/components/' . $page_style . '.css'); ?>">
        <?php endif; ?>
    <?php endif; ?>

    <!-- 404 Page Style -->
    <?php if($page_title === '404 - Page Not Found'): ?>
        <link rel="stylesheet" href="<?php echo url_for('/assets/css/pages/404.css'); ?>">
    <?php endif; ?>
    
    <!-- External Dependencies -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
                    <li><a href="<?php echo url_for('/about.php'); ?>" <?php echo $page_title === 'About' ? 'class="active" aria-current="page"' : ''; ?>>About</a></li>
                </ul>
            </nav>

            <!-- Auth Links -->
            <div class="auth-links">
                <a href="<?php echo url_for('/auth/login.php'); ?>" class="login">Log In</a>
                <a href="<?php echo url_for('/auth/register.php'); ?>" class="sign-up">Sign Up</a>
            </div>
        </div>
    </header>
    <main class="main-content">