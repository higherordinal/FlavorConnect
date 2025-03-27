<?php
// Prevent direct access to this template
if(!isset($type)) {
    redirect_to(url_for('/admin/categories/index.php'));
}
?>

<div class="form-group">
    <label for="type_name">Type Name</label>
    <input type="text" class="form-control<?php echo error_class('name', $type->errors); ?>" id="type_name" name="type[name]" value="<?php echo h($type->name); ?>" required data-error-message="Type name cannot be blank">
    <?php echo display_error('name', $type->errors); ?>
</div>

<div class="form-group">
    <label for="type_description">Description</label>
    <textarea class="form-control<?php echo error_class('description', $type->errors); ?>" id="type_description" name="type[description]" rows="3" data-error-message="Please provide a description"><?php echo h($type->description); ?></textarea>
    <?php echo display_error('description', $type->errors); ?>
</div>
