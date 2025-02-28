<?php
require_once('../../private/core/initialize.php');
require_once(PRIVATE_PATH . '/core/validation_functions.php');
require_login();

// Debug output
error_log("Script Path: " . $_SERVER['SCRIPT_NAME']);
error_log("WWW_ROOT: " . WWW_ROOT);

$page_title = 'Create Recipe';
$page_style = 'recipe-create';
error_log("Page Title: " . $page_title);

include(SHARED_PATH . '/member_header.php');

$recipe = new Recipe();
$errors = [];

if(is_post_request()) {
    // Validate recipe data
    $errors = validate($_POST, [
        'title' => 'required',
        'description' => 'required',
        'style_id' => 'required',
        'diet_id' => 'required',
        'type_id' => 'required',
        'prep_hours' => 'numeric',
        'prep_minutes' => 'numeric',
        'cook_hours' => 'numeric',
        'cook_minutes' => 'numeric',
        'video_url' => 'url',
        'alt_text' => 'required',
    ]);
    
    if(empty($errors)) {
        // Handle file upload
        if(isset($_FILES['recipe_image']) && $_FILES['recipe_image']['error'] === UPLOAD_ERR_OK) {
            $temp_path = $_FILES['recipe_image']['tmp_name'];
            $extension = strtolower(pathinfo($_FILES['recipe_image']['name'], PATHINFO_EXTENSION));
            $filename = uniqid('recipe_') . '.' . $extension;
            $target_path = PUBLIC_PATH . '/assets/uploads/recipes/' . $filename;

            // Move file to target location
            if(move_uploaded_file($temp_path, $target_path)) {
                $_POST['img_file_path'] = $filename;
            } else {
                $session->message('Error uploading image. Please try again.', 'error');
            }
        }

        // Set recipe properties
        $recipe->user_id = $session->user_id;
        $recipe->title = $_POST['title'] ?? '';
        $recipe->description = $_POST['description'] ?? '';
        $recipe->style_id = $_POST['style_id'] ?? '';
        $recipe->diet_id = $_POST['diet_id'] ?? '';
        $recipe->type_id = $_POST['type_id'] ?? '';
        
        // Convert hours and minutes to seconds
        $prep_hours = intval($_POST['prep_hours'] ?? 0);
        $prep_minutes = intval($_POST['prep_minutes'] ?? 0);
        $cook_hours = intval($_POST['cook_hours'] ?? 0);
        $cook_minutes = intval($_POST['cook_minutes'] ?? 0);
        
        $recipe->prep_time = ($prep_hours * 3600) + ($prep_minutes * 60);
        $recipe->cook_time = ($cook_hours * 3600) + ($cook_minutes * 60);
        
        $recipe->video_url = $_POST['video_url'] ?? '';
        $recipe->img_file_path = $_POST['img_file_path'] ?? '';
        $recipe->alt_text = $_POST['alt_text'] ?? '';
        $recipe->is_featured = false;
        $recipe->created_date = date('Y-m-d');
        $recipe->created_time = date('H:i:s');

        if($recipe->save()) {
            // Save ingredients
            if(isset($_POST['ingredients']) && is_array($_POST['ingredients'])) {
                foreach($_POST['ingredients'] as $ingredient_data) {
                    // Create or find ingredient
                    $ingredient = new Ingredient([
                        'recipe_id' => $recipe->recipe_id,
                        'name' => $ingredient_data['name']
                    ]);
                    
                    if($ingredient->save()) {
                        // Create recipe ingredient relationship
                        $recipe_ingredient = new RecipeIngredient([
                            'recipe_id' => $recipe->recipe_id,
                            'ingredient_id' => $ingredient->ingredient_id,
                            'measurement_id' => $ingredient_data['measurement_id'],
                            'quantity' => $ingredient_data['quantity']
                        ]);
                        
                        if(!$recipe_ingredient->save()) {
                            $errors = array_merge($errors, $recipe_ingredient->errors);
                        }
                    } else {
                        $errors = array_merge($errors, $ingredient->errors);
                    }
                }
            }
            
            // Save recipe steps
            if(isset($_POST['steps']) && is_array($_POST['steps'])) {
                foreach($_POST['steps'] as $step_data) {
                    $recipe_step = new RecipeStep([
                        'recipe_id' => $recipe->recipe_id,
                        'step_number' => $step_data['step_number'],
                        'instruction' => $step_data['instruction']
                    ]);
                    
                    if(!$recipe_step->save()) {
                        $errors = array_merge($errors, $recipe_step->errors);
                    }
                }
            }
            
            if(empty($errors)) {
                $session->message('Recipe created successfully.');
                redirect_to(url_for('/recipes/show.php?id=' . $recipe->recipe_id));
            } else {
                $session->message('Failed to save recipe details. Please try again.', 'error');
            }
        } else {
            // Database save failed
            $errors = array_merge($errors, $recipe->errors);
            $session->message('Failed to create recipe. Please try again.', 'error');
        }
    }
}

$ref = $_GET['ref'] ?? '';
$back_link = match($ref) {
    'profile' => url_for('/users/profile.php'),
    'home' => url_for('/index.php'),
    'header' => url_for('/recipes/index.php'),
    default => url_for('/recipes/index.php')
};
?>

<div class="recipe-form">
    <div class="page-header">
        <a href="<?php echo $back_link; ?>" class="back-link">
            <i class="fas fa-arrow-left"></i> Back
        </a>
        <h1>Create New Recipe</h1>
    </div>
    
    <?php echo display_errors($errors); ?>
    
    <form action="<?php echo url_for('/recipes/new.php'); ?>" method="post" enctype="multipart/form-data">
        <?php include('form_fields.php'); ?>
        
        <div class="form-buttons">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create Recipe
            </button>
            <a href="<?php echo url_for('/recipes/index.php'); ?>" class="btn btn-outline">
                <i class="fas fa-times"></i> Cancel
            </a>
        </div>
    </form>
</div>

<?php include(SHARED_PATH . '/footer.php'); ?>