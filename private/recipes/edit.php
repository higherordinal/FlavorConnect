<?php
require_once('../core/initialize.php');
require_once(PRIVATE_PATH . '/validation/validation.php');
require_login();

$page_title = 'recipe-form';
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
if($recipe->user_id != $session->user_id && !$session->is_admin()) {
    $session->message('You do not have permission to edit this recipe.', 'error');
    redirect_to(url_for('/recipes/show.php?id=' . $id));
}

$errors = [];

if(is_post_request()) {
    // Validate recipe data
    $validation = new Validation($_POST);
    $validation->check('title', [
        'label' => 'Title',
        'required' => true,
        'min' => 2,
        'max' => 255
    ]);
    $validation->check('description', [
        'label' => 'Description',
        'required' => true,
        'min' => 2,
        'max' => 65535
    ]);
    $validation->check('style_id', [
        'label' => 'Style',
        'required' => true,
        'numeric' => true
    ]);
    $validation->check('diet_id', [
        'label' => 'Diet',
        'required' => true,
        'numeric' => true
    ]);
    $validation->check('type_id', [
        'label' => 'Type',
        'required' => true,
        'numeric' => true
    ]);
    $validation->check('prep_hours', [
        'label' => 'Prep Hours',
        'required' => true,
        'numeric' => true
    ]);
    $validation->check('prep_minutes', [
        'label' => 'Prep Minutes',
        'required' => true,
        'numeric' => true
    ]);
    $validation->check('cook_hours', [
        'label' => 'Cook Hours',
        'required' => true,
        'numeric' => true
    ]);
    $validation->check('cook_minutes', [
        'label' => 'Cook Minutes',
        'required' => true,
        'numeric' => true
    ]);
    $validation->check('video_url', [
        'label' => 'Video URL',
        'url' => true
    ]);
    $validation->check('alt_text', [
        'label' => 'Alt Text',
        'required' => true,
        'min' => 2,
        'max' => 255
    ]);

    $errors = $validation->errors();
    
    if(empty($errors)) {
        // Handle file upload
        if(isset($_FILES['recipe_image']) && $_FILES['recipe_image']['error'] === UPLOAD_ERR_OK) {
            $temp_path = $_FILES['recipe_image']['tmp_name'];
            $extension = strtolower(pathinfo($_FILES['recipe_image']['name'], PATHINFO_EXTENSION));
            $filename = uniqid('recipe_') . '.' . $extension;
            $target_path = PUBLIC_PATH . '/assets/uploads/recipes/' . $filename;

            // Move file to target location
            if(move_uploaded_file($temp_path, $target_path)) {
                // Delete old image if exists
                if(!empty($recipe->img_file_path)) {
                    $old_image = PUBLIC_PATH . '/assets/uploads/recipes/' . $recipe->img_file_path;
                    if(file_exists($old_image)) {
                        unlink($old_image);
                    }
                }
                $_POST['img_file_path'] = $filename;
            } else {
                $session->message('Error uploading image. Please try again.', 'error');
            }
        }

        // Update recipe properties
        $recipe->title = $_POST['title'] ?? $recipe->title;
        $recipe->description = $_POST['description'] ?? $recipe->description;
        $recipe->style_id = $_POST['style_id'] ?? $recipe->style_id;
        $recipe->diet_id = $_POST['diet_id'] ?? $recipe->diet_id;
        $recipe->type_id = $_POST['type_id'] ?? $recipe->type_id;
        $recipe->prep_hours = $_POST['prep_hours'] ?? $recipe->prep_hours;
        $recipe->prep_minutes = $_POST['prep_minutes'] ?? $recipe->prep_minutes;
        $recipe->cook_hours = $_POST['cook_hours'] ?? $recipe->cook_hours;
        $recipe->cook_minutes = $_POST['cook_minutes'] ?? $recipe->cook_minutes;
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
?>

<main>
    <div class="recipe-form">
        <header class="page-header">
            <h1>Edit Recipe: <?php echo h($recipe->title); ?></h1>
        </header>

        <?php echo display_errors($errors); ?>
        
        <form action="<?php echo url_for('/private/recipes/edit.php?id=' . h(u($id))); ?>" method="post" enctype="multipart/form-data">
            <?php include('form_fields.php'); ?>
            
            <div class="form-buttons">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Save Changes
                </button>
                <a href="<?php echo url_for('/recipes/show.php?id=' . h(u($id))); ?>" class="btn btn-secondary">
                    <i class="fas fa-times"></i>
                    Cancel
                </a>
            </div>
        </form>
    </div>
</main>

<?php include(SHARED_PATH . '/footer.php'); ?>