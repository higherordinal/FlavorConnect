<?php
require_once('../../private/core/initialize.php');

$page_title = 'Register';
$page_style = 'auth';
$component_styles = ['forms'];

// Scripts
$utility_scripts = ['common', 'form-validation'];
$page_scripts = ['auth'];

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

<div class="content register-form">
    <?php echo display_errors($errors); ?>
    <?php echo display_session_message(); ?>

    <form class="form" action="<?php echo url_for('/auth/register.php'); ?>" method="post">
        <div class="form-group form-header">
            <h1>Register</h1>
        </div>

        <div class="form-group">
            <label class="form-label" for="username">Username</label>
            <input class="form-control <?php echo error_class('username', $errors); ?>" type="text" name="username" id="username" value="<?php echo h($username ?? ''); ?>" autocomplete="username" required data-error-message="Username cannot be blank">
            <?php echo display_error('username', $errors); ?>
        </div>

        <div class="name-fields-container">
            <div class="form-group">
                <label class="form-label" for="first_name">First Name</label>
                <div class="input-container">
                    <input class="form-control <?php echo error_class('first_name', $errors); ?>" type="text" name="first_name" id="first_name" value="<?php echo h($first_name ?? ''); ?>" autocomplete="given-name" required data-error-message="First name cannot be blank">
                    <?php echo display_error('first_name', $errors); ?>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="last_name">Last Name</label>
                <div class="input-container">
                    <input class="form-control <?php echo error_class('last_name', $errors); ?>" type="text" name="last_name" id="last_name" value="<?php echo h($last_name ?? ''); ?>" autocomplete="family-name" required data-error-message="Last name cannot be blank">
                    <?php echo display_error('last_name', $errors); ?>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="email">Email</label>
            <input class="form-control <?php echo error_class('email', $errors); ?>" type="email" name="email" id="email" value="<?php echo h($email ?? ''); ?>" autocomplete="email" required data-error-message="Email cannot be blank">
            <?php echo display_error('email', $errors); ?>
        </div>

        <div class="form-group">
            <label class="form-label" for="password">Password</label>
            <input class="form-control <?php echo error_class('password', $errors); ?>" type="password" name="password" id="password" autocomplete="new-password" required data-error-message="Password cannot be blank">
            <?php echo display_error('password', $errors); ?>
            <div class="password-requirements validation-requirements" id="existing-password-requirements" style="display: none;">
                <p class="requirement-heading">Password must contain:</p>
                <ul>
                    <li id="length-check"><span class="check-icon">✓</span> At least 8 characters</li>
                    <li id="uppercase-check"><span class="check-icon">✓</span> At least one uppercase letter</li>
                    <li id="lowercase-check"><span class="check-icon">✓</span> At least one lowercase letter</li>
                    <li id="number-check"><span class="check-icon">✓</span> At least one number</li>
                </ul>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="confirm_password">Confirm Password</label>
            <input class="form-control <?php echo error_class('confirm_password', $errors); ?>" type="password" name="confirm_password" id="confirm_password" autocomplete="new-password" required data-error-message="Please confirm your password">
            <?php echo display_error('confirm_password', $errors); ?>
            <div id="password-match" class="password-match validation-message">Passwords match</div>
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

