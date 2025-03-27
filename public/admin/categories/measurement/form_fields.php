<?php
// Prevent direct access to this template
if(!isset($measurement)) {
    redirect_to(url_for('/admin/categories/index.php'));
}
?>

<div class="form-group">
    <label for="measurement_name">Measurement Name</label>
    <input type="text" class="form-control<?php echo error_class('name', $measurement->errors); ?>" id="measurement_name" name="measurement[name]" value="<?php echo h($measurement->name); ?>" required data-error-message="Measurement name cannot be blank">
    <?php echo display_error('name', $measurement->errors); ?>
</div>

<div class="form-group">
    <label for="measurement_abbreviation">Abbreviation</label>
    <input type="text" class="form-control<?php echo error_class('abbreviation', $measurement->errors); ?>" id="measurement_abbreviation" name="measurement[abbreviation]" value="<?php echo h($measurement->abbreviation); ?>" required data-error-message="Abbreviation cannot be blank">
    <?php echo display_error('abbreviation', $measurement->errors); ?>
</div>

<div class="form-group">
    <label for="measurement_type">Type</label>
    <select class="form-control<?php echo error_class('type', $measurement->errors); ?>" id="measurement_type" name="measurement[type]" required data-error-message="Please select a measurement type">
        <option value="volume" <?php if($measurement->type === 'volume') echo 'selected'; ?>>Volume</option>
        <option value="weight" <?php if($measurement->type === 'weight') echo 'selected'; ?>>Weight</option>
        <option value="count" <?php if($measurement->type === 'count') echo 'selected'; ?>>Count</option>
    </select>
    <?php echo display_error('type', $measurement->errors); ?>
</div>
