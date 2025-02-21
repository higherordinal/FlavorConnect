<?php
// Prevent this file from being accessed directly
if(!isset($user)) {
    redirect_to(url_for('/admin/users/index.php'));
}
?>

<div class="form-group">
    <label for="username">Username</label>
    <input type="text" name="user[username]" id="username" value="<?php echo isset($user->user_id) ? h($user->username) : ''; ?>" placeholder="Enter username" required>
</div>

<div class="form-group">
    <label for="first_name">First Name</label>
    <input type="text" name="user[first_name]" id="first_name" value="<?php echo isset($user->user_id) ? h($user->first_name) : ''; ?>" placeholder="Enter first name" required>
</div>

<div class="form-group">
    <label for="last_name">Last Name</label>
    <input type="text" name="user[last_name]" id="last_name" value="<?php echo isset($user->user_id) ? h($user->last_name) : ''; ?>" placeholder="Enter last name" required>
</div>

<div class="form-group">
    <label for="email">Email</label>
    <input type="email" name="user[email]" id="email" value="<?php echo isset($user->user_id) ? h($user->email) : ''; ?>" placeholder="Enter email address" required>
</div>

<div class="form-group">
    <label for="password">Password <?php if(isset($user->user_id)) { echo "(leave blank to keep current password)"; } ?></label>
    <input type="password" name="user[password]" id="password" placeholder="Enter password" <?php if(!isset($user->user_id)) { echo "required"; } ?>>
</div>

<?php if($session->is_super_admin()) { ?>
<div class="form-group">
    <label for="user_level">User Level</label>
    <select name="user[user_level]" id="user_level" required>
        <option value="u" <?php echo (!isset($user->user_id) || $user->user_level === 'u') ? "selected" : ""; ?>>Regular User</option>
        <option value="a" <?php echo (isset($user->user_id) && $user->user_level === 'a') ? "selected" : ""; ?>>Admin</option>
        <option value="s" <?php echo (isset($user->user_id) && $user->user_level === 's') ? "selected" : ""; ?>>Super Admin</option>
    </select>
</div>
<?php } ?>

<div class="form-group">
    <label for="is_active">Status</label>
    <select name="user[is_active]" id="is_active" required>
        <option value="1" <?php echo (!isset($user->user_id) || $user->is_active) ? "selected" : ""; ?>>Active</option>
        <option value="0" <?php echo (isset($user->user_id) && !$user->is_active) ? "selected" : ""; ?>>Inactive</option>
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
