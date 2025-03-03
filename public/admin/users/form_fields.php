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
    <input type="text" name="user[username]" id="username" value="<?php echo h($user->username); ?>" placeholder="Enter username" required>
</div>

<div class="form-group">
    <label for="first_name">First Name</label>
    <input type="text" name="user[first_name]" id="first_name" value="<?php echo h($user->first_name); ?>" placeholder="Enter first name" required>
</div>

<div class="form-group">
    <label for="last_name">Last Name</label>
    <input type="text" name="user[last_name]" id="last_name" value="<?php echo h($user->last_name); ?>" placeholder="Enter last name" required>
</div>

<div class="form-group">
    <label for="email">Email</label>
    <input type="email" name="user[email]" id="email" value="<?php echo h($user->email); ?>" placeholder="Enter email address" required>
</div>

<div class="form-group">
    <label for="password">Password <?php if(!$is_new) { echo "(leave blank to keep current password)"; } ?></label>
    <input type="password" name="user[password]" id="password" placeholder="Enter password" <?php if($is_new) { echo "required"; } ?>>
</div>

<div class="form-group">
    <label for="confirm_password">Confirm Password <?php if(!$is_new) { echo "(leave blank to keep current password)"; } ?></label>
    <input type="password" name="user[confirm_password]" id="confirm_password" placeholder="Re-enter the password" <?php if($is_new) { echo "required"; } ?>>
</div>

<?php if($session->is_super_admin()) { ?>
<div class="form-group">
    <label for="user_level">User Level</label>
    <select name="user[user_level]" id="user_level" required>
        <option value="u" <?php echo ($user->user_level === 'u' || $user->user_level === '') ? "selected" : ""; ?>>Regular User</option>
        <option value="a" <?php echo ($user->user_level === 'a') ? "selected" : ""; ?>>Admin</option>
        <option value="s" <?php echo ($user->user_level === 's') ? "selected" : ""; ?>>Super Admin</option>
    </select>
</div>
<?php } ?>

<div class="form-group">
    <label for="is_active">Status</label>
    <select name="user[is_active]" id="is_active" required>
        <option value="1" <?php echo ($user->is_active || !isset($user->is_active)) ? "selected" : ""; ?>>Active</option>
        <option value="0" <?php echo (isset($user->is_active) && !$user->is_active) ? "selected" : ""; ?>>Inactive</option>
    </select>
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
