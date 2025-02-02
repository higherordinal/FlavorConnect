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
    <link rel="stylesheet" href="<?php echo url_for('/assets/css/main.css?v=' . time()); ?>">
    
    <!-- Component Styles -->
    <link rel="stylesheet" href="<?php echo url_for('/assets/css/components/header.css?v=' . time()); ?>">
    <link rel="stylesheet" href="<?php echo url_for('/assets/css/components/public-header.css?v=' . time()); ?>">
    <link rel="stylesheet" href="<?php echo url_for('/assets/css/components/footer.css?v=' . time()); ?>">
    <link rel="stylesheet" href="<?php echo url_for('/assets/css/components/forms.css?v=' . time()); ?>">
    
    <?php if($page_title === 'Home') { ?>
    <link rel="stylesheet" href="<?php echo url_for('/assets/css/pages/home.css?v=' . time()); ?>">
    <?php } ?>
    
    <?php if($page_title === 'About') { ?>
    <link rel="stylesheet" href="<?php echo url_for('/assets/css/pages/about.css?v=' . time()); ?>">
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
                <ul>
                    <li><a href="<?php echo url_for('/index.php'); ?>" <?php echo $page_title === 'Home' ? 'class="active" aria-current="page"' : ''; ?>>Home</a></li>
                    <li><a href="<?php echo url_for('/recipes/index.php'); ?>" <?php echo $page_title === 'Recipes' ? 'class="active" aria-current="page"' : ''; ?>>Recipes</a></li>
                    <li><a href="<?php echo url_for('/about.php'); ?>" <?php echo $page_title === 'About' ? 'class="active" aria-current="page"' : ''; ?>>About</a></li>
                </ul>
            </nav>

            <!-- Authentication Section -->
            <div class="auth-section">
                <div class="auth-buttons">
                    <a href="<?php echo url_for('/auth/login.php'); ?>" class="btn btn-outline">Login</a>
                    <a href="<?php echo url_for('/auth/register.php'); ?>" class="btn btn-primary">Sign Up</a>
                </div>
            </div>
        </div>
    </header>
    <main class="main-content">