<?php
// Prevent this file from being accessed directly
if(!isset($user)) {
    redirect_to(url_for('/admin/users/index.php'));
}

// Determine if this is a new user form
$is_new = !isset($user->user_id);
?>

<div class="form-group">
    <label for="username">Username</label>
    <input type="text" name="user[username]" id="username" value="<?php echo h($user->username); ?>" placeholder="Enter username" class="<?php echo error_class('username', $user->errors); ?>" required>
    <?php echo display_error('username', $user->errors); ?>
</div>

<div class="form-group">
    <label for="first_name">First Name</label>
    <input type="text" name="user[first_name]" id="first_name" value="<?php echo h($user->first_name); ?>" placeholder="Enter first name" class="<?php echo error_class('first_name', $user->errors); ?>" required>
    <?php echo display_error('first_name', $user->errors); ?>
</div>

<div class="form-group">
    <label for="last_name">Last Name</label>
    <input type="text" name="user[last_name]" id="last_name" value="<?php echo h($user->last_name); ?>" placeholder="Enter last name" class="<?php echo error_class('last_name', $user->errors); ?>" required>
    <?php echo display_error('last_name', $user->errors); ?>
</div>

<div class="form-group">
    <label for="email">Email</label>
    <input type="email" name="user[email]" id="email" value="<?php echo h($user->email); ?>" placeholder="Enter email address" class="<?php echo error_class('email', $user->errors); ?>" required>
    <?php echo display_error('email', $user->errors); ?>
</div>

<div class="form-group">
    <label for="password">Password <?php if(!$is_new) { echo "(leave blank to keep current password)"; } ?></label>
    <input type="password" name="user[password]" id="password" placeholder="Enter password" class="<?php echo error_class('password', $user->errors); ?>" <?php if($is_new) { echo "required"; } ?>>
    <?php echo display_error('password', $user->errors); ?>
</div>

<div class="form-group">
    <label for="confirm_password">Confirm Password <?php if(!$is_new) { echo "(leave blank to keep current password)"; } ?></label>
    <input type="password" name="user[confirm_password]" id="confirm_password" placeholder="Re-enter the password" class="<?php echo error_class('confirm_password', $user->errors); ?>" <?php if($is_new) { echo "required"; } ?>>
    <?php echo display_error('confirm_password', $user->errors); ?>
</div>

<?php if($session->is_super_admin()) { ?>
<div class="form-group">
    <label for="user_level">User Level</label>
    <select name="user[user_level]" id="user_level" class="<?php echo error_class('user_level', $user->errors); ?>" required>
        <option value="u" <?php echo ($user->user_level === 'u' || $user->user_level === '') ? "selected" : ""; ?>>Regular User</option>
        <option value="a" <?php echo ($user->user_level === 'a') ? "selected" : ""; ?>>Admin</option>
        <option value="s" <?php echo ($user->user_level === 's') ? "selected" : ""; ?>>Super Admin</option>
    </select>
    <?php echo display_error('user_level', $user->errors); ?>
</div>
<?php } ?>

<div class="form-group">
    <label for="is_active">Status</label>
    <select name="user[is_active]" id="is_active" class="<?php echo error_class('is_active', $user->errors); ?>" required>
        <option value="1" <?php echo ($user->is_active || !isset($user->is_active)) ? "selected" : ""; ?>>Active</option>
        <option value="0" <?php echo (isset($user->is_active) && !$user->is_active) ? "selected" : ""; ?>>Inactive</option>
    </select>
    <?php echo display_error('is_active', $user->errors); ?>
</div>

<?php if(!empty($user->errors)) { ?>
<div class="errors">
    <h3>Please fix the following errors:</h3>
    <ul>
        <?php foreach($user->errors as $error) { ?>
            <li><?php echo h($error); ?></li>
        <?php } ?>
    </ul>
</div>
<?php } ?>
