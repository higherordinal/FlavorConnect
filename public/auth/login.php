<?php
require_once('../../private/config/config.php');
require_once(PRIVATE_PATH . '/core/initialize.php');

$page_title = 'Login';
$page_style = 'auth';

$errors = [];
$username = '';
$password = '';

if(is_post_request()) {
    $args = [];
    $args['username'] = $_POST['username'] ?? '';
    $args['password'] = $_POST['password'] ?? '';

    $errors = validate_login($args);
    if(empty($errors)) {
        $found_user = User::find_by_username($args['username']);
        if($found_user && $found_user->verify_password($args['password'])) {
            $session->login($found_user);
            redirect_to(url_for('/index.php'));
        } else {
            $errors['login'] = "Invalid username or password.";
            $username = $args['username'];
        }
    }
}

include(SHARED_PATH . '/public_header.php');
?>
<link rel="stylesheet" href="<?php echo url_for('/assets/css/components/header.css'); ?>">
<link rel="stylesheet" href="<?php echo url_for('/assets/css/components/footer.css'); ?>">
<link rel="stylesheet" href="<?php echo url_for('/assets/css/components/forms.css'); ?>">

<div class="content login-form">
    <?php echo display_errors($errors); ?>
    <?php echo display_session_message(); ?>

    <form class="form" action="<?php echo url_for('/auth/login.php'); ?>" method="post">
        <div class="form-group form-header">
            <h1>Login</h1>
        </div>
        
        <div class="form-group">
            <label class="form-label" for="username">Username</label>
            <input class="form-input <?php echo error_class('username', $errors); ?>" type="text" name="username" id="username" value="<?php echo h($username ?? ''); ?>" required data-error-message="Username cannot be blank">
            <?php echo display_error('username', $errors); ?>
        </div>

        <div class="form-group">
            <label class="form-label" for="password">Password</label>
            <input class="form-input <?php echo error_class('password', $errors); ?>" type="password" name="password" id="password" required data-error-message="Password cannot be blank">
            <?php echo display_error('password', $errors); ?>
        </div>

        <div class="form-group button-container">
            <button type="submit" class="btn-primary" name="submit">Sign In</button>
        </div>
        
        <div class="form-footer">
            <p>Don't have an account? <a href="<?php echo url_for('/auth/register.php'); ?>">Register here</a></p>
        </div>
    </form>
</div>

<?php include(SHARED_PATH . '/footer.php'); ?>
