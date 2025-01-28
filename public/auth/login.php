<?php
require_once('../../private/initialize.php');
$page_title = 'Login';
include(SHARED_PATH . '/public_header.php');
?>
<link rel="stylesheet" href="<?php echo url_for('/assets/css/components/login.css'); ?>">

<div class="content">
    <h1>Login</h1>
    
    <?php echo display_errors($errors); ?>
    <?php echo display_message($session->message); ?>

    <form action="<?php echo url_for('/auth/login.php'); ?>" method="post">
        <div>
            <label for="username">Username</label>
            <input type="text" name="username" id="username" value="<?php echo h($username ?? ''); ?>" required>
        </div>

        <div>
            <label for="password">Password</label>
            <input type="password" name="password" id="password" required>
        </div>

        <div>
            <input type="submit" name="submit" value="Login">
        </div>
    </form>

    <div>
        <p>Don't have an account? <a href="<?php echo url_for('/auth/register.php'); ?>">Register here</a></p>
    </div>
</div>

<?php include(SHARED_PATH . '/footer.php'); ?>
