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
        foreach($_POST['styles'] as $id => $name) {
            $style = RecipeAttribute::find_by_id($id);
            if($style) {
                $style->name = $name;
                $result = $style->save();
                if(!$result) {
                    $errors = array_merge($errors, $style->errors);
                }
            }
        }
    }

    // Process new styles
    if(isset($_POST['new_styles'])) {
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

    // Process diets
    if(isset($_POST['diets'])) {
        foreach($_POST['diets'] as $id => $name) {
            $diet = RecipeAttribute::find_by_id($id);
            if($diet) {
                $diet->name = $name;
                $result = $diet->save();
                if(!$result) {
                    $errors = array_merge($errors, $diet->errors);
                }
            }
        }
    }

    // Process new diets
    if(isset($_POST['new_diets'])) {
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

    // Process types
    if(isset($_POST['types'])) {
        foreach($_POST['types'] as $id => $name) {
            $type = RecipeAttribute::find_by_id($id);
            if($type) {
                $type->name = $name;
                $result = $type->save();
                if(!$result) {
                    $errors = array_merge($errors, $type->errors);
                }
            }
        }
    }

    // Process new types
    if(isset($_POST['new_types'])) {
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

    // Process measurements
    if(isset($_POST['measurements'])) {
        foreach($_POST['measurements'] as $id => $name) {
            $measurement = Measurement::find_by_id($id);
            if($measurement) {
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

    if(!empty($errors)) {
        $session->message('Error: ' . implode(', ', $errors), 'error');
    } else {
        $session->message('Changes saved successfully.');
    }
}

redirect_to(url_for('/admin/categories/index.php'));
?>
