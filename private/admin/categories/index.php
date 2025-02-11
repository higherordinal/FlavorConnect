<?php
require_once('../../private/core/initialize.php');
require_login();

// Only admins and super admins can access this page
if(!$session->is_admin() && !$session->is_super_admin()) {
    $session->message('Access denied. Admin privileges required.');
    redirect_to(url_for('/'));
}

$page_title = 'Recipe Metadata Management';
include(SHARED_PATH . '/header.php');

// Get all metadata
$styles = RecipeStyle::find_all();
$diets = RecipeDiet::find_all();
$types = RecipeType::find_all();
$measurements = Measurement::find_all();
?>

<link rel="stylesheet" href="<?php echo url_for('/css/admin.css'); ?>">

<div class="admin-management metadata">
    <h1>Recipe Metadata Management</h1>
    
    <?php echo display_session_message(); ?>
    
    <div class="metadata-sections">
        <!-- Recipe Styles Section -->
        <section class="metadata-section">
            <h2>Recipe Styles</h2>
            <div class="actions">
                <a href="<?php echo url_for('/admin/categories/style/new.php'); ?>" class="btn btn-primary">Add Style</a>
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($styles as $style) { ?>
                        <tr>
                            <td><?php echo h($style->name); ?></td>
                            <td class="actions">
                                <a href="<?php echo url_for('/admin/categories/style/edit.php?id=' . h(u($style->id))); ?>" class="btn btn-sm btn-secondary">Edit</a>
                                <a href="<?php echo url_for('/admin/categories/style/delete.php?id=' . h(u($style->id))); ?>" class="btn btn-sm btn-danger">Delete</a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </section>

        <!-- Recipe Diets Section -->
        <section class="metadata-section">
            <h2>Recipe Diets</h2>
            <div class="actions">
                <a class="action" href="<?php echo url_for('/admin/new_diet.php'); ?>">Add Diet</a>
            </div>
            <table class="list">
                <tr>
                    <th>Name</th>
                    <th>Recipes</th>
                    <th>Actions</th>
                </tr>
                <?php foreach($diets as $diet) { ?>
                    <tr>
                        <td><?php echo h($diet->name); ?></td>
                        <td><?php echo Recipe::count_by_diet($diet->id); ?></td>
                        <td class="actions">
                            <a class="action" href="<?php echo url_for('/admin/edit_diet.php?id=' . h(u($diet->id))); ?>">Edit</a>
                            <a class="action delete" href="<?php echo url_for('/admin/delete_diet.php?id=' . h(u($diet->id))); ?>">Delete</a>
                        </td>
                    </tr>
                <?php } ?>
            </table>
        </section>

        <!-- Recipe Types Section -->
        <section class="metadata-section">
            <h2>Recipe Types</h2>
            <div class="actions">
                <a class="action" href="<?php echo url_for('/admin/new_type.php'); ?>">Add Type</a>
            </div>
            <table class="list">
                <tr>
                    <th>Name</th>
                    <th>Recipes</th>
                    <th>Actions</th>
                </tr>
                <?php foreach($types as $type) { ?>
                    <tr>
                        <td><?php echo h($type->name); ?></td>
                        <td><?php echo Recipe::count_by_type($type->id); ?></td>
                        <td class="actions">
                            <a class="action" href="<?php echo url_for('/admin/edit_type.php?id=' . h(u($type->id))); ?>">Edit</a>
                            <a class="action delete" href="<?php echo url_for('/admin/delete_type.php?id=' . h(u($type->id))); ?>">Delete</a>
                        </td>
                    </tr>
                <?php } ?>
            </table>
        </section>

        <!-- Measurements Section -->
        <section class="metadata-section">
            <h2>Measurements</h2>
            <div class="actions">
                <a class="action" href="<?php echo url_for('/admin/new_measurement.php'); ?>">Add Measurement</a>
            </div>
            <table class="list">
                <tr>
                    <th>Name</th>
                    <th>Uses</th>
                    <th>Actions</th>
                </tr>
                <?php foreach($measurements as $measurement) { ?>
                    <tr>
                        <td><?php echo h($measurement->name); ?></td>
                        <td><?php echo Recipe::count_by_measurement($measurement->id); ?></td>
                        <td class="actions">
                            <a class="action" href="<?php echo url_for('/admin/edit_measurement.php?id=' . h(u($measurement->id))); ?>">Edit</a>
                            <a class="action delete" href="<?php echo url_for('/admin/delete_measurement.php?id=' . h(u($measurement->id))); ?>">Delete</a>
                        </td>
                    </tr>
                <?php } ?>
            </table>
        </section>
    </div>
</div>

<?php include(SHARED_PATH . '/footer.php'); ?>
