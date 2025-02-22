<?php
require_once('../../private/config/config.php');
require_once(PRIVATE_PATH . '/core/initialize.php');

$page_title = 'Login';
$page_style = 'login';

$errors = [];
$username = '';
$password = '';

if(is_post_request()) {
    $args = [];
    $args['username'] = $_POST['username'] ?? '';
    $args['password'] = $_POST['password'] ?? '';

    $login_user = new User($args);
    if($login_user->verify_login()) {
        $session->login($login_user);
        redirect_to(url_for('/index.php'));
    } else {
        $errors = $login_user->errors;
        $username = $args['username'];
    }
}

include(SHARED_PATH . '/public_header.php');
?>
<link rel="stylesheet" href="<?php echo url_for('/assets/css/components/header.css'); ?>">
<link rel="stylesheet" href="<?php echo url_for('/assets/css/components/footer.css'); ?>">
<link rel="stylesheet" href="<?php echo url_for('/assets/css/components/forms.css'); ?>">

<div class="content">
    <?php echo display_errors($errors); ?>
    <?php echo display_session_message(); ?>

    <form class="form" action="<?php echo url_for('/auth/login.php'); ?>" method="post">
        <div class="form-group form-header">
            <h1>Login</h1>
        </div>
        
        <div class="form-group">
            <label class="form-label" for="username">Username</label>
            <input class="form-input" type="text" name="username" id="username" value="<?php echo h($username ?? ''); ?>" required>
        </div>

        <div class="form-group">
            <label class="form-label" for="password">Password</label>
            <input class="form-input" type="password" name="password" id="password" required>
        </div>

        <div class="form-group">
            <button type="submit" class="form-button" name="submit">Login</button>
        </div>
        
        <div class="form-footer">
            <p>Don't have an account? <a href="<?php echo url_for('/auth/register.php'); ?>">Register here</a></p>
        </div>
    </form>
</div>

<?php include(SHARED_PATH . '/footer.php'); ?>
