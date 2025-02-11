<?php
// Prevent direct access to this template
if(!isset($diet)) {
    redirect_to(url_for('/admin/categories/recipe_metadata.php'));
}
?>

<div class="form-group">
    <label for="diet_name">Diet Name</label>
    <input type="text" class="form-control" id="diet_name" name="diet[name]" value="<?php echo h($diet->name); ?>" required>
</div>

<div class="form-group">
    <label for="diet_description">Description</label>
    <textarea class="form-control" id="diet_description" name="diet[description]" rows="3"><?php echo h($diet->description); ?></textarea>
</div>
