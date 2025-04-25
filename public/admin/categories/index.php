<?php
require_once('../../../private/core/initialize.php');
require_admin();

$page_title = 'Admin: Recipe Metadata Management';
$page_style = 'admin';

// Scripts
// Note: 'common' and 'back-link' are already loaded in member_header.php
$utility_scripts = [];
$component_scripts = ['recipe-favorite'];
$page_scripts = ['admin'];

// Get all metadata
$styles = RecipeAttribute::find_by_type('style');
$diets = RecipeAttribute::find_by_type('diet');
$types = RecipeAttribute::find_by_type('type');
$measurements = Measurement::find_all_ordered();

include(SHARED_PATH . '/member_header.php');
?>

<div class="admin-management metadata">
    <?php 
    // Use unified_navigation directly, which will call get_back_link internally
    echo unified_navigation(
        '/admin/index.php',
        [
            ['url' => '/index.php', 'label' => 'Home'],
            ['url' => '/admin/index.php', 'label' => 'Admin'],
            ['label' => 'Recipe Metadata']
        ]
    ); 
    ?>

    <div class="admin-header">
        <h1>Recipe Metadata Management</h1>
    </div>
    
    <?php echo display_session_message(); ?>
    
    <div class="metadata-sections">
        <!-- Recipe Styles Section -->
        <section class="metadata-section">
            <div class="section-header">
                <h2>Recipe Styles</h2>
            </div>
            <table class="list">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Recipes</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($styles as $style) { ?>
                        <tr id="style-<?php echo h($style->id); ?>">
                            <td data-label="Name"><?php echo h($style->name); ?></td>
                            <td data-label="Recipes"><?php echo RecipeAttribute::count_by_attribute_id($style->id, 'style'); ?></td>
                            <td data-label="Actions" class="actions">
                                <a href="<?php echo url_for('/admin/categories/style/edit.php?id=' . h(u($style->id))); ?>" class="action edit" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="<?php echo url_for('/admin/categories/style/delete.php?id=' . h(u($style->id))); ?>" class="action delete" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            <div class="add-new-button">
                <a href="<?php echo url_for('/admin/categories/style/new.php' . get_ref_parameter('ref_page', '/admin/categories/index.php')); ?>" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Style
                </a>
            </div>
        </section>

        <!-- Recipe Diets Section -->
        <section class="metadata-section">
            <div class="section-header">
                <h2>Recipe Diets</h2>
            </div>
            <table class="list">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Recipes</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($diets as $diet) { ?>
                        <tr id="diet-<?php echo h($diet->id); ?>">
                            <td data-label="Name"><?php echo h($diet->name); ?></td>
                            <td data-label="Recipes"><?php echo RecipeAttribute::count_by_attribute_id($diet->id, 'diet'); ?></td>
                            <td data-label="Actions" class="actions">
                                <a href="<?php echo url_for('/admin/categories/diet/edit.php?id=' . h(u($diet->id)) . get_ref_parameter('ref_page')); ?>" class="action edit" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="<?php echo url_for('/admin/categories/diet/delete.php?id=' . h(u($diet->id)) . get_ref_parameter('ref_page')); ?>" class="action delete" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            <div class="add-new-button">
                <a href="<?php echo url_for('/admin/categories/diet/new.php' . get_ref_parameter('ref_page', '/admin/categories/index.php')); ?>" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Diet
                </a>
            </div>
        </section>

        <!-- Recipe Types Section -->
        <section class="metadata-section">
            <div class="section-header">
                <h2>Recipe Types</h2>
            </div>
            <table class="list">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Recipes</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($types as $type) { ?>
                        <tr id="type-<?php echo h($type->id); ?>">
                            <td data-label="Name"><?php echo h($type->name); ?></td>
                            <td data-label="Recipes"><?php echo RecipeAttribute::count_by_attribute_id($type->id, 'type'); ?></td>
                            <td data-label="Actions" class="actions">
                                <a href="<?php echo url_for('/admin/categories/type/edit.php?id=' . h(u($type->id)) . get_ref_parameter('ref_page')); ?>" class="action edit" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="<?php echo url_for('/admin/categories/type/delete.php?id=' . h(u($type->id)) . get_ref_parameter('ref_page')); ?>" class="action delete" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            <div class="add-new-button">
                <a href="<?php echo url_for('/admin/categories/type/new.php' . get_ref_parameter('ref_page', '/admin/categories/index.php')); ?>" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Type
                </a>
            </div>
        </section>

        <!-- Recipe Measurements Section -->
        <section class="metadata-section">
            <div class="section-header">
                <h2>Recipe Measurements</h2>
            </div>
            <table class="list">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($measurements as $measurement) { ?>
                        <tr id="measurement-<?php echo h($measurement->measurement_id); ?>">
                            <td data-label="Name"><?php echo h($measurement->name); ?></td>
                            <td data-label="Actions" class="actions">
                                <a href="<?php echo url_for('/admin/categories/measurement/edit.php?id=' . h(u($measurement->measurement_id))); ?>" class="action edit" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="<?php echo url_for('/admin/categories/measurement/delete.php?id=' . h(u($measurement->measurement_id))); ?>" class="action delete" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            <div class="add-new-button">
                <a href="<?php echo url_for('/admin/categories/measurement/new.php' . get_ref_parameter('ref_page', '/admin/categories/index.php')); ?>" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Measurement
                </a>
            </div>
        </section>
    </div>
</div>

<?php include(SHARED_PATH . '/footer.php'); ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Check if we have a fragment identifier in the URL
        if (window.location.hash) {
            // Get the element with the ID matching the fragment
            const targetElement = document.getElementById(window.location.hash.substring(1));
            
            if (targetElement) {
                // Add a highlight class to the element
                targetElement.classList.add('highlight-row');
                
                // Scroll to the element
                setTimeout(function() {
                    targetElement.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                    
                    // Remove the highlight after a few seconds
                    setTimeout(function() {
                        targetElement.classList.remove('highlight-row');
                    }, 3000);
                }, 300);
            }
        }
    });
</script>

<style>
    .highlight-row {
        background-color: rgba(255, 255, 0, 0.2) !important;
        transition: background-color 3s;
    }
</style>
