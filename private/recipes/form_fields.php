<?php
// Get recipe attributes from their respective tables
$sql = "SELECT style_id as id, name FROM recipe_style ORDER BY name";
$styles = $db->query($sql)->fetch_all(MYSQLI_ASSOC);

$sql = "SELECT diet_id as id, name FROM recipe_diet ORDER BY name";
$diets = $db->query($sql)->fetch_all(MYSQLI_ASSOC);

$sql = "SELECT type_id as id, name FROM recipe_type ORDER BY name";
$types = $db->query($sql)->fetch_all(MYSQLI_ASSOC);
?>

<div class="form-group">
    <label for="title">Recipe Title</label>
    <input type="text" name="title" id="title" class="form-control" value="<?php echo h($recipe->title ?? ''); ?>" required>
</div>

<div class="form-group">
    <label for="description">Description</label>
    <textarea name="description" id="description" class="form-control" rows="4" required><?php echo h($recipe->description ?? ''); ?></textarea>
</div>

<div class="row categories-row">
    <div class="form-group">
        <label for="style_id">Cuisine Style</label>
        <select name="style_id" id="style_id" class="form-control" required>
            <option value="">Select Style</option>
            <?php foreach($styles as $style) { ?>
                <option value="<?php echo h($style['id']); ?>" <?php if(isset($recipe->style_id) && $recipe->style_id == $style['id']) echo 'selected'; ?>>
                    <?php echo h($style['name']); ?>
                </option>
            <?php } ?>
        </select>
    </div>
    
    <div class="form-group">
        <label for="diet_id">Diet Category</label>
        <select name="diet_id" id="diet_id" class="form-control" required>
            <option value="">Select Diet</option>
            <?php foreach($diets as $diet) { ?>
                <option value="<?php echo h($diet['id']); ?>" <?php if(isset($recipe->diet_id) && $recipe->diet_id == $diet['id']) echo 'selected'; ?>>
                    <?php echo h($diet['name']); ?>
                </option>
            <?php } ?>
        </select>
    </div>
    
    <div class="form-group">
        <label for="type_id">Meal Type</label>
        <select name="type_id" id="type_id" class="form-control" required>
            <option value="">Select Type</option>
            <?php foreach($types as $type) { ?>
                <option value="<?php echo h($type['id']); ?>" <?php if(isset($recipe->type_id) && $recipe->type_id == $type['id']) echo 'selected'; ?>>
                    <?php echo h($type['name']); ?>
                </option>
            <?php } ?>
        </select>
    </div>
</div>

<div class="row time-row">
    <div class="time-section">
        <h4>Preparation Time</h4>
        <div class="time-inputs">
            <div class="form-group">
                <label for="prep_hours">Hours</label>
                <input type="number" name="prep_hours" id="prep_hours" class="form-control" min="0" 
                       value="<?php echo h(isset($recipe->prep_time) && $recipe->prep_time > 0 ? floor($recipe->prep_time / 3600) : 0); ?>">
            </div>
            <div class="form-group">
                <label for="prep_minutes">Minutes</label>
                <input type="number" name="prep_minutes" id="prep_minutes" class="form-control" min="0" max="59" 
                       value="<?php echo h(isset($recipe->prep_time) && $recipe->prep_time > 0 ? floor(($recipe->prep_time % 3600) / 60) : 0); ?>">
            </div>
        </div>
    </div>
    
    <div class="time-section">
        <h4>Cooking Time</h4>
        <div class="time-inputs">
            <div class="form-group">
                <label for="cook_hours">Hours</label>
                <input type="number" name="cook_hours" id="cook_hours" class="form-control" min="0" 
                       value="<?php echo h(isset($recipe->cook_time) && $recipe->cook_time > 0 ? floor($recipe->cook_time / 3600) : 0); ?>">
            </div>
            <div class="form-group">
                <label for="cook_minutes">Minutes</label>
                <input type="number" name="cook_minutes" id="cook_minutes" class="form-control" min="0" max="59" 
                       value="<?php echo h(isset($recipe->cook_time) && $recipe->cook_time > 0 ? floor(($recipe->cook_time % 3600) / 60) : 0); ?>">
            </div>
        </div>
    </div>
</div>

