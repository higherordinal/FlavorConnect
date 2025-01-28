<?php
require_once('../../private/initialize.php');
$page_title = 'Register';
include(SHARED_PATH . '/public_header.php');
?>
<link rel="stylesheet" href="<?php echo url_for('/assets/css/components/register.css'); ?>">

<div class="content">
    <h1>Create Account</h1>

    <?php echo display_errors($errors); ?>
    <?php echo display_message($session->message); ?>

    <form action="<?php echo url_for('/auth/register.php'); ?>" method="post">
        <div>
            <label for="username">Username</label>
            <input type="text" name="username" id="username" value="<?php echo h($username ?? ''); ?>" required>
        </div>

        <div>
            <label for="first_name">First Name</label>
            <input type="text" name="first_name" id="first_name" value="<?php echo h($first_name ?? ''); ?>" required>
        </div>

        <div>
            <label for="last_name">Last Name</label>
            <input type="text" name="last_name" id="last_name" value="<?php echo h($last_name ?? ''); ?>" required>
        </div>

        <div>
            <label for="email">Email</label>
            <input type="email" name="email" id="email" value="<?php echo h($email ?? ''); ?>" required>
        </div>

        <div>
            <label for="password">Password</label>
            <input type="password" name="password" id="password" required>
        </div>

        <div>
            <label for="confirm_password">Confirm Password</label>
            <input type="password" name="confirm_password" id="confirm_password" required>
        </div>

        <div>
            <input type="submit" name="submit" value="Register">
        </div>
    </form>

    <div>
        <p>Already have an account? <a href="<?php echo url_for('/auth/login.php'); ?>">Login here</a></p>
    </div>
</div>

<?php include(SHARED_PATH . '/footer.php'); ?>
