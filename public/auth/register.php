<?php
require_once('../../private/config/config.php');
require_once(PRIVATE_PATH . '/core/initialize.php');

$page_title = 'Register';
$page_style = 'auth';

$errors = [];
$username = '';
$first_name = '';
$last_name = '';
$email = '';

if(is_post_request()) {
    $args = [];
    $args['username'] = $_POST['username'] ?? '';
    $args['first_name'] = $_POST['first_name'] ?? '';
    $args['last_name'] = $_POST['last_name'] ?? '';
    $args['email'] = $_POST['email'] ?? '';
    $args['password'] = $_POST['password'] ?? '';
    $args['confirm_password'] = $_POST['confirm_password'] ?? '';

    $user = new User($args);
    if($user->save()) {
        $session->message('Registration successful! Please log in.');
        redirect_to(url_for('/auth/login.php'));
    } else {
        $errors = $user->errors;
        $username = $args['username'];
        $first_name = $args['first_name'];
        $last_name = $args['last_name'];
        $email = $args['email'];
    }
}

include(SHARED_PATH . '/public_header.php');
?>
<link rel="stylesheet" href="<?php echo url_for('/assets/css/components/header.css'); ?>">
<link rel="stylesheet" href="<?php echo url_for('/assets/css/components/footer.css'); ?>">
<link rel="stylesheet" href="<?php echo url_for('/assets/css/components/forms.css'); ?>">

<div class="content register-form">
    <?php echo display_errors($errors); ?>
    <?php echo display_session_message(); ?>

    <form class="form" action="<?php echo url_for('/auth/register.php'); ?>" method="post">
        <div class="form-group form-header">
            <h1>Register</h1>
        </div>

        <div class="form-group">
            <label class="form-label" for="username">Username</label>
            <input class="form-input <?php echo error_class('username', $errors); ?>" type="text" name="username" id="username" value="<?php echo h($username ?? ''); ?>" required>
            <?php echo display_error('username', $errors); ?>
        </div>

        <div class="form-group">
            <label class="form-label" for="first_name">First Name</label>
            <input class="form-input <?php echo error_class('first_name', $errors); ?>" type="text" name="first_name" id="first_name" value="<?php echo h($first_name ?? ''); ?>" required>
            <?php echo display_error('first_name', $errors); ?>
        </div>

        <div class="form-group">
            <label class="form-label" for="last_name">Last Name</label>
            <input class="form-input <?php echo error_class('last_name', $errors); ?>" type="text" name="last_name" id="last_name" value="<?php echo h($last_name ?? ''); ?>" required>
            <?php echo display_error('last_name', $errors); ?>
        </div>

        <div class="form-group">
            <label class="form-label" for="email">Email</label>
            <input class="form-input <?php echo error_class('email', $errors); ?>" type="email" name="email" id="email" value="<?php echo h($email ?? ''); ?>" required>
            <?php echo display_error('email', $errors); ?>
        </div>

        <div class="form-group">
            <label class="form-label" for="password">Password</label>
            <input class="form-input <?php echo error_class('password', $errors); ?>" type="password" name="password" id="password" required>
            <?php echo display_error('password', $errors); ?>
        </div>

        <div class="form-group">
            <label class="form-label" for="confirm_password">Confirm Password</label>
            <input class="form-input <?php echo error_class('confirm_password', $errors); ?>" type="password" name="confirm_password" id="confirm_password" required>
            <?php echo display_error('confirm_password', $errors); ?>
        </div>

        <div class="form-group button-container">
            <button type="submit" class="btn-primary" name="submit">Create Account</button>
        </div>

        <div class="form-footer">
            <p>Already have an account? <a href="<?php echo url_for('/auth/login.php'); ?>">Login here</a></p>
        </div>
    </form>
</div>

<?php include(SHARED_PATH . '/footer.php'); ?>
