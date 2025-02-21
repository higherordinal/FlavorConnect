<?php
require_once('../../../private/core/initialize.php');
require_login();

// Only admins and super admins can access this page
if(!$session->is_admin() && !$session->is_super_admin()) {
    $session->message('Access denied. Admin privileges required.');
    redirect_to(url_for('/'));
}

if(is_post_request()) {
    $errors = [];

    // Process styles
    if(isset($_POST['styles'])) {
        RecipeAttribute::find_by_type('style'); // Set up for style type
        foreach($_POST['styles'] as $id => $name) {
            $style = RecipeAttribute::find_by_id($id);
            if($style && $style->name !== $name) { // Only process if name has changed
                $style->name = $name;
                $result = validate_metadata(['name' => $name], RecipeAttribute::get_table_name(), $id);
                if(empty($result)) {
                    $result = $style->save();
                    if(!$result) {
                        $errors = array_merge($errors, $style->errors);
                    }
                } else {
                    $errors = array_merge($errors, $result);
                }
            }
        }
    }

    // Process new styles
    if(isset($_POST['new_styles'])) {
        RecipeAttribute::find_by_type('style'); // Set up for style type
        foreach($_POST['new_styles'] as $name) {
            if(!empty($name)) {
                $style = new RecipeAttribute(['name' => $name]);
                $result = $style->save();
                if(!$result) {
                    $errors = array_merge($errors, $style->errors);
                }
            }
        }
    }

    // Process style deletions
    if(isset($_POST['delete_style'])) {
        RecipeAttribute::find_by_type('style'); // Set up for style type
        foreach($_POST['delete_style'] as $id) {
            $style = RecipeAttribute::find_by_id($id);
            if($style) {
                $result = $style->delete();
                if(!$result) {
                    $errors[] = "Failed to delete style.";
                }
            }
        }
    }

    // Process diets
    if(isset($_POST['diets'])) {
        RecipeAttribute::find_by_type('diet'); // Set up for diet type
        foreach($_POST['diets'] as $id => $name) {
            $diet = RecipeAttribute::find_by_id($id);
            if($diet && $diet->name !== $name) { // Only process if name has changed
                $diet->name = $name;
                $result = validate_metadata(['name' => $name], RecipeAttribute::get_table_name(), $id);
                if(empty($result)) {
                    $result = $diet->save();
                    if(!$result) {
                        $errors = array_merge($errors, $diet->errors);
                    }
                } else {
                    $errors = array_merge($errors, $result);
                }
            }
        }
    }

    // Process new diets
    if(isset($_POST['new_diets'])) {
        RecipeAttribute::find_by_type('diet'); // Set up for diet type
        foreach($_POST['new_diets'] as $name) {
            if(!empty($name)) {
                $diet = new RecipeAttribute(['name' => $name]);
                $result = $diet->save();
                if(!$result) {
                    $errors = array_merge($errors, $diet->errors);
                }
            }
        }
    }

    // Process diet deletions
    if(isset($_POST['delete_diet'])) {
        RecipeAttribute::find_by_type('diet'); // Set up for diet type
        foreach($_POST['delete_diet'] as $id) {
            $diet = RecipeAttribute::find_by_id($id);
            if($diet) {
                $result = $diet->delete();
                if(!$result) {
                    $errors[] = "Failed to delete diet.";
                }
            }
        }
    }

    // Process types
    if(isset($_POST['types'])) {
        RecipeAttribute::find_by_type('type'); // Set up for type type
        foreach($_POST['types'] as $id => $name) {
            $type = RecipeAttribute::find_by_id($id);
            if($type && $type->name !== $name) { // Only process if name has changed
                $type->name = $name;
                $result = validate_metadata(['name' => $name], RecipeAttribute::get_table_name(), $id);
                if(empty($result)) {
                    $result = $type->save();
                    if(!$result) {
                        $errors = array_merge($errors, $type->errors);
                    }
                } else {
                    $errors = array_merge($errors, $result);
                }
            }
        }
    }

    // Process new types
    if(isset($_POST['new_types'])) {
        RecipeAttribute::find_by_type('type'); // Set up for type type
        foreach($_POST['new_types'] as $name) {
            if(!empty($name)) {
                $type = new RecipeAttribute(['name' => $name]);
                $result = $type->save();
                if(!$result) {
                    $errors = array_merge($errors, $type->errors);
                }
            }
        }
    }

    // Process type deletions
    if(isset($_POST['delete_type'])) {
        RecipeAttribute::find_by_type('type'); // Set up for type type
        foreach($_POST['delete_type'] as $id) {
            $type = RecipeAttribute::find_by_id($id);
            if($type) {
                $result = $type->delete();
                if(!$result) {
                    $errors[] = "Failed to delete type.";
                }
            }
        }
    }

    // Process measurements
    if(isset($_POST['measurements'])) {
        foreach($_POST['measurements'] as $id => $name) {
            $measurement = Measurement::find_by_id($id);
            if($measurement && $measurement->name !== $name) { // Only process if name has changed
                $measurement->name = $name;
                $result = $measurement->save();
                if(!$result) {
                    $errors = array_merge($errors, $measurement->errors);
                }
            }
        }
    }

    // Process new measurements
    if(isset($_POST['new_measurements'])) {
        foreach($_POST['new_measurements'] as $name) {
            if(!empty($name)) {
                $measurement = new Measurement(['name' => $name]);
                $result = $measurement->save();
                if(!$result) {
                    $errors = array_merge($errors, $measurement->errors);
                }
            }
        }
    }

    // Process measurement deletions
    if(isset($_POST['delete_measurement'])) {
        foreach($_POST['delete_measurement'] as $id) {
            $measurement = Measurement::find_by_id($id);
            if($measurement) {
                $result = $measurement->delete();
                if(!$result) {
                    $errors[] = "Failed to delete measurement.";
                }
            }
        }
    }

    if(!empty($errors)) {
        // Deduplicate error messages and make them more specific
        $unique_errors = array_unique($errors);
        $message = '';
        foreach($unique_errors as $error) {
            if (strpos($error, 'already exists') !== false) {
                $message .= 'One or more items with this name already exist. ';
            } else {
                $message .= $error . ' ';
            }
        }
        $session->message(rtrim($message), 'error');
    } else {
        $session->message('Changes saved successfully.');
    }
    redirect_to(private_url_for('/admin/categories/index.php'));
}
?>
