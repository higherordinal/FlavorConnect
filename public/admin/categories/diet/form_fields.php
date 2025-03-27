<?php
// Prevent direct access to this template
if(!isset($diet)) {
    redirect_to(url_for('/admin/categories/index.php'));
}
?>

<div class="form-group">
    <label for="diet_name">Diet Name</label>
    <input type="text" class="form-control<?php echo error_class('name', $diet->errors); ?>" id="diet_name" name="diet[name]" value="<?php echo h($diet->name); ?>" required data-error-message="Diet name cannot be blank">
    <?php echo display_error('name', $diet->errors); ?>
</div>

<div class="form-group">
    <label for="diet_description">Description</label>
    <textarea class="form-control<?php echo error_class('description', $diet->errors); ?>" id="diet_description" name="diet[description]" rows="3" data-error-message="Please provide a description"><?php echo h($diet->description); ?></textarea>
    <?php echo display_error('description', $diet->errors); ?>
</div>
