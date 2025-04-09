<?php
require_once('../../private/core/initialize.php');
require_login();

$page_title = 'Create Recipe';
$page_style = 'recipe-crud';
$component_styles = ['forms'];

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
        if(isset($_FILES['recipe_image']) && $_FILES['recipe_image']['name'] != '') {
            // Create image processor
            require_once(PRIVATE_PATH . '/classes/RecipeImageProcessor.class.php');
            $processor = new RecipeImageProcessor();
            
            // Define upload directory
            $upload_dir = PUBLIC_PATH . '/assets/uploads/recipes';
            
            // Check for file upload errors first
            if ($_FILES['recipe_image']['error'] !== UPLOAD_ERR_OK) {
                $upload_error_messages = [
                    UPLOAD_ERR_INI_SIZE => "The uploaded file exceeds the upload_max_filesize directive in php.ini.",
                    UPLOAD_ERR_FORM_SIZE => "The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form.",
                    UPLOAD_ERR_PARTIAL => "The uploaded file was only partially uploaded.",
                    UPLOAD_ERR_NO_FILE => "No file was uploaded.",
                    UPLOAD_ERR_NO_TMP_DIR => "Missing a temporary folder.",
                    UPLOAD_ERR_CANT_WRITE => "Failed to write file to disk.",
                    UPLOAD_ERR_EXTENSION => "A PHP extension stopped the file upload."
                ];
                
                $error_message = $upload_error_messages[$_FILES['recipe_image']['error']] ?? "Unknown upload error.";
                $errors['recipe_image'] = $error_message;
                
                // Add to session message for non-JavaScript users
                $session->message('Error uploading image: ' . $error_message, 'error');
            } else {
                // Handle image upload and processing
                $upload_result = $processor->handleImageUpload($_FILES['recipe_image'], $upload_dir);
                
                if($upload_result['success']) {
                    $_POST['img_file_path'] = $upload_result['filename'];
                    
                    // If there were processing errors but upload succeeded, show a warning
                    if(!empty($upload_result['errors'])) {
                        $session->message('Image uploaded but processing had issues: ' . implode(', ', $upload_result['errors']), 'warning');
                    }
                } else {
                    // Add processor errors to the errors array
                    foreach ($upload_result['errors'] as $error) {
                        $errors['recipe_image'] = $error;
                    }
                    $session->message('Error uploading image: ' . implode(', ', $upload_result['errors']), 'error');
                }
            }
        }

        // Only proceed if there are no errors
        if(empty($errors)) {
            // Set recipe properties
            $recipe->user_id = $session->get_user_id();
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
            $recipe->is_featured = 0; // Set to integer 0 instead of boolean false
            
            // Use only created_at with current timestamp
            $recipe->created_at = date('Y-m-d H:i:s');

            if($recipe->save()) {
                $ingredient_errors = false;
                
                // Save ingredients - now that we have a valid recipe_id
                if(isset($_POST['ingredients']) && is_array($_POST['ingredients'])) {
                    foreach($_POST['ingredients'] as $ingredient_data) {
                        // Skip empty ingredient names
                        if(empty($ingredient_data['name'])) {
                            continue;
                        }
                        
                        // Create recipe ingredient with the recipe_id
                        $recipe_ingredient = new RecipeIngredient([
                            'recipe_id' => $recipe->recipe_id,
                            'name' => $ingredient_data['name'],
                            'measurement_id' => $ingredient_data['measurement_id'],
                            'quantity' => $ingredient_data['quantity']
                        ]);
                        
                        if(!$recipe_ingredient->save()) {
                            $ingredient_errors = true;
                            $errors = array_merge($errors, $recipe_ingredient->errors);
                        }
                    }
                    
                    // If we had ingredient errors, log them but continue
                    if($ingredient_errors) {
                        error_log('Errors saving ingredients for recipe ID ' . $recipe->recipe_id . ': ' . print_r($errors, true));
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
}

$back_link_data = get_back_link('/recipes/index.php');
// Extract the URL from the back_link_data array
$back_link = $back_link_data['url'];
// Get the suggested back text
$back_text = $back_link_data['text'];
?>

<div class="container">
    <?php 
    echo unified_navigation(
        $back_link,
        [
            ['url' => '/index.php', 'label' => 'Home'],
            ['url' => '/recipes/index.php', 'label' => 'Recipes'],
            ['label' => 'Create Recipe']
        ],
        $back_text
    ); 
    ?>
</div>

<div class="recipe-form">
    <div class="page-header with-banner" id="recipe-header" style="background-image: url('<?php echo url_for('/assets/images/recipe-form-header.webp'); ?>');">
        <h1>Create New Recipe</h1>
    </div>
    
    <?php echo display_errors($errors); ?>
    
    <form action="<?php echo url_for('/recipes/new.php'); ?>" method="post" enctype="multipart/form-data">
        <?php include('form_fields.php'); ?>
        
        <div class="form-buttons">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create Recipe
            </button>
            <a href="<?php echo url_for('/recipes/index.php'); ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php include(SHARED_PATH . '/footer.php'); ?>