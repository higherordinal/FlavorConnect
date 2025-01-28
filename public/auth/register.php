<?php
require_once('../../private/initialize.php');
require_once('../../private/validation_functions.php');
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
