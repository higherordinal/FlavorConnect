<?php
require_once('../../private/core/initialize.php');
require_login();

$page_title = 'Edit Recipe';
$page_style = 'recipe-create';
include(SHARED_PATH . '/member_header.php');

if(!isset($_GET['id'])) {
    redirect_to(url_for('/recipes/index.php'));
}
$id = $_GET['id'];
$recipe = Recipe::find_by_id($id);

if(!$recipe) {
    redirect_to(url_for('/recipes/index.php'));
}

// Check if user has permission to edit this recipe
if($recipe->user_id != $session->get_user_id() && !$session->is_admin()) {
    $session->message('You do not have permission to edit this recipe.', 'error');
    redirect_to(url_for('/recipes/show.php?id=' . $id));
}

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
            if($_FILES['recipe_image']['error'] === UPLOAD_ERR_OK) {
                $temp_path = $_FILES['recipe_image']['tmp_name'];
                $extension = strtolower(pathinfo($_FILES['recipe_image']['name'], PATHINFO_EXTENSION));
                
                // Validate file type
                $allowed_extensions = ['jpg', 'jpeg', 'png'];
                if(!in_array($extension, $allowed_extensions)) {
                    $errors[] = "Invalid file type. Allowed formats: JPG, PNG";
                    $session->message('Invalid file type. Allowed formats: JPG, PNG', 'error');
                } else {
                    $filename = uniqid('recipe_') . '.' . $extension;
                    $target_path = PUBLIC_PATH . '/assets/uploads/recipes/' . $filename;
                    
                    // Ensure the directory exists
                    $upload_dir = PUBLIC_PATH . '/assets/uploads/recipes/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }

                    // Move file to target location
                    if(move_uploaded_file($temp_path, $target_path)) {
                        // Delete old image if exists
                        if(!empty($recipe->img_file_path)) {
                            $old_image_path = PUBLIC_PATH . '/assets/uploads/recipes/' . $recipe->img_file_path;
                            if(file_exists($old_image_path)) {
                                unlink($old_image_path);
                            }
                        }
                        $_POST['img_file_path'] = $filename;
                    } else {
                        $session->message('Error uploading image. Please try again.', 'error');
                    }
                }
            } else if($_FILES['recipe_image']['error'] === UPLOAD_ERR_INI_SIZE || $_FILES['recipe_image']['error'] === UPLOAD_ERR_FORM_SIZE) {
                $errors[] = "The uploaded image exceeds the maximum file size limit. Please upload a smaller image.";
                $session->message('The uploaded image exceeds the maximum file size limit.', 'error');
            } else if($_FILES['recipe_image']['error'] !== UPLOAD_ERR_NO_FILE) {
                $session->message('Error uploading image. Please try again.', 'error');
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
            
            $recipe->video_url = $_POST['video_url'] ?? $recipe->video_url;
            if(isset($_POST['img_file_path'])) {
                $recipe->img_file_path = $_POST['img_file_path'];
            }
            $recipe->alt_text = $_POST['alt_text'] ?? $recipe->alt_text;
            $recipe->updated_at = date('Y-m-d H:i:s');
            
            // Process ingredients
            if(isset($_POST['ingredients']) && is_array($_POST['ingredients'])) {
                // Reset ingredients array
                $recipe->ingredients = [];
                
                foreach($_POST['ingredients'] as $i => $ingredient_data) {
                    if(!empty($ingredient_data['name'])) {
                        $recipe->ingredients[] = [
                            'name' => $ingredient_data['name'],
                            'quantity' => $ingredient_data['quantity'] ?? '',
                            'measurement_id' => $ingredient_data['measurement_id'] ?? ''
                        ];
                    }
                }
            }
            
            // Process instructions
            if(isset($_POST['steps']) && is_array($_POST['steps'])) {
                // Reset instructions array
                $recipe->instructions = [];
                
                foreach($_POST['steps'] as $i => $step_data) {
                    if(!empty($step_data['instruction'])) {
                        $recipe->instructions[] = [
                            'instruction' => $step_data['instruction'],
                            'step_number' => $i + 1
                        ];
                    }
                }
            }
            
            // Save the recipe
            $result = $recipe->save();
            
            if($result === true) {
                // Success
                $session->message('Recipe updated successfully.');
                // Ensure we redirect to the show page
                header("Location: " . url_for('/recipes/show.php?id=' . $id));
                exit;
            } else {
                // Database save failed
                $errors[] = "Failed to save recipe.";
                
                // Merge recipe errors with form errors
                if (!empty($recipe->errors)) {
                    $errors = array_merge($errors, $recipe->errors);
                }
            }
        }
    }
}

$ref = $_GET['ref'] ?? '';
$back_link = match($ref) {
    'profile' => url_for('/users/profile.php'),
    'show' => url_for('/recipes/show.php?id=' . h(u($id))),
    'home' => url_for('/index.php'),
    'header' => url_for('/recipes/index.php'),
    'favorites' => url_for('/users/favorites.php'),
    default => url_for('/recipes/show.php?id=' . h(u($id)))
};

$back_text = match($ref) {
    'profile' => 'Back to Profile',
    'show' => 'Back to Recipe',
    'home' => 'Back to Home',
    'header' => 'Back to Recipes',
    'favorites' => 'Back to Favorites',
    default => 'Back to Recipe'
};
?>

<main class="main-content">
    <div class="container">
        <a href="<?php echo $back_link; ?>" class="back-link">
            <i class="fas fa-arrow-left"></i> <?php echo $back_text; ?>
        </a>
        
        <div class="breadcrumbs">
            <a href="<?php echo url_for('/index.php'); ?>" class="breadcrumb-item">Home</a>
            <span class="breadcrumb-separator">/</span>
            <a href="<?php echo url_for('/recipes/index.php'); ?>" class="breadcrumb-item">Recipes</a>
            <span class="breadcrumb-separator">/</span>
            <a href="<?php echo url_for('/recipes/show.php?id=' . h(u($id))); ?>" class="breadcrumb-item"><?php echo h($recipe->title); ?></a>
            <span class="breadcrumb-separator">/</span>
            <span class="breadcrumb-item active">Edit Recipe</span>
        </div>
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
