<?php
require_once('../../private/core/initialize.php');
require_once(PRIVATE_PATH . '/core/validation_functions.php');
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

// Load recipe ingredients and steps
$ingredients = RecipeIngredient::find_by_recipe_id($recipe->recipe_id);
$steps = RecipeStep::find_by_recipe_id($recipe->recipe_id);

// Check if user has permission to edit this recipe
if($recipe->user_id != $session->get_user_id() && !$session->is_admin()) {
    $session->message('You do not have permission to edit this recipe.', 'error');
    redirect_to(url_for('/recipes/show.php?id=' . $id));
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

            if($recipe->save()) {
                $session->message('Recipe updated successfully.');
                redirect_to(url_for('/recipes/show.php?id=' . $id));
            } else {
                // Database save failed
                $errors = array_merge($errors, $recipe->errors);
                $session->message('Failed to update recipe. Please try again.', 'error');
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
?>

<div class="container">
    <a href="<?php echo $back_link; ?>" class="back-link">
        <i class="fas fa-arrow-left"></i> Back
    </a>
    
    <div class="breadcrumbs">
        <a href="<?php echo url_for('/index.php'); ?>" class="breadcrumb-item">Home</a>
        <span class="breadcrumb-separator">/</span>
        <a href="<?php echo url_for('/recipes/index.php'); ?>" class="breadcrumb-item">Recipes</a>
        <span class="breadcrumb-separator">/</span>
        <a href="<?php echo url_for('/recipes/show.php?id=' . h(u($id))); ?>" class="breadcrumb-item"><?php echo h($recipe->title); ?></a>
        <span class="breadcrumb-separator">/</span>
        <span class="breadcrumb-item active">Edit</span>
    </div>
</div>

<main>
    <div class="recipe-form">
        <header class="page-header">
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
                    <i class="fas fa-trash"></i>
                    Delete Recipe
                </a>
                <a href="<?php echo url_for('/recipes/show.php?id=' . h(u($id))); ?>" class="btn btn-secondary">
                    <i class="fas fa-times"></i>
                    Cancel
                </a>
            </div>
        </form>
    </div>
</main>

<?php include(SHARED_PATH . '/footer.php'); ?>