<?php
require_once('../../private/config/config.php');
require_once(PRIVATE_PATH . '/core/initialize.php');

$page_title = 'Register';

$errors = [];
$username = '';
$first_name = '';
$last_name = '';
$email = '';

if(is_post_request()) {
    // Get form data
    $args = [];
    $args['username'] = $_POST['username'] ?? '';
    $args['first_name'] = $_POST['first_name'] ?? '';
    $args['last_name'] = $_POST['last_name'] ?? '';
    $args['email'] = $_POST['email'] ?? '';
    $args['password'] = $_POST['password'] ?? '';
    $args['confirm_password'] = $_POST['confirm_password'] ?? '';

    // Store values for form repopulation
    $username = $args['username'];
    $first_name = $args['first_name'];
    $last_name = $args['last_name'];
    $email = $args['email'];

    // Validate all fields
    $errors = validate_user($args);

    // If no errors, create user
    if(empty($errors)) {
        $user = new User($args);
        if($user->save()) {
            $session->message("Registration successful! Please log in.", "success");
            redirect_to(url_for('/auth/login.php'));
        } else {
            // Get any additional validation errors from user object
            $errors = array_merge($errors, $user->errors);
        }
    }
}

include(SHARED_PATH . '/public_header.php');
?>
<link rel="stylesheet" href="<?php echo url_for('/assets/css/components/register.css'); ?>">

<div class="content">
    <?php echo display_errors($errors); ?>
    <?php echo display_session_message(); ?>

    <form class="form" action="<?php echo url_for('/auth/register.php'); ?>" method="post">
        <div class="form-group form-header">
            <h1>Register</h1>
        </div>

        <div class="form-group">
            <label class="form-label" for="username">Username</label>
            <input class="form-input" type="text" name="username" id="username" value="<?php echo h($username ?? ''); ?>" required>
        </div>

        <div class="form-group">
            <label class="form-label" for="first_name">First Name</label>
            <input class="form-input" type="text" name="first_name" id="first_name" value="<?php echo h($first_name ?? ''); ?>" required>
        </div>

        <div class="form-group">
            <label class="form-label" for="last_name">Last Name</label>
            <input class="form-input" type="text" name="last_name" id="last_name" value="<?php echo h($last_name ?? ''); ?>" required>
        </div>

        <div class="form-group">
            <label class="form-label" for="email">Email</label>
            <input class="form-input" type="email" name="email" id="email" value="<?php echo h($email ?? ''); ?>" required>
        </div>

        <div class="form-group">
            <label class="form-label" for="password">Password</label>
            <input class="form-input" type="password" name="password" id="password" required>
        </div>

        <div class="form-group">
            <label class="form-label" for="confirm_password">Confirm Password</label>
            <input class="form-input" type="password" name="confirm_password" id="confirm_password" required>
        </div>

        <button type="submit" class="form-button" name="submit">Register</button>

        <div class="form-footer">
            <p>Already have an account? <a href="<?php echo url_for('/auth/login.php'); ?>">Login here</a></p>
        </div>
    </form>
</div>

<?php include(SHARED_PATH . '/footer.php'); ?>
