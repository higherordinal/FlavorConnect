<?php
// Prevent direct access to this template
if(!isset($style)) {
    redirect_to(url_for('/admin/categories/'));
}
?>

<div class="form-group">
    <label for="style-name">Name</label>
    <input type="text" class="form-control<?php echo error_class('name', $style->errors); ?>" id="style-name" name="style[name]" value="<?php echo h($style->name); ?>" required data-error-message="Style name cannot be blank">
    <?php echo display_error('name', $style->errors); ?>
</div>
