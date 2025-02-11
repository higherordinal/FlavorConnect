<?php
// Prevent direct access to this template
if(!isset($type)) {
    redirect_to(url_for('/admin/categories/recipe_metadata.php'));
}
?>

<div class="form-group">
    <label for="type_name">Type Name</label>
    <input type="text" class="form-control" id="type_name" name="type[name]" value="<?php echo h($type->name); ?>" required>
</div>

<div class="form-group">
    <label for="type_description">Description</label>
    <textarea class="form-control" id="type_description" name="type[description]" rows="3"><?php echo h($type->description); ?></textarea>
</div>