<div class="row ingredients-row">
    <div class="ingredients-section">
        <h4>Recipe Ingredients</h4>
        <div id="ingredients-container">
            <?php 
            if (isset($ingredients) && !empty($ingredients)) {
                foreach($ingredients as $i => $ingredient) { ?>
                <div class="ingredient-row">
                    <div class="form-group">
                        <label for="quantity_<?php echo $i; ?>">Quantity</label>
                        <input type="number" name="ingredients[<?php echo $i; ?>][quantity]" id="quantity_<?php echo $i; ?>" 
                               class="form-control" step="0.01" min="0" value="<?php echo h(format_quantity($ingredient->quantity)); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="measurement_<?php echo $i; ?>">Measurement</label>
                        <select name="ingredients[<?php echo $i; ?>][measurement_id]" id="measurement_<?php echo $i; ?>" class="form-control" required>
                            <option value="">Select Measurement</option>
                            <?php
                            $measurements = $db->query("SELECT * FROM measurement ORDER BY name");
                            while($measurement = $measurements->fetch_assoc()) {
                                $selected = ($measurement['measurement_id'] == $ingredient->measurement_id) ? 'selected' : '';
                                echo "<option value=\"" . h($measurement['measurement_id']) . "\" {$selected}>" . h($measurement['name']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="ingredient_<?php echo $i; ?>">Ingredient</label>
                        <input type="text" name="ingredients[<?php echo $i; ?>][name]" id="ingredient_<?php echo $i; ?>" 
                               class="form-control" value="<?php echo h($ingredient->name); ?>" required>
                    </div>
                    
                    <button type="button" class="btn btn-danger remove-ingredient">×</button>
                </div>
                <?php }
            } else {
                // Show default empty ingredient rows if no ingredients exist
                for($i = 0; $i < 3; $i++) { ?>
                <div class="ingredient-row">
                    <div class="form-group">
                        <label for="quantity_<?php echo $i; ?>">Quantity</label>
                        <input type="number" name="ingredients[<?php echo $i; ?>][quantity]" id="quantity_<?php echo $i; ?>" 
                               class="form-control" step="0.01" min="0" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="measurement_<?php echo $i; ?>">Measurement</label>
                        <select name="ingredients[<?php echo $i; ?>][measurement_id]" id="measurement_<?php echo $i; ?>" class="form-control" required>
                            <option value="">Select Measurement</option>
                            <?php
                            $measurements = $db->query("SELECT * FROM measurement ORDER BY name");
                            while($measurement = $measurements->fetch_assoc()) {
                                echo "<option value=\"" . h($measurement['measurement_id']) . "\">" . h($measurement['name']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="ingredient_<?php echo $i; ?>">Ingredient</label>
                        <input type="text" name="ingredients[<?php echo $i; ?>][name]" id="ingredient_<?php echo $i; ?>" class="form-control" required>
                    </div>
                    
                    <button type="button" class="btn btn-danger remove-ingredient">×</button>
                </div>
                <?php }
            } ?>
        </div>
        
        <button type="button" class="btn btn-primary" id="add-ingredient">
            <i class="fas fa-plus"></i> Add Another Ingredient
        </button>
    </div>
</div>

<div class="row directions-row">
    <div class="directions-section">
        <h4>Recipe Directions</h4>
        <div id="directions-container">
            <?php 
            if (isset($steps) && !empty($steps)) {
                foreach($steps as $i => $step) { ?>
                <div class="direction-row">
                    <span class="step-number"><?php echo $i + 1; ?></span>
                    <div class="form-group">
                        <label for="step_<?php echo $i; ?>">Step <?php echo $i + 1; ?> Instructions</label>
                        <textarea name="steps[<?php echo $i; ?>][instruction]" id="step_<?php echo $i; ?>" 
                                  class="form-control" rows="2" required><?php echo h($step->instruction); ?></textarea>
                        <input type="hidden" name="steps[<?php echo $i; ?>][step_number]" value="<?php echo $i + 1; ?>">
                    </div>
                    <button type="button" class="btn btn-danger remove-step">×</button>
                </div>
                <?php }
            } else { ?>
                <div class="direction-row">
                    <span class="step-number">1</span>
                    <div class="form-group">
                        <label for="step_0">Step 1 Instructions</label>
                        <textarea name="steps[0][instruction]" id="step_0" class="form-control" rows="2" required></textarea>
                        <input type="hidden" name="steps[0][step_number]" value="1">
                    </div>
                    <button type="button" class="btn btn-danger remove-step">×</button>
                </div>
                <div class="direction-row">
                    <span class="step-number">2</span>
                    <div class="form-group">
                        <label for="step_1">Step 2 Instructions</label>
                        <textarea name="steps[1][instruction]" id="step_1" class="form-control" rows="2" required></textarea>
                        <input type="hidden" name="steps[1][step_number]" value="2">
                    </div>
                    <button type="button" class="btn btn-danger remove-step">×</button>
                </div>
            <?php } ?>
        </div>
        
        <button type="button" class="btn btn-primary" id="add-step">
            <i class="fas fa-plus"></i> Add Another Step
        </button>
    </div>
</div>

<div class="row media-row">
    <div class="media-section">
        <h4>Recipe Image</h4>
        <div class="form-group">
            <label for="recipe_image">Upload Image</label>
            <input type="file" name="recipe_image" id="recipe_image" class="form-control" accept="image/*" <?php echo isset($recipe->img_file_path) ? '' : 'required'; ?>>
            <?php if(isset($recipe->img_file_path) && $recipe->img_file_path) { ?>
                <div class="mt-2">
                    <img src="<?php echo url_for('/assets/uploads/recipes/' . $recipe->img_file_path); ?>" alt="Current recipe image" class="img-thumbnail" style="max-width: 200px;">
                </div>
            <?php } ?>
            <small class="form-text text-muted">Upload a high-quality image of your recipe</small>
        </div>

        <div class="form-group">
            <label for="alt_text">Image Description</label>
            <input type="text" name="alt_text" id="alt_text" class="form-control" value="<?php echo h($recipe->alt_text ?? ''); ?>" required>
            <small class="form-text text-muted">Describe the image for accessibility purposes</small>
        </div>
    </div>
</div>

<div class="form-group">
    <label for="video_url">Video URL (optional)</label>
    <input type="url" name="video_url" id="video_url" class="form-control" value="<?php echo h($recipe->video_url ?? ''); ?>">
    <small class="form-text text-muted">Add a link to your recipe video if you have one</small>
</div>

<script src="<?php echo url_for('/assets/js/pages/recipe-form.js'); ?>" defer></script>
