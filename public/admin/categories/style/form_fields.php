<?php
// Prevent direct access to this template
if(!isset($style)) {
    redirect_to(url_for('/admin/categories/'));
}
?>

<div class="form-group">
    <label for="style-name">Name</label>
    <input type="text" class="form-control" id="style-name" name="style[name]" value="<?php echo h($style->name); ?>" required>
</div>
