<?php
// Prevent direct access to this template
if(!isset($measurement)) {
    redirect_to(url_for('/admin/categories/index.php'));
}
?>

<div class="form-group">
    <label for="measurement_name">Measurement Name</label>
    <input type="text" class="form-control" id="measurement_name" name="measurement[name]" value="<?php echo h($measurement->name); ?>" required>
</div>

<div class="form-group">
    <label for="measurement_abbreviation">Abbreviation</label>
    <input type="text" class="form-control" id="measurement_abbreviation" name="measurement[abbreviation]" value="<?php echo h($measurement->abbreviation); ?>" required>
</div>

<div class="form-group">
    <label for="measurement_type">Type</label>
    <select class="form-control" id="measurement_type" name="measurement[type]" required>
        <option value="volume" <?php if($measurement->type === 'volume') echo 'selected'; ?>>Volume</option>
        <option value="weight" <?php if($measurement->type === 'weight') echo 'selected'; ?>>Weight</option>
        <option value="count" <?php if($measurement->type === 'count') echo 'selected'; ?>>Count</option>
    </select>
</div>
