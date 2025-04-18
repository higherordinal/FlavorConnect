<?php
require_once('../../private/core/initialize.php');
require_login();

$page_title = 'Edit Recipe';
$page_style = 'recipe-crud';
$component_styles = ['forms'];

// Scripts
$utility_scripts = ['common', 'form-validation', 'back-link'];
$component_scripts = ['recipe-form'];
include(SHARED_PATH . '/member_header.php');

// Validate recipe access with ownership check and redirect to recipes index if invalid
$recipe = validate_recipe_access(null, true, true, '/recipes/index.php');
if(!$recipe) {
    exit; // validate_recipe_access already handled the error
}

$id = $recipe->recipe_id;

// Load recipe ingredients and steps
$ingredients = RecipeIngredient::find_by_recipe_id($recipe->recipe_id);
$steps = RecipeStep::find_by_recipe_id($recipe->recipe_id);

// Initialize ingredients and instructions arrays in the recipe object
$recipe->ingredients = [];
$recipe->instructions = [];

// Populate the recipe object with existing ingredients and steps
if (!empty($ingredients)) {
    foreach ($ingredients as $ingredient) {
        $recipe->ingredients[] = [
            'ingredient_id' => $ingredient->ingredient_id,
            'recipe_id' => $ingredient->recipe_id,
            'name' => $ingredient->name,
            'quantity' => $ingredient->quantity,
            'measurement_id' => $ingredient->measurement_id
        ];
    }
}

if (!empty($steps)) {
    foreach ($steps as $step) {
        $recipe->instructions[] = [
            'step_id' => $step->step_id,
            'recipe_id' => $step->recipe_id,
            'instruction' => $step->instruction,
            'step_number' => $step->step_number
        ];
    }
}

$errors = [];

if(is_post_request()) {
    // Validate recipe data
    $errors = validate_recipe($_POST);
    
    if(empty($errors)) {
        // Handle file upload
        if(isset($_FILES['recipe_image']) && $_FILES['recipe_image']['name'] != '') {
            // Create image processor
            require_once(PRIVATE_PATH . '/classes/RecipeImageProcessor.class.php');
            $processor = new RecipeImageProcessor();
            
            // Define upload directory
            $upload_dir = PUBLIC_PATH . '/assets/uploads/recipes';
            
            // Get old filename if exists
            $old_filename = $recipe->img_file_path;
            
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
                $upload_result = $processor->handleImageUpload($_FILES['recipe_image'], $upload_dir, $old_filename);
                
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
            // Update recipe properties
            $recipe->title = $_POST['title'] ?? $recipe->title;
            $recipe->description = $_POST['description'] ?? $recipe->description;
            $recipe->style_id = $_POST['style_id'] ?? $recipe->style_id;
            $recipe->diet_id = $_POST['diet_id'] ?? $recipe->diet_id;
            $recipe->type_id = $_POST['type_id'] ?? $recipe->type_id;
            
            // Convert hours and minutes to seconds
            $prep_hours = intval($_POST['prep_hours'] ?? 0);
            $prep_minutes = intval($_POST['prep_minutes'] ?? 0);
            $cook_hours = intval($_POST['cook_hours'] ?? 0);
            $cook_minutes = intval($_POST['cook_minutes'] ?? 0);
            
            $recipe->prep_time = ($prep_hours * 3600) + ($prep_minutes * 60);
            $recipe->cook_time = ($cook_hours * 3600) + ($cook_minutes * 60);
            $recipe->servings = $_POST['servings'] ?? $recipe->servings;
            $recipe->alt_text = $_POST['alt_text'] ?? $recipe->alt_text;
            $recipe->video_url = $_POST['video_url'] ?? $recipe->video_url;
            
            // Set image file path if it was uploaded
            if(isset($_POST['img_file_path'])) {
                $recipe->img_file_path = $_POST['img_file_path'];
            }
            
            // Process ingredients
            $ingredients = [];
            if(isset($_POST['ingredients'])) {
                foreach($_POST['ingredients'] as $i => $ingredient) {
                    if(!empty($ingredient['name'])) {
                        $ingredients[] = [
                            'name' => $ingredient['name'],
                            'quantity' => $ingredient['quantity'] ?? '',
                            'measurement_id' => $ingredient['measurement_id'] ?? null,
                            'sort_order' => $i + 1
                        ];
                    }
                }
            }
            $recipe->ingredients = $ingredients;
            
            // Process instructions
            $instructions = [];
            if(isset($_POST['steps'])) {
                foreach($_POST['steps'] as $i => $step) {
                    if(!empty($step['instruction'])) {
                        $instructions[] = [
                            'instruction' => $step['instruction'],
                            'step_number' => $i + 1
                        ];
                    }
                }
            }
            $recipe->instructions = $instructions;
            
            // Save the recipe
            if($recipe->save()) {
                $session->message('Recipe updated successfully!');
                redirect_to(url_for('/recipes/show.php?id=' . $recipe->recipe_id));
            } else {
                // If there were errors during save, add them to the errors array
                $errors = array_merge($errors, $recipe->errors);
                $session->message('Error updating recipe. Please try again.', 'error');
            }
        }
    }
}

$ref = $_GET['ref'] ?? '';
$back_link_data = get_back_link('/recipes/show.php?id=' . h(u($id)));

// Extract the URL from the back_link_data array
$back_link = $back_link_data['url'];

// Use custom back text if specified by ref parameter, otherwise use suggested text
$back_text = match($ref) {
    'profile' => 'Back to Profile',
    'show' => 'Back to Recipe',
    'home' => 'Back to Home',
    'header' => 'Back to Recipes',
    'favorites' => 'Back to Favorites',
    default => $back_link_data['text']
};
?>

<main class="main-content">
    <div class="container">
        <?php 
        echo unified_navigation(
            $back_link,
            [
                ['url' => '/index.php', 'label' => 'Home'],
                ['url' => '/recipes/index.php', 'label' => 'Recipes'],
                ['url' => '/recipes/show.php?id=' . h(u($id)), 'label' => h($recipe->title)],
                ['label' => 'Edit Recipe']
            ],
            $back_text
        ); 
        ?>
    </div>

    <div class="recipe-form">
        <header class="page-header with-recipe-banner" id="recipe-header" <?php 
            if($recipe->img_file_path) {
                echo 'style="background-image: url(\'' . url_for($recipe->get_image_path('banner')) . '\');"';
            }
        ?>>
            <h1>Edit Recipe: <?php echo h($recipe->title); ?></h1>
        </header>

        <?php echo display_errors($errors); ?>
        
        <form action="<?php echo url_for('/recipes/edit.php?id=' . h(u($id))); ?>" method="post" enctype="multipart/form-data">
            <?php include('form_fields.php'); ?>
            
            <div class="form-buttons">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Save Changes
                </button>
                <a href="<?php echo url_for('/recipes/delete.php?id=' . h(u($id))); ?>" class="btn btn-danger">
                    <i class="fas fa-trash-alt"></i>
                    Delete Recipe
                </a>
                <a href="<?php echo $back_link; ?>" class="btn btn-secondary">
                    <i class="fas fa-times"></i>
                    Cancel
                </a>
            </div>
        </form>
    </div>
</main>

<?php include(SHARED_PATH . '/footer.php'); ?>
