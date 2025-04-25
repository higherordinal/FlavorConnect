<?php
require_once('../../../private/core/initialize.php');
require_admin();

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Initialize errors array
$errors = [];

if(is_post_request()) {
    // Process styles
    if(isset($_POST['styles'])) {
        foreach($_POST['styles'] as $id => $data) {
            $name = $data['name'];
            if (strpos($id, 'new_') === 0) {
                if(!empty($name)) {
                    $style = new RecipeAttribute(['name' => $name, 'type' => 'style']);
                    $result = $style->save();
                    if(!$result) {
                        $errors = array_merge($errors, $style->errors);
                    }
                }
            } else {
                $style = RecipeAttribute::find_one($id, 'style');
                if($style && $style->name !== $name) {
                    $style->name = $name;
                    $result = $style->save();
                    if(!$result) {
                        $errors = array_merge($errors, $style->errors);
                    }
                }
            }
        }
    }
    
    // Process diets
    if(isset($_POST['diets'])) {
        foreach($_POST['diets'] as $id => $data) {
            $name = $data['name'];
            if (strpos($id, 'new_') === 0) {
                if(!empty($name)) {
                    $diet = new RecipeAttribute(['name' => $name, 'type' => 'diet']);
                    $result = $diet->save();
                    if(!$result) {
                        $errors = array_merge($errors, $diet->errors);
                    }
                }
            } else {
                $diet = RecipeAttribute::find_one($id, 'diet');
                if($diet && $diet->name !== $name) {
                    $diet->name = $name;
                    $result = $diet->save();
                    if(!$result) {
                        $errors = array_merge($errors, $diet->errors);
                    }
                }
            }
        }
    }
    
    // Process types
    if(isset($_POST['types'])) {
        foreach($_POST['types'] as $id => $data) {
            $name = $data['name'];
            if (strpos($id, 'new_') === 0) {
                if(!empty($name)) {
                    $type = new RecipeAttribute(['name' => $name, 'type' => 'type']);
                    $result = $type->save();
                    if(!$result) {
                        $errors = array_merge($errors, $type->errors);
                    }
                }
            } else {
                $type = RecipeAttribute::find_one($id, 'type');
                if($type && $type->name !== $name) {
                    $type->name = $name;
                    $result = $type->save();
                    if(!$result) {
                        $errors = array_merge($errors, $type->errors);
                    }
                }
            }
        }
    }
    
    // Process measurements
    if(isset($_POST['measurements'])) {
        foreach($_POST['measurements'] as $id => $data) {
            $name = $data['name'];
            if (strpos($id, 'new_') === 0) {
                if(!empty($name)) {
                    $measurement = new Measurement(['name' => $name]);
                    $result = $measurement->save();
                    if(!$result) {
                        $errors = array_merge($errors, $measurement->errors);
                    }
                }
            } else {
                $measurement = Measurement::find_by_id($id);
                if($measurement && $measurement->name !== $name) {
                    $measurement->name = $name;
                    $result = $measurement->save();
                    if(!$result) {
                        $errors = array_merge($errors, $measurement->errors);
                    }
                }
            }
        }
    }
    
    // Process deletions
    if(isset($_POST['delete_styles'])) {
        foreach($_POST['delete_styles'] as $id) {
            $style = RecipeAttribute::find_one($id, 'style');
            if($style) {
                $style->delete();
            }
        }
    }
    
    if(isset($_POST['delete_diets'])) {
        foreach($_POST['delete_diets'] as $id) {
            $diet = RecipeAttribute::find_one($id, 'diet');
            if($diet) {
                $diet->delete();
            }
        }
    }
    
    if(isset($_POST['delete_types'])) {
        foreach($_POST['delete_types'] as $id) {
            $type = RecipeAttribute::find_one($id, 'type');
            if($type) {
                $type->delete();
            }
        }
    }
    
    if(isset($_POST['delete_measurements'])) {
        foreach($_POST['delete_measurements'] as $id) {
            $measurement = Measurement::find_by_id($id);
            if($measurement) {
                $result = $measurement->delete();
                if(!$result) {
                    $errors = array_merge($errors, $measurement->errors);
                }
            }
        }
    }

    // Set session message and redirect
    if(empty($errors)) {
        $session->message('Changes saved successfully.');
    } else {
        $session->message('Failed to save some changes: ' . implode(', ', $errors), 'error');
    }
    
    // Redirect back to the categories page
    redirect_to(url_for('/admin/categories/index.php' . get_ref_parameter()));
}

// If not a POST request, redirect to index
redirect_to(url_for('/admin/categories/index.php' . get_ref_parameter()));
?>
