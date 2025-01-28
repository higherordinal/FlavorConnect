<?php
require_once('../../private/initialize.php');
require_once('../../private/validation_functions.php');
$page_title = 'Login';

$errors = [];
$username = '';

if(is_post_request()) {
    $args = [];
    $args['username'] = $_POST['username'] ?? '';
    $args['password'] = $_POST['password'] ?? '';

    // Store username for form repopulation
    $username = $args['username'];

    // Basic validations
    $errors = validate_login($args);

    // If no errors, try to login
    if(empty($errors)) {
        $user = User::find_by_username($args['username']);
        if($user && $user->verify_password($args['password'])) {
            if($user->is_active) {
                $session->login($user);
                redirect_to(url_for('/index.php'));
            } else {
                $errors['account'] = "Your account has been deactivated. Please contact an administrator.";
            }
        } else {
            $errors['login'] = "Invalid username or password.";
        }
    }
}

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
